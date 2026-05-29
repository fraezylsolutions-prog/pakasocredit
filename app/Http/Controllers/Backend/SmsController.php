<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use App\Models\User;
use App\Traits\SmsTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    use SmsTrait;

    public function __construct()
    {
        $this->middleware('permission:sms-template');
    }

    public function template(Request $request)
    {
        $perPage = $request->perPage ?? 15;
        $order   = $request->order ?? 'asc';
        $search  = $request->search ?? null;
        $status  = $request->status ?? 'all';
        $sms     = SmsTemplate::order($order)
            ->search($search)
            ->status($status)
            ->paginate($perPage);

        return view('backend.sms.template', compact('sms'));
    }

    public function edit_template($id)
    {
        $template = SmsTemplate::find($id);
        return view('backend.sms.edit', compact('template'));
    }

    public function update_template(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message_body' => 'required',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        $template = SmsTemplate::find($request->id);
        $template->update([
            'message_body' => nl2br($request->message_body),
            'status'       => $request->status,
        ]);

        notify()->success(__('SMS Template Updated Successfully'));
        return redirect()->back();
    }

    // ===================== BULK SMS =====================

    public function bulkSms()
    {
        $templates  = SmsTemplate::where('status', 1)->get();
        $totalUsers = User::where('status', 1)
                        ->whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->count();
        $totalAll   = User::whereNotNull('phone')
                        ->where('phone', '!=', '')
                        ->count();

        return view('backend.sms.bulk', compact('templates', 'totalUsers', 'totalAll'));
    }

    public function sendBulkSms(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_type' => 'required|in:active_users,all_users,specific',
            'message_type'   => 'required|in:free_text,template',
            'message'        => 'required_if:message_type,free_text',
            'template_id'    => 'required_if:message_type,template',
        ]);

        if ($validator->fails()) {
            notify()->error($validator->errors()->first(), 'Error');
            return redirect()->back();
        }

        try {
            // Build message
            if ($request->message_type === 'template') {
                $template = SmsTemplate::find($request->template_id);
                if (!$template) {
                    notify()->error('Template not found', 'Error');
                    return redirect()->back();
                }
                $messageBody = strip_tags($template->message_body);
            } else {
                $messageBody = $request->message;
            }

            // Get phone numbers
            $phones = collect();

            if ($request->recipient_type === 'active_users') {
                $phones = User::where('status', 1)
                    ->whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone');

            } elseif ($request->recipient_type === 'all_users') {
                $phones = User::whereNotNull('phone')
                    ->where('phone', '!=', '')
                    ->pluck('phone');

            } elseif ($request->recipient_type === 'specific') {
                $validator2 = Validator::make($request->all(), [
                    'specific_phones' => 'required|string',
                ]);
                if ($validator2->fails()) {
                    notify()->error('Please enter at least one phone number', 'Error');
                    return redirect()->back();
                }
                $phones = collect(preg_split('/[\s,]+/', $request->specific_phones))
                    ->map(fn($p) => trim($p))
                    ->filter(fn($p) => !empty($p))
                    ->values();
            }

            if ($phones->isEmpty()) {
                notify()->warning('No recipients with phone numbers found.', 'Warning');
                return redirect()->back();
            }

            $sent  = 0;
            $failed = 0;

            foreach ($phones as $phone) {
                try {
                    $this->sendSms($phone, ['message_body' => $messageBody]);
                    $sent++;
                } catch (Exception $e) {
                    $failed++;
                }
            }

            if ($failed > 0) {
                notify()->warning("Sent to {$sent} recipients. {$failed} failed.", 'Partial Success');
            } else {
                notify()->success("SMS sent successfully to {$sent} recipients.", 'Success');
            }

        } catch (Exception $e) {
            notify()->error('Something went wrong: ' . $e->getMessage(), 'Error');
        }

        return redirect()->back();
    }
}