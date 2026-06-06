@extends('frontend::layouts.user')
@section('title')
    {{ __('Fund Transfer') }}
@endsection
@section('content')
    <div class="row">
        @include('frontend::fund_transfer.include.__header')

        <div class="col-xl-12 col-lg-12 col-md-12 col-12">
            <div class="site-card">
                <div class="site-card-header">
                    <div class="title">{{ __('Fund Transfer') }}</div>
                    <div class="card-header-links">
                        <a href="#" class="card-header-link" data-bs-toggle="modal"
                           data-bs-target="#addBox">
                            <i data-lucide="plus-circle"></i>{{ __('Add Beneficiary') }}
                        </a>
                    </div>
                </div>

                {{-- ── Transfer Type Selector (NEW) ────────────────────────────── --}}
                <div class="site-card-body pb-0">
                    <div class="step-details-form mb-3">
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="inputs">
                                    <label class="input-label">{{ __('Transfer Type') }}<span class="required">*</span></label>
                                    <div class="d-flex gap-3 flex-wrap">
                                        {{-- Automatic Transfer --}}
                                        <label class="transfer-type-card" id="autoTransferCard" style="cursor:pointer;">
                                            <input type="radio" name="transfer_type" value="auto"
                                                   id="autoTransfer" class="d-none" checked>
                                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border"
                                                 id="autoTransferBox"
                                                 style="border-color:#6C3BCE !important;background:rgba(108,59,206,0.06);min-width:200px;">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width:40px;height:40px;background:#6C3BCE;flex-shrink:0;">
                                                    <i data-lucide="zap" style="color:#fff;width:18px;height:18px;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px;color:#1A1A2E;">
                                                        {{ __('Instant Transfer') }}
                                                    </div>
                                                    <div class="text-muted" style="font-size:11px;">
                                                        {{ __('Transfer within the platform') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>

                                        {{-- Manual Transfer --}}
                                        <label class="transfer-type-card" id="manualTransferCard" style="cursor:pointer;">
                                            <input type="radio" name="transfer_type" value="manual"
                                                   id="manualTransfer" class="d-none">
                                            <div class="d-flex align-items-center gap-3 p-3 rounded-3 border"
                                                 id="manualTransferBox"
                                                 style="border-color:#dee2e6;min-width:200px;">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center"
                                                     style="width:40px;height:40px;background:#f0f0f0;flex-shrink:0;">
                                                    <i data-lucide="landmark" style="color:#666;width:18px;height:18px;"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold" style="font-size:13px;color:#1A1A2E;">
                                                        {{ __('Manual Transfer') }}
                                                    </div>
                                                    <div class="text-muted" style="font-size:11px;">
                                                        {{ __('Bank transfer via Pakaso') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- ── End Transfer Type Selector ───────────────────────────────── --}}

                {{-- ── Automatic Transfer Form (existing — unchanged) ──────────── --}}
                <div id="autoTransferSection">
                    <form action="{{ route('user.fund_transfer.transfer') }}" method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="site-card-body">
                            <div class="step-details-form mb-4">
                                <div class="row" id="dynamic-custom-fields">

                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="inputs">
                                            <label for="" class="input-label">{{ __('Select Bank') }}<span
                                                    class="required">*</span></label>
                                            <select name="bank_id"
                                                    class="box-input select2-basic-active"
                                                    id="bankId">
                                                <option value="" disabled selected>--{{ __('Select Bank') }}--</option>
                                                <option value="0">{{ __('Own Bank') }}</option>
                                                @foreach ($banks as $bank)
                                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="input-info-text charge"></div>
                                        </div>
                                    </div>

                                    @if (setting('multiple_currency', 'permission'))
                                        <div class="col-xl-4 col-lg-4 col-md-4">
                                            <div class="inputs">
                                                <label for="" class="input-label">{{ __('Wallet') }}<span
                                                        class="required">*</span></label>
                                                <select name="wallet_type" class="box-input"
                                                        id="walletSelect">
                                                    <option value="default"
                                                            data-currency="{{ setting('site_currency') }}"
                                                            selected>
                                                        {{ __('Default Wallet') }}</option>
                                                    @foreach ($wallets as $wallet)
                                                        <option value="{{ $wallet->id }}"
                                                                @selected($code == $wallet->currency?->code)
                                                                data-currency="{{ $wallet->currency?->code }}">
                                                            {{ $wallet?->currency?->name }}
                                                            ({{ $wallet?->currency?->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="inputs">
                                            <label for="" class="input-label">{{ __('Select Beneficiary') }} </label>
                                            <select name="beneficiary_id"
                                                    class="box-input select2-basic-active"
                                                    id="beneficiaryId">
                                                <option value=""
                                                        selected>--{{ __('Beneficiary') }}--</option>
                                            </select>
                                            <div class="input-info-text transfer"></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 col-md-6 custom-fields">
                                        <div class="inputs">
                                            <label for=""
                                                   class="input-label">{{ __('Account Number') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       id="account_number"
                                                       name="manual_data[account_number]">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="inputs">
                                            <label for="" class="input-label">{{ __('Enter Amount') }}<span
                                                    class="required">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       id="amount" name="amount">
                                                <span class="input-group-text"
                                                      id="amountCurrency">{{ $currency }}</span>
                                            </div>
                                            <div class="input-info-text min-max"></div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 col-md-6 custom-fields">
                                        <div class="inputs">
                                            <label for=""
                                                   class="input-label">{{ __('Name on account') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       id="account_name"
                                                       name="manual_data[account_name]">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-lg-6 col-md-6 custom-fields"
                                         id="branch_name_field">
                                        <div class="inputs">
                                            <label for=""
                                                   class="input-label">{{ __('Branch Name') }}</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control"
                                                       id="branch_name"
                                                       name="manual_data[branch_name]">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xl-12 col-lg-12 col-md-12">
                                    <div class="inputs">
                                        <label for="">{{ __('Purpose of transfer(Optional)') }}</label>
                                        <textarea class="box-textarea" rows="3"
                                                  name="purpose"></textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Transfer Review (existing — unchanged) --}}
                            <div class="site-card">
                                <div class="site-card-header">
                                    <div class="title-small">{{ __('Transfer Review Details:') }}</div>
                                </div>
                                <div class="site-card-body p-0 overflow-x-auto">
                                    <div class="site-custom-table site-custom-table-sm">
                                        <div class="contents">
                                            <div class="site-table-list">
                                                <div class="site-table-col">
                                                    <div class="trx fw-bold">{{ __('Amount:') }}</div>
                                                </div>
                                                <div class="site-table-col">
                                                    <div class="fw-bold amount"><span class="currency"></span></div>
                                                </div>
                                            </div>
                                            <div class="site-table-list">
                                                <div class="site-table-col">
                                                    <div class="trx fw-bold">{{ __('Charge:') }}</div>
                                                </div>
                                                <div class="site-table-col">
                                                    <div class="red-color fw-bold charge2"></div>
                                                </div>
                                            </div>
                                            <div class="site-table-list">
                                                <div class="site-table-col">
                                                    <div class="trx fw-bold">{{ __('Bank Name:') }}</div>
                                                </div>
                                                <div class="site-table-col">
                                                    <div class="fw-bold bank-name-container d-none">
                                                        <span
                                                            class="type site-badge badge-primary bank_name"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="site-table-list">
                                                <div class="site-table-col">
                                                    <div class="trx fw-bold">{{ __('Total:') }}</div>
                                                </div>
                                                <div class="site-table-col">
                                                    <div class="fw-bold total"></div>
                                                </div>
                                            </div>
                                            <div class="site-table-list">
                                                <div class="site-table-col">
                                                    <div class="trx fw-bold">{{ __('Transferable Amount:') }}</div>
                                                </div>
                                                <div class="site-table-col">
                                                    <div class="fw-bold pay-amount"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status'))
                                    type="button"
                                    data-bs-toggle="modal"
                                    data-bs-target="#passcode"
                                @else
                                    type="submit"
                                @endif
                                class="site-btn polis-btn">
                                <i data-lucide="send"></i> {{ __('Transfer the fund') }}
                            </button>
                        </div>

                        @if (auth()->user()->passcode !== null && setting('fund_transfer_passcode_status'))
                            <div class="modal fade" id="passcode" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-md modal-dialog-centered">
                                    <div class="modal-content site-table-modal">
                                        <div class="modal-body popup-body">
                                            <button type="button" class="modal-btn-close"
                                                    data-bs-dismiss="modal" aria-label="Close">
                                                <i data-lucide="x"></i>
                                            </button>
                                            <div class="popup-body-text">
                                                <div class="title">{{ __('Confirm Your Passcode') }}</div>
                                                <div class="step-details-form">
                                                    <div class="row">
                                                        <div class="col-xl-12">
                                                            <div class="inputs">
                                                                <label for=""
                                                                       class="input-label">{{ __('Passcode') }}<span
                                                                        class="required">*</span></label>
                                                                <input type="password" class="box-input"
                                                                       name="passcode" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="action-btns">
                                                    <button type="submit"
                                                            class="site-btn-sm primary-btn me-2">
                                                        <i data-lucide="check"></i>
                                                        {{ __('Confirm') }}
                                                    </button>
                                                    <button type="button" class="site-btn-sm red-btn"
                                                            data-bs-dismiss="modal"
                                                            aria-label="Close">
                                                        <i data-lucide="x"></i>
                                                        {{ __('Close') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                {{-- ── End Automatic Transfer Form ──────────────────────────────── --}}

            </div>
        </div>

        @include('frontend::fund_transfer.include.__add_beneficiary')
    </div>
@endsection

@section('script')
<script>
$(document).ready(function () {
    "use strict";

    // ── Transfer Type Toggle ──────────────────────────────────────────────────
    $('input[name="transfer_type"]').on('change', function () {
        if ($(this).val() === 'manual') {
            // Redirect to manual fund transfer page
            window.location.href = '{{ route("user.manual-deposit.create") }}?type=transfer';
        } else {
            // Show automatic transfer form
            $('#autoTransferSection').show();
            $('#autoTransferBox').css({
                'border-color': '#6C3BCE',
                'background': 'rgba(108,59,206,0.06)'
            });
            $('#manualTransferBox').css({
                'border-color': '#dee2e6',
                'background': 'transparent'
            });
        }
    });

    // Style toggle on click
    $('#autoTransferCard').on('click', function () {
        $('#autoTransferBox').css({ 'border-color': '#6C3BCE !important', 'background': 'rgba(108,59,206,0.06)' });
        $('#manualTransferBox').css({ 'border-color': '#dee2e6', 'background': 'transparent' });
    });

    $('#manualTransferCard').on('click', function () {
        $('#manualTransferBox').css({ 'border-color': '#6C3BCE', 'background': 'rgba(108,59,206,0.06)' });
        $('#autoTransferBox').css({ 'border-color': '#dee2e6', 'background': 'transparent' });
    });

    // ── Existing JS (unchanged) ───────────────────────────────────────────────
    var isPhysicalBank = '{{ setting('multi_branch', 'permission') }}';
    if (!isPhysicalBank) {
        $('#bankId').on('change', function () {
            if ($(this).val() == 0) {
                $('#branch_name_field').addClass('d-none');
            } else {
                $('#branch_name_field').removeClass('d-none');
            }
        });
    }

    var currency = "{{ setting('site_currency') }}";

    const onWalletChange = function () {
        currency = $('#walletSelect').find(':selected').data('currency');
        $('#amountCurrency').text(currency);
    };

    @if (setting('multiple_currency', 'permission'))
        $('#walletSelect').on('change', function () {
            onWalletChange();
            onAmountChange();
            onChangeBank();
        });
        onWalletChange();
    @endif

    $('#account_number').on('input', function () {
        $.ajax({
            type: 'GET',
            url: '/user/search-by-account-number/' + $(this).val(),
            success: function (data) {
                $('#account_name').val(data.name);
                $('#branch_name').val(data.branch_name);
            },
            error: function () {
                $('#account_name').val('');
                $('#branch_name').val('');
            }
        });
    });

    $('#bank_id').select2({ dropdownParent: $('#addBox') });
    $('#walletSelect').select2();
    $('.select2-basic-active').select2({ minimumResultsForSearch: Infinity });

    $('#beneficiaryId').on('change', function () {
        customFieldsVisibility();
        onAmountChange();
    });

    function customFieldsVisibility() {
        let fields = $('.custom-fields');
        if ($('#beneficiaryId').val() != '') {
            fields.hide();
        } else {
            fields.show();
        }
    }

    $('.add-beneficiary').niceSelect();
    $('.edit-beneficiary').niceSelect();

    $('#bank_name').on('change', function (e) {
        if ($(this).val() == null) {
            $('#branch_name_sec').hide();
        } else {
            $('#branch_name_sec').show();
        }
        onAmountChange();
    });

    var globalData;

    $('#bankId').change(function () {
        onChangeBank();
        onAmountChange();
    });

    const onChangeBank = function () {
        var bankId = $('#bankId').val();
        $.ajax({
            type: 'GET',
            url: '/user/fund-transfer/beneficiary-details/' + bankId,
            data: { currency_code: currency },
            success: function (data) {
                $('#beneficiaryId').empty();
                $('#dynamic-custom-fields .custom-fields.dynamic').remove();
                globalData = data.banksData;
                $('#beneficiaryId').append('<option value="" selected>--{{ __('Beneficiary') }}--</option>');
                $.each(data.beneficiaries, function (key, beneficiary) {
                    let accountNumber = beneficiary.account_number;
                    $('#beneficiaryId').append('<option value="' + beneficiary.id + '">' +
                        beneficiary.account_name + ' **** ' + accountNumber.slice(-4) + '</option>');
                });
                if (bankId != 0) {
                    $('.bank_name').text(data.banksData.name);
                    $('.bank-name-container').removeClass('d-none');
                    var img = '<img class="table-icon" src="../assets/' + data.banksData.logo + '">';
                    $('#logo').html(img);
                    $('.charge').text('{{ __("Charge") }} ' + data.banksData.charge + ' ' +
                        (data.banksData.charge_type === 'percentage' ? ' % ' : currency));
                    $('.min-max').text('{{ __("Minimum") }} ' + data.banksData.minimum_transfer + ' ' +
                        currency + ' {{ __("and") }} {{ __("Maximum") }} ' +
                        data.banksData.maximum_transfer + ' ' + currency);
                    $('.transfer').text('{{ __("Transfer in") }}: ' + data.banksData.processing_time +
                        ' ' + data.banksData.processing_type);
                    if (data.customFields) {
                        const dynamicFields = $(data.customFields).addClass('dynamic');
                        $('#dynamic-custom-fields').append(dynamicFields);
                        imagePreview();
                    }
                } else {
                    $('.bank_name').text('Own Bank');
                    $('.bank-name-container').removeClass('d-none');
                    $('.charge').text('{{ __("Charge") }} ' + data.banksData.charge + ' ' +
                        (data.banksData.charge_type === 'percentage' ? ' % ' : currency));
                    $('.min-max').text('{{ __("Minimum") }} ' + data.banksData.minimum_transfer + ' ' +
                        currency + ' {{ __("and") }} {{ __("Maximum") }} ' +
                        data.banksData.maximum_transfer + ' ' + currency);
                    $('.transfer').text('{{ __("Instant Transfer") }}');
                    $('#logo_sec').hide();
                }
                customFieldsVisibility();
            },
            error: function () {
                $('.bank_name').text('');
                $('.bank-name-container').addClass('d-none');
                $('#dynamic-custom-fields .custom-fields.dynamic').remove();
                customFieldsVisibility();
            }
        });
    };

    const onAmountChange = function () {
        var amount = $('#amount').val();
        $('.amount').text((Number(amount) + ' ' + currency));
        $('.currency').text(currency);
        var charge = globalData?.charge_type === 'percentage' ?
            calPercentage(amount, globalData?.charge) : globalData?.charge;
        $('.charge2').text((charge ?? 0) + ' ' + currency);
        var total = (Number(amount ?? 0) + Number(charge ?? 0));
        $('.total').text(total + ' ' + currency);
        var payTotal = Number(amount);
        $('.pay-amount').text(payTotal + ' ' + currency);
    };

    $('#amount').on('keyup', function () {
        onAmountChange();
    });
});
</script>
@endsection
