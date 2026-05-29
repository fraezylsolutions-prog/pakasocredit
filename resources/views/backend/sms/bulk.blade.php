@extends('backend.layouts.app')
@section('title')
    {{ __('Bulk SMS') }}
@endsection
@section('content')
<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content">
                        <h2 class="title">{{ __('Bulk SMS') }}</h2>
                        <a href="{{ route('admin.template.sms.index') }}" class="title-btn">
                            <i data-lucide="file-text"></i>{{ __('SMS Templates') }}
                        </a>
                        <a href="{{ route('admin.settings.plugin', 'sms') }}" class="title-btn">
                            <i data-lucide="settings"></i>{{ __('SMS Config') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">

        {{-- Stats Row --}}
        <div class="row mb-3">
            <div class="col-xl-4 col-md-4 col-sm-6 col-12 mb-3">
                <div class="site-card text-center py-4">
                    <i data-lucide="users" style="width:36px;height:36px;color:var(--primary-color)"></i>
                    <h2 class="mt-2 mb-0">{{ $totalUsers }}</h2>
                    <p class="text-muted mb-0">{{ __('Active Users with Phone') }}</p>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 col-sm-6 col-12 mb-3">
                <div class="site-card text-center py-4">
                    <i data-lucide="user" style="width:36px;height:36px;color:var(--primary-color)"></i>
                    <h2 class="mt-2 mb-0">{{ $totalAll }}</h2>
                    <p class="text-muted mb-0">{{ __('All Users with Phone') }}</p>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 col-sm-6 col-12 mb-3">
                <div class="site-card text-center py-4">
                    <i data-lucide="message-square" style="width:36px;height:36px;color:var(--primary-color)"></i>
                    <h2 class="mt-2 mb-0">{{ $templates->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Active SMS Templates') }}</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-8 col-lg-8 col-md-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <h3 class="title">{{ __('Compose Bulk SMS') }}</h3>
                    </div>
                    <div class="site-card-body">
                        <form action="{{ route('admin.sms.bulk.send') }}" method="POST">
                            @csrf

                            {{-- Recipient Type --}}
                            <div class="site-input-groups">
                                <label class="box-input-label">{{ __('Send To') }} <span class="text-danger">*</span></label>
                                <select name="recipient_type" id="recipient_type" class="box-input" required>
                                    <option value="">{{ __('-- Select Recipients --') }}</option>
                                    <option value="active_users">
                                        {{ __('Active Users Only') }} ({{ $totalUsers }} {{ __('with phone') }})
                                    </option>
                                    <option value="all_users">
                                        {{ __('All Users including inactive') }} ({{ $totalAll }} {{ __('with phone') }})
                                    </option>
                                    <option value="specific">{{ __('Specific Phone Numbers') }}</option>
                                </select>
                            </div>

                            {{-- Specific Phones --}}
                            <div class="site-input-groups" id="specific_phones_group" style="display:none;">
                                <label class="box-input-label">
                                    {{ __('Phone Numbers') }}
                                    <small class="text-muted">
                                        — {{ __('comma or newline separated, include country code') }}
                                    </small>
                                </label>
                                <textarea name="specific_phones" class="form-textarea mb-1" rows="3"
                                    placeholder="+2348012345678, +2347098765432"></textarea>
                            </div>

                            {{-- Message Type --}}
                            <div class="site-input-groups">
                                <label class="box-input-label">{{ __('Message Type') }} <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4 mt-1 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="message_type"
                                            id="msg_free" value="free_text" checked>
                                        <label class="form-check-label" for="msg_free">
                                            <i data-lucide="edit-3" style="width:14px"></i>
                                            {{ __('Write Custom Message') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="message_type"
                                            id="msg_template" value="template">
                                        <label class="form-check-label" for="msg_template">
                                            <i data-lucide="file-text" style="width:14px"></i>
                                            {{ __('Use Existing Template') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Free Text Box --}}
                            <div class="site-input-groups" id="free_text_group">
                                <label class="box-input-label">{{ __('Message') }} <span class="text-danger">*</span></label>
                                <textarea name="message" id="sms_message" class="form-textarea mb-1" rows="5"
                                    placeholder="{{ __('Type your SMS message here...') }}"></textarea>
                                <small class="text-muted">
                                    <span id="char_count">0</span>/160 {{ __('characters') }} &nbsp;|&nbsp;
                                    <span id="sms_count">1</span> {{ __('SMS part(s)') }}
                                </small>
                            </div>

                            {{-- Template Selector --}}
                            <div class="site-input-groups" id="template_group" style="display:none;">
                                <label class="box-input-label">{{ __('Select Template') }} <span class="text-danger">*</span></label>
                                @if($templates->isEmpty())
                                    <p class="text-warning">
                                        {{ __('No active SMS templates found.') }}
                                        <a href="{{ route('admin.template.sms.index') }}">{{ __('Create one') }}</a>
                                    </p>
                                @else
                                    <select name="template_id" class="box-input" id="template_select">
                                        <option value="">{{ __('-- Select a Template --') }}</option>
                                        @foreach($templates as $tmpl)
                                            <option value="{{ $tmpl->id }}"
                                                data-body="{{ strip_tags($tmpl->message_body) }}">
                                                {{ $tmpl->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="template_preview" class="mt-2 p-3 rounded"
                                        style="background:var(--card-bg,#f8f9fa);border:1px solid var(--border-color,#dee2e6);display:none;">
                                        <small class="text-muted d-block mb-1"><strong>{{ __('Preview:') }}</strong></small>
                                        <p id="template_preview_text" class="mb-0 small"></p>
                                    </div>
                                @endif
                            </div>

                            {{-- Warning --}}
                            <div class="alert alert-warning d-flex align-items-start gap-2 mt-3">
                                <i data-lucide="alert-triangle" style="min-width:18px;margin-top:2px"></i>
                                <span class="small">
                                    {{ __('Ensure your SMS gateway is configured in') }}
                                    <a href="{{ route('admin.settings.plugin', 'sms') }}">{{ __('SMS Settings') }}</a>
                                    {{ __('before sending. Each recipient is charged as a separate SMS. This action cannot be undone.') }}
                                </span>
                            </div>

                            {{-- Note about subscribers --}}
                            <div class="alert alert-info d-flex align-items-start gap-2">
                                <i data-lucide="info" style="min-width:18px;margin-top:2px"></i>
                                <span class="small">
                                    {{ __('Note: Subscribers only have email addresses and cannot receive SMS. To reach subscribers, use') }}
                                    <a href="{{ route('admin.mail.send.subscriber') }}">{{ __('Send Email to Subscribers') }}</a>.
                                </span>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="action-btns mt-3">
                                <button type="submit" class="site-btn-sm primary-btn me-2" id="send_btn"
                                    onclick="return confirm('{{ __('Send bulk SMS to all selected recipients? This cannot be undone.') }}')">
                                    <i data-lucide="send"></i> {{ __('Send Bulk SMS') }}
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="site-btn-sm outline-btn">
                                    {{ __('Cancel') }}
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            {{-- Right Side Tips --}}
            <div class="col-xl-4 col-lg-4 col-md-12">
                <div class="site-card">
                    <div class="site-card-header">
                        <h3 class="title">{{ __('Tips') }}</h3>
                    </div>
                    <div class="site-card-body">
                        <ul class="small" style="padding-left:1.2rem;line-height:2">
                            <li>{{ __('Always include country code in phone numbers (e.g. +234...)') }}</li>
                            <li>{{ __('Keep messages under 160 characters to avoid splitting into multiple SMS') }}</li>
                            <li>{{ __('Test with a specific number first before sending to all users') }}</li>
                            <li>{{ __('Only users with phone numbers saved will receive SMS') }}</li>
                            <li>{{ __('Subscribers cannot receive SMS — they have email only') }}</li>
                            <li>{{ __('Configure Twilio or Nexmo in SMS Settings before sending') }}</li>
                        </ul>

                        <hr>
                        <p class="small text-muted mb-2"><strong>{{ __('Quick Links') }}</strong></p>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('admin.template.sms.index') }}" class="site-btn-xs outline-btn">
                                <i data-lucide="file-text"></i> {{ __('Manage SMS Templates') }}
                            </a>
                            <a href="{{ route('admin.settings.plugin', 'sms') }}" class="site-btn-xs outline-btn">
                                <i data-lucide="settings"></i> {{ __('SMS Gateway Config') }}
                            </a>
                            <a href="{{ route('admin.user.mail-send.all') }}" class="site-btn-xs outline-btn">
                                <i data-lucide="mail"></i> {{ __('Send Bulk Email Instead') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    // Toggle specific phones textarea
    document.getElementById('recipient_type').addEventListener('change', function () {
        document.getElementById('specific_phones_group').style.display =
            this.value === 'specific' ? 'block' : 'none';
    });

    // Toggle message type sections
    document.querySelectorAll('input[name="message_type"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.getElementById('free_text_group').style.display =
                this.value === 'free_text' ? 'block' : 'none';
            document.getElementById('template_group').style.display =
                this.value === 'template' ? 'block' : 'none';
        });
    });

    // Character & SMS part counter
    document.getElementById('sms_message').addEventListener('input', function () {
        const len = this.value.length;
        document.getElementById('char_count').textContent = len;
        document.getElementById('sms_count').textContent = len > 0 ? Math.ceil(len / 160) : 1;
    });

    // Template preview
    const templateSelect = document.getElementById('template_select');
    if (templateSelect) {
        templateSelect.addEventListener('change', function () {
            const body    = this.options[this.selectedIndex]?.dataset?.body || '';
            const preview = document.getElementById('template_preview');
            if (body) {
                document.getElementById('template_preview_text').textContent = body;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    }
</script>
@endpush