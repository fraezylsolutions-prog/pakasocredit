<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\TxnStatus;
use App\Enums\TxnType;
use App\Http\Controllers\Controller;
use App\Models\DepositMethod;
use App\Models\Transaction;
use App\Traits\ImageUpload;
use App\Traits\NotifyTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Txn;

class ManualDepositController extends Controller
{
    use ImageUpload, NotifyTrait;

    /**
     * Show bank details + deposit history.
     * URL: GET /user/manual-deposit
     */
    public function index()
    {
        if (! setting('user_deposit', 'permission') || ! Auth::user()->deposit_status) {
            notify()->error(__('Deposit currently unavailable'), 'Error');
            return to_route('user.dashboard');
        }

        if (setting('kyc_deposit') && auth()->user()->kyc != 1) {
            notify()->error(__('Please verify your KYC.'), 'Error');
            return to_route('user.dashboard');
        }

        $bankDetails = $this->bankDetails();
        $currency    = setting('site_currency', 'global');

        // User's manual deposit history
        $deposits = Transaction::where('user_id', Auth::id())
            ->where('type', TxnType::ManualDeposit)
            ->latest()
            ->paginate(10);

        return view('frontend::deposit.manual.index', compact('bankDetails', 'currency', 'deposits'));
    }

    /**
     * Show proof upload form.
     * URL: GET /user/manual-deposit/create
     */
    public function create()
    {
        if (! setting('user_deposit', 'permission') || ! Auth::user()->deposit_status) {
            notify()->error(__('Deposit currently unavailable'), 'Error');
            return to_route('user.dashboard');
        }

        $bankDetails = $this->bankDetails();
        $currency    = setting('site_currency', 'global');

        // Get the manual deposit method from deposit_methods table
        $method = DepositMethod::where('type', 'manual')
            ->where('status', 1)
            ->first();

        return view('frontend::deposit.manual.create', compact('bankDetails', 'currency', 'method'));
    }

    /**
     * Store the proof upload — uses the existing Txn facade (no new model needed).
     * URL: POST /user/manual-deposit/store
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'         => ['required', 'numeric', 'min:' . setting('min_manual_deposit', 'bank_details', 100)],
            'sender_name'    => ['required', 'string', 'max:100'],
            'sender_bank'    => ['required', 'string', 'max:100'],
            'sender_account' => ['required', 'string', 'max:30'],
            'proof'          => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'], // optional
            'note'           => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back()->withInput();
        }

        // Upload proof file if provided (optional)
        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = self::imageUploadTrait($request->file('proof'));
        }

        $user     = Auth::user();
        $amount   = (float) $request->amount;
        $currency = setting('site_currency', 'global');

        // Get manual deposit method (to get charge info)
        $method = DepositMethod::where('type', 'manual')
            ->where('status', 1)
            ->first();

        $charge      = $method ? (float) $method->charge : 0;
        $finalAmount = $amount + $charge;

        // Determine if this is a fund transfer or deposit
        $isTransfer  = $request->transfer_type === 'transfer';
        $description = $isTransfer ? 'Manual Fund Transfer' : 'Manual Bank Transfer Deposit';

        // Build manual data payload
        $manualData = [
            'proof'          => $proofPath,
            'sender_name'    => $request->sender_name,
            'sender_bank'    => $request->sender_bank,
            'sender_account' => $request->sender_account,
            'note'           => $request->note ?? '',
            'type'           => $isTransfer ? 'transfer' : 'deposit',
        ];

        // Create transaction using the existing Txn facade
        $txnInfo = Txn::new(
            $amount,
            $charge,
            $finalAmount,
            $method ? $method->gateway_code : 'manual_bank_transfer',
            $description,
            TxnType::ManualDeposit,
            TxnStatus::Pending,
            $currency,
            $amount,
            $user->id,
            null,
            'User',
            $manualData
        );

        // Notify admin via email, push, SMS
        $shortcodes = [
            '[[full_name]]'      => $user->full_name,
            '[[txn]]'            => $txnInfo->tnx,
            '[[deposit_amount]]' => $amount . ' ' . $currency,
            '[[sender_name]]'    => $request->sender_name,
            '[[sender_bank]]'    => $request->sender_bank,
            '[[site_title]]'     => setting('site_title', 'global'),
            '[[site_url]]'       => route('home'),
        ];

        $this->mailNotify(setting('site_email', 'global'), 'user_manual_deposit_request', $shortcodes);
        $this->pushNotify('user_manual_deposit_request', $shortcodes, route('admin.deposit.pending'), $user->id);
        $this->smsNotify('user_manual_deposit_request', $shortcodes, $user->phone);

        // Success notification
        $symbol = setting('currency_symbol', 'global');
        $notify = [
            'card-header' => $isTransfer ? __('Fund Transfer') : __('Manual Deposit'),
            'title'       => $symbol . $amount . ' ' . ($isTransfer ? __('Transfer Request Submitted') : __('Deposit Proof Submitted')),
            'p'           => $isTransfer
                ? __('Your transfer request has been submitted. Pakaso will review and process within 24 hours.')
                : __('Your deposit proof has been submitted for review. Admin will verify within 24 hours.'),
            'strong'      => __('Transaction ID: ') . $txnInfo->tnx,
            'action'      => route('user.deposit.log'),
            'a'           => __('VIEW HISTORY'),
            'view_name'   => 'deposit',
        ];
        Session::put('user_notify', $notify);

        return redirect()->route('user.notify');
    }

    /**
     * Get bank details from settings — reads from 'bank_details' section.
     */
    private function bankDetails(): array
    {
        return [
            'bank_name'      => setting('manual_deposit_bank_name', 'bank_details', 'Sterling Bank'),
            'account_name'   => setting('manual_deposit_account_name', 'bank_details', 'Pakaso Credit Limited'),
            'account_number' => setting('manual_deposit_account_number', 'bank_details', ''),
            'bank_branch'    => setting('manual_deposit_bank_branch', 'bank_details', ''),
            'instructions'   => setting('manual_deposit_instructions', 'bank_details',
                'Use your registered phone number as payment narration.'),
        ];
    }
}
