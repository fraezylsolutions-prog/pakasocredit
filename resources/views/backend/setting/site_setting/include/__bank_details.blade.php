{{--
    resources/views/backend/setting/site_setting/include/__bank_details.blade.php
    This file is auto-loaded by the settings system via config/setting.php
    section name: bank_details
--}}
<div class="col-xl-6 col-lg-12 col-md-12 col-12">
    <div class="site-card">
        <div class="site-card-header">
            <h3 class="title">{{ __($fields['title']) }}</h3>
        </div>
        <div class="site-card-body">

            <form action="{{ route('admin.settings.update') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="section" value="bank_details">

                {{-- Bank Name --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Bank Name') }}
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <input type="text"
                               class="form-control site-input"
                               name="manual_deposit_bank_name"
                               value="{{ setting('manual_deposit_bank_name', 'bank_details', 'Sterling Bank') }}"
                               placeholder="e.g. Sterling Bank">
                    </div>
                </div>

                {{-- Account Name --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Account Name') }}
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <input type="text"
                               class="form-control site-input"
                               name="manual_deposit_account_name"
                               value="{{ setting('manual_deposit_account_name', 'bank_details', 'Pakaso Credit Limited') }}"
                               placeholder="e.g. Pakaso Credit Limited">
                    </div>
                </div>

                {{-- Account Number --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Account Number') }}
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <input type="text"
                               class="form-control site-input"
                               name="manual_deposit_account_number"
                               value="{{ setting('manual_deposit_account_number', 'bank_details', '') }}"
                               placeholder="e.g. 0142871404"
                               maxlength="20">
                    </div>
                </div>

                {{-- Bank Branch --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Bank Branch') }}
                        <small class="text-muted d-block">({{ __('optional') }})</small>
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <input type="text"
                               class="form-control site-input"
                               name="manual_deposit_bank_branch"
                               value="{{ setting('manual_deposit_bank_branch', 'bank_details', '') }}"
                               placeholder="e.g. Victoria Island Branch">
                    </div>
                </div>

                {{-- Instructions --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Payment Instructions') }}
                        <small class="text-muted d-block">({{ __('optional') }})</small>
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <textarea class="form-control site-input"
                                  name="manual_deposit_instructions"
                                  rows="2"
                                  placeholder="{{ __('e.g. Use your registered phone number as narration') }}">{{ setting('manual_deposit_instructions', 'bank_details', '') }}</textarea>
                    </div>
                </div>

                {{-- Min Deposit --}}
                <div class="site-input-groups row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Min Deposit Amount') }}
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <input type="number"
                               class="form-control site-input"
                               name="min_manual_deposit"
                               value="{{ setting('min_manual_deposit', 'bank_details', '1000') }}"
                               min="1"
                               placeholder="1000">
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="site-input-groups row mb-4">
                    <div class="col-xl-4 col-lg-4 col-md-3 col-12 col-label">
                        {{ __('Live Preview') }}
                    </div>
                    <div class="col-xl-8 col-lg-8 col-md-9 col-12">
                        <div class="p-3 rounded-3"
                             style="background:linear-gradient(135deg,#6C3BCE,#4A1F9E);color:#fff;">
                            <div style="font-size:10px;opacity:0.6;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px;">
                                {{ __('Users will see this') }}
                            </div>
                            <div class="mb-2">
                                <div style="font-size:11px;opacity:0.6;">{{ __('Bank') }}</div>
                                <div class="fw-bold" id="preview_bank">
                                    {{ setting('manual_deposit_bank_name', 'bank_details', '—') }}
                                </div>
                            </div>
                            <div class="mb-2">
                                <div style="font-size:11px;opacity:0.6;">{{ __('Account Name') }}</div>
                                <div class="fw-bold" id="preview_name">
                                    {{ setting('manual_deposit_account_name', 'bank_details', '—') }}
                                </div>
                            </div>
                            <div>
                                <div style="font-size:11px;opacity:0.6;">{{ __('Account Number') }}</div>
                                <div class="fw-bold" id="preview_number"
                                     style="font-size:18px;letter-spacing:2px;">
                                    {{ setting('manual_deposit_account_number', 'bank_details', '——————————') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="row">
                    <div class="offset-sm-4 col-sm-8">
                        <button type="submit" class="site-btn-sm primary-btn w-100">
                            {{ __('Save Bank Details') }}
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@push('single-script')
<script>
(function ($) {
    'use strict';
    $('input[name="manual_deposit_bank_name"]').on('input', function () {
        $('#preview_bank').text($(this).val() || '—');
    });
    $('input[name="manual_deposit_account_name"]').on('input', function () {
        $('#preview_name').text($(this).val() || '—');
    });
    $('input[name="manual_deposit_account_number"]').on('input', function () {
        $('#preview_number').text($(this).val() || '——————————');
    });
})(jQuery);
</script>
@endpush
