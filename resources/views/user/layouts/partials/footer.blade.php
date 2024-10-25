<footer class="d-flex align-items-center justify-content-between bg-white w-100 px-4 footer-sec">
    <div class="res-order d-flex align-items-center">
        <p class="mb-0 gilroy-medium">{{ __('Copyright') }} &copy; {{ date('Y') }}&nbsp;<a href="{{ url('/') }}" class="link-text">{{ settings('name') }}</a>&nbsp;|&nbsp;{{ __('All Rights Reserved.') }}</p>
        <span class="d-none">{{ config('paymoney.version') }}</span>
    </div>
    <div class="d-flex f-link align-items-center">
        <div>
            <div class="d-flex align-items-center text-gray-100 f-13 blink-w sp" id="select_language">
                <div class="form-group selectParent f-13">
                    <select class="select2 form-control f-13 mb-2n" data-minimum-results-for-search="Infinity" id="select-height">
                        @foreach (getLanguagesListAtFooterFrontEnd() as $lang)
                            <option class="f-13 gilroy-medium" {{ \Session::get('dflt_lang') == $lang->short_name ? 'selected' : '' }} value='{{ $lang->short_name }}'>{{ $lang->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</footer>