@extends('user.layouts.app')

@push('css')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Merchants') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title">{{ __('List of all the merchant accounts in one place') }}</p>
</div>
@include('user.common.alert')
<div class="d-flex justify-content-center mt-32 mb-4" id="merchantIndex">
    <a href="{{ route('user.merchants.create') }}" class="green-btn text-center cursor-pointer add-new-merchant bg-primary d-flex justify-content-center align-items-center">
        <span class="mb-0 f-14 leading-17 gilroy-medium text-white">+ {{ __('New Merchant') }}</span>
    </a>
</div>
@if ($merchants->count() > 0)
    @foreach($merchants as $merchant)
        <div class="merchat-details-row border-top-corner border-5FF bg-white shadow d-flex justify-content-between align-items-center">
            <div class="d-flex max-50 w-45">
                <div class="d-flex merchant-profile-div">
                    <div class="merchant-profile-img">
                        <img src="{{ image($merchant->logo, 'merchant') }}" alt="{{ __('Merchant') }}">
                    </div>
                </div>
                <div class="d-flex flex-wrap-mer ml-20 w-100 justify-content-between">
                    <div class="merchant-business-url w-200-mer">
                        <p class="mb-0 f-16 leading-20 gilroy-medium text-dark mt-2p">{{ $merchant->business_name }}</p>
                        <p class="mb-0 f-14 leading-16 gilroy-regular text-gray-100 mt-2 text-beak-all">{{ $merchant->site_url }}</p>
                    </div>
                    <div class="merchant-status w-176-mer">
                        <p class="mb-0 f-16 leading-20 gilroy-medium text-dark text-beak-all mt-2p">{{ $merchant->merchant_uuid }}</p>
                        <p class="mb-0 f-13 leading-16 gilroy-regular {{ getColor($merchant->status) }} mt-2">{{ $merchant->status }}</p>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-wrap-mer optimized justify-content-between w-45 align-items-center ">
                <div class="merchant-currency mer-w-30">
                <p class="mb-0 text-uppercase f-16 leading-20 l-sp64 gilroy-medium text-dark w-200-mer">{{ $merchant?->currency?->code }}</p>
                <p class="mb-0  mt-2 f-13 leading-16 gilroy-regular text-gray-100">{{ ucfirst($merchant->type) }}</p>  
                </div>
                <div class="merchant-action gap-reset div-5FF d-flex h-34 justify-content-end mer-w-70 position-r merchant-icon-section">
                    @if ($merchant->status == 'Approved')
                        @if($merchant->type == 'standard')
                            <!-- HTML form Generator Gear Icon -->
                            <div class="hover-setting pl-27 pr-27 border-px pt-3p show-tooltip" data-bs-toggle="tooltip" data-color="primary-top" data-bs-placement="top" title="{{ __('Generate HTML Payment Form') }}">
                                <a class="cursor-pointer gearBtn" 
                                data-type="{{ $merchant->type }}" 
                                data-currencyType="{{ $merchant?->currency?->type }}" 
                                data-merchantCurrencyCode="{{ !empty($merchant->currency) ? $merchant->currency->code :  $defaultWallet->currency->code }}" 
                                data-merchantCurrencyId="{{ !empty($merchant->currency) ? $merchant->currency->id : $defaultWallet->currency_id }}" 
                                data-marchantID="{{ $merchant->id }}"  
                                data-marchant="{{ $merchant->merchant_uuid }}" data-bs-toggle="modal" data-bs-target="#merchantModal">
                                    {!! svgIcons('gear_icon') !!}
                                </a>
                            </div>
                        @else
                            @if (!empty($merchant->appInfo->client_id) && !empty($merchant->appInfo->client_secret))
                                <!-- Express Merchant QrScanner Icon -->
                                <div class="hover-qr-code pr-27 border-px pt-3p">
                                    <div class="show-tooltip" data-bs-toggle="tooltip" data-color="primary-top" data-bs-placement="top" title="{{ __('Qr code') }}">
                                        <a class="cursor-pointer b-none bg-transparent" data-bs-toggle="modal" data-bs-target="#expressMerchantQrCodeModal" 
                                        data-clientId="{{ !empty($merchant->appInfo->client_id) ? $merchant->appInfo->client_id : '' }}" 
                                        data-clientSecret="{{ !empty($merchant->appInfo->client_secret) ? $merchant->appInfo->client_secret : '' }}" 
                                        data-merchantId="{{ $merchant->id }}"
                                        data-merchantDefaultCurrencyId="{{ !empty($merchant->currency) ? $merchant->currency->id : '' }}">
                                            {!! svgIcons('scanner_icon_sm') !!}
                                        </a>
                                    </div>
                                    
                                </div>
                                <!-- Client ID & Client Secret Gear Icon -->
                                <div class="hover-setting pl-27 pr-27 border-px pt-3p show-tooltip" data-bs-toggle="tooltip" data-color="primary-top" data-bs-placement="top" title="{{ __('Secret Codes') }}">
                                    <a class="cursor-pointer gearBtn" 
                                    data-client-id="{{ isset($merchant->appInfo->client_id) ? $merchant->appInfo->client_id : '' }}" data-client-secret="{{ isset($merchant->appInfo->client_secret) ? $merchant->appInfo->client_secret : '' }}" data-merchantCurrencyId="{{ !empty($merchant->currency) ? $merchant->currency->id : $defaultWallet->currency_id }}" data-marchantID="{{ $merchant->id }}" data-marchant="{{ $merchant->merchant_uuid }}" data-bs-toggle="modal" data-bs-target="#expressModal">
                                        {!! svgIcons('gear_icon') !!}
                                    </a>
                                </div>
                            @endif
                        @endif
                    @endif
                
                    <div  class="hover-qr-view pl-27 pr-27 cursor-pointer border-px pt-3p show-tooltip" data-bs-toggle="tooltip" data-color="primary-top" data-bs-placement="top" title="{{ __('Merchant Details') }}">
                        <a class="b-none bg-transparent" data-bs-toggle="modal" data-bs-target="#merchantModaldetails" data-id="{{ $merchant->id }}" 
                        data-note="{{ $merchant->note }}" 
                        data-logo="{{ image($merchant->logo, 'merchant') }}" 
                        data-statusColor="{{ getBgColor($merchant->status) }}" 
                        data-status="{{ $merchant->status }}" 
                        data-site_url="{{ $merchant->site_url }}" 
                        data-name="{{ $merchant->business_name }}" 
                        data-merchant_uuid="{{ $merchant->merchant_uuid }}" 
                        data-created_at="{{ dateFormat($merchant->created_at) }}"  
                        data-merchantCurrencyCode="{{ !empty($merchant->currency) ? $merchant->currency->code :  $defaultWallet->currency->code }}">
                        {!! svgIcons('eye_open_icon') !!}
                        </a>
                    </div>
                
                    <div class="hover-edit pt-3p show-tooltip" data-bs-toggle="tooltip" data-color="primary-top" data-bs-placement="top" title="{{ __('Edit') }}">
                        <a href="{{ route('user.merchants.edit', $merchant->id) }}" class="rtl-27 pl-27 cursor-pointer">
                        {!! svgIcons('edit_icon') !!}
                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Express merchant QrCode modal -->
    <div class="modal fade modal-overly" id="expressMerchantQrCodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog merchant-space">
            <div class="modal-dialog merchant-space">
                <div class="modal-content">
                    <div class="modal-header w-modal-header">
                        <p class="modal-title gilroy-Semibold text-dark">{{ __('Express Merchant QR Code') }}</p>
                        <button type="button" class="cursor-pointer close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <span class="close-div position-absolute modal-close-btn rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                                {!! svgIcons('cross_icon') !!}
                            </span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-pxy">
                        <div class="express-merchant-qr-section ">
                            <div class="row">
                                <div class="col-md-8 offset-md-2">
                                    <p class="mb-0 f-14 leading-22 text-dark gilroy-medium text-center mt-5p">{{ __('Copy the form code and place it on your website or use the QR code below for payment') }}</p>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-20 d-none loader-img">
                                <div>
                                    <img src="{{ image(null, 'loader') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-20">
                                <div class="express-merchant-qr-div">
                                    <img class="expressMerchantQrCodeImg" alt="{{ __('QrCode') }}">
                                </div>
                            </div>

                            <div class="d-flex justify-content-center mt-36 mb-10 r-mt-20 r-mb-20">
                                <a href="javascript:void(0)"
                                class="print-btn print-btn-merchant gap-2 d-flex justify-content-center align-items-center" id="qr-code-print-express">
                                    {!! svgIcons('printer') !!}
                                    <span class="f-14 leading-17 gilroy-medium">{{ __('Print') }}</span>
                                </a>
                                <div>
                                    <a href="javascript:void(0)" class="repeat-btn d-flex justify-content-center align-items-center ml-20 update-express-merchant-qr-code">
                                    <span class="gilroy-medium f-14 leading-17 gilroy-medium">{{ __('Generate Again') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Merchant Detail Modal -->
    <div class="modal fade modal-overly" id="merchantModaldetails" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog-width modal-dialog merchant">
            <div class="modal-content">
                <div class="m-header-top">
                    <button type="button" class="cursor-pointer close-btn btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-div position-absolute modal-close-btn rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                            {!! svgIcons('cross_icon') !!}
                        </span>
                    </button>
                </div>
                <div class="modal-body modal-body-30pxy">
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="merchant-details-parent bg-white">
                                <div class="merchant-details-header bg-white-50 d-flex">
                                    <div class="id-details-left d-flex justify-content-between flex-wrap align-items-center w-100">
                                        <div class="d-flex">
                                            <div class="id-details-img">
                                                <img src="" alt="{{ __('Merchant Image') }}" id="merchant-img">
                                            </div>
                                            <div class="id-title ml-20">
                                                <p class="mb-0 f-15 leading-18 gilroy-medium text-primary">{{ __('Merchant Details') }}</p>
                                                <p class="mb-0 f-20 leading-23 gilroy-Semibold text-dark mt-10">{{ __('ID') }}: <span id="merchant-uuid"></span></p>
                                            </div>
                                        </div>
                                        <a id="merchant-edit-route" class="green-btn edit-merchant-btn bg-primary f-14 leading-17 text-white gilroy-medium d-flex justify-content-center align-items-center r-mt-15">{{ __('Edit Merchant') }}</a>
                                    </div>
                                </div>
                                <div class="row mt-28">
                                    <div class="col-xl-5 col-12 merchant-link">
                                        <div class="details-section d-flex gap-14 flex-wrap">
                                            <p class="mb-0 text-dark f-28 leading-35 gilroy-Semibold" id="merchant-name"></p>
                                            <div class="status-w d-flex justify-content-center align-items-center mt-5p" id="merchant-status-bg">
                                                <p class="mb-0 f-12 leading-15 gilroy-medium text-white" id="merchant-status"></p>
                                            </div>
                                        </div>
                                        <div class="mt-9 link-a"> 
                                            <a href="javascript:void(0)" class="text-gray-100 f-16 leading-20 text-decoration-underline" id="merchant-site-url"></a>
                                        </div>
                                        <div class="mt-26">
                                            <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Currency') }}</p>
                                            <p class="mb-0 f-24 leading-30 text-dark gilroy-Semibold mt-2" id="merchant-currency"></p>
                                        </div>
                                        <div class="mt-28">
                                            <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Created on') }}</p>
                                            <p class="mb-0 f-16 leading-20 text-dark gilroy-medium text-dark mt-2" id="merchant-created-at"></p>
                                        </div>
                                    </div>
                                    <div class="col-xl-7 col-12">
                                        <div class="message-section mt-0 w-330">
                                            <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Message') }}</p>
                                            <p class="mb-0 f-14 leading-24 gilroy-medium text-dark mt-2" id="merchant-note"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Express Merchant Credential Modal View -->
    <div class="modal fade modal-overly" id="expressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog merchant-space">
            <div class="modal-content">
                <div class="modal-header w-modal-header">
                    <p class="modal-title gilroy-Semibold text-dark">{{ __('App Info / Merchant Credentials') }}</p>
                    <button type="button" class="cursor-pointer close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-div position-absolute modal-close-btn rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                            {!! svgIcons('cross_icon') !!}
                        </span>
                    </button>
                </div>
                <div class="modal-body clients-secret-id modal-body-pxy">
                    <div class="express-merchant-qr-section">
                        <!-- Client ID -->
                        <div class="d-flex justify-content-between m-address">
                            <p class="mb-0 gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-5p mt-28">{{ __('Client ID') }}</p>
                            <div class="copy-parent-div text-center" id="copy-parent-div-client">
                            <span class="f-12 gilroy-medium">{{ __('Copied') }}</span>
                            </div>
                        </div>
                        <div class="d-flex position-relative copy-div">
                            <input class="w-100 b-none form-control client-id input-form-control apply-bg gilroy-medium f-18 text-dark l-s0" type="text" id="client_id" readonly>
                            <button id="copyClientIdBtn" class="bg-unset flex-shrink-1 b-none credential copy-btn">
                            {!! svgIcons('copy_bg_icon') !!}
                            </button>
                        </div> 
                        <!-- Client Secret -->
                        <div class="d-flex justify-content-between m-address exp-merchant">
                            <p class="mb-0 gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-24">{{ __('Client Secret') }}</p>
                            <div class="copy-parent-div text-center " id="copy-parent-div-client-secret">
                            <span class="f-12 gilroy-medium">{{ __('Copied') }}</span>
                            </div>
                        </div>
                        <div class="d-flex position-relative copy-div">
                            <input class="w-100 b-none form-control client-secret input-form-control apply-bg gilroy-medium f-18 text-dark l-s0" type="text" id="client_secret" readonly>
                            <button id="copyClientSecretBtn" class="bg-unset flex-shrink-1 b-none credential copy-btn">
                            {!! svgIcons('copy_bg_icon') !!}
                            </button>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Standard Merchant Payment Form Modal View -->
    <div class="modal fade modal-overly" id="merchantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog-width modal-dialog merchant-space">
            <div class="modal-content">
                <div class="modal-header w-modal-header">
                    <p class="modal-title gilroy-Semibold text-dark">{{ __('HTML Form Generator') }}</p>
                    <button type="button" class="cursor-pointer close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-div position-absolute modal-close-btn rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                            {!! svgIcons('cross_icon') !!}
                        </span>
                    </button>
                </div>
                <div class="modal-body modal-body-pxy">
                    <div class="row gutter-20x">
                        <div class="col-xl-5 col-12">
                            <!-- Merchant Id -->
                            <div class="label-top">
                                <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 r-mt-amount">{{ __('Merchant ID') }}</label>
                                <input readonly type="text" class="gilroy-medium leading-20 form-control input-form-control input-form-control-withdraw apply-bg"
                                name="merchant_id" id="merchant_id">
                            </div>
                            <!-- Currency Id -->
                            <div class="label-top">
                                <input type="hidden" name="merchant_main_id" id="merchant_main_id">
                                <input type="hidden" name="currency_id" id="currency_id"/>
                                <input type="hidden" name="currency_type" id="currency_type"/>
                            </div>
                            <!-- Item Name -->
                            <div class="label-top">
                                <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount">{{ __('Item Name') }}</label>
                                <input type="text" class="form-control leading-20 input-form-control input-form-control-withdraw apply-bg" name="item_name" id="item_name">
                            </div>
                            <!-- Order Number -->
                            <div class="label-top">
                                <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount">{{ __('Order Number') }}</label>
                                <input type="text" class="form-control leading-20 input-form-control input-form-control-withdraw apply-bg" name="order" id="order">
                            </div>
                            <!-- Price -->
                            <div class="label-top">
                                <div class="d-flex justify-content-between">
                                <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount">{{ __('Price') }}</label>
                                <label class="gilroy-medium text-primary mb-2 f-14 leading-17 mt-20 r-mt-amount" id="merchantCurrencyCode"></label>
                                </div>
                                <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg l-s2" name="amount" id="amount" placeholder="" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)">
                            </div>
                            <!-- Custom -->
                            <div class="label-top">
                                <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount"> {{ __('Custom') }}</label>
                                <input type="text" class="form-control leading-20 input-form-control input-form-control-withdraw apply-bg" name="custom" id="custom">
                            </div>
                        </div>
                        <div class="col-xl-7 col-12">
                            <div class="d-flex justify-content-between r-mt-20">
                                <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100 mt-5p">{{ __('Generated HTML Form') }}</p>
                                <div class="d-flex">
                                    <div class="copy-parent-div-left text-end" id="copy-parent-div">
                                        <span class="f-12 gilroy-medium" id="copiedMessage">{{ __('Copied') }}</span>
                                    </div>
                                    <button id="click-to-copy" class="b-none bg-transparent click-to-copy-text">
                                        {!! svgIcons('copy_icon') !!}
                                    </button>
                                </div>
                            </div>
                            <!-- form -->
                            <div>
                                <p class="mb-0 formfield">
                                    <!-- Standard Merchant Payment Form -->
                                    <textarea rows="5" class="input-form-control focus-bgcolor text-start form-control position-relative overflow-auto form-copy thin-scrollbar merchant-textarea text-dark f-14 leading-24 bg-white-50 mt-8 h-textarea" name="html" id="result">
                                        <form method="POST" action="{{ route('user.merchant.payment_form') }}">
                                            <input type="hidden" name="order" id="result_order" value="#"/>
                                            <input type="hidden" name="merchant" id="result_merchant" value="#"/>
                                            <input type="hidden" name="merchant_id" id="result_merchant_id" value="#"/>
                                            <input type="hidden" name="item_name" id="result_item_name" value="Testing payment"/>
                                            <input type="hidden" name="amount" id="result_amount" value="#"/>
                                            <input type="hidden" name="custom" id="result_custom" value="comment"/>
                                            <button type="submit">{{ __('Submit') }}</button>
                                        </form>
                                    </textarea> 
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="generate-section row">
                        <div class="col-xl-5 col-12">
                            <button id="generate-standard-payment-form" class="mt-17 b-none w-100 generate-form bg-primary text-white f-16 leading-20 gilroy-medium d-flex justify-content-center align-items-center generate-standard-payment-form">{{ __('Generate Form') }}</button>
                        </div>
                        <div class="col-xl-7 col-12">
                            
                        </div>
                    </div>

                    <!-- Standard Merchant QrCode Section -->
                    <div class="merchant-qr-section d-none">
                        <div class="row mt-14">
                            <div class="col-md-8 offset-md-2">
                            <p class="mb-0 f-14 leading-24 text-dark gilroy-medium text-center mt-2">{{ __('Copy the form code and place it on your website or use the QR code below for payment') }}</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-20 loader-img d-none">
                            <img src="{{ image(null, 'loader') }}">
                        </div>

                        <div class="d-flex justify-content-center mt-20">
                            <div class="merchant-qr-div">
                            <img src="" alt="{{ __('QrCode') }}" id="qrCodeImg">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-28 r-mt-20 r-mb-20">
                            <a href="javascript:void(0)"
                            class="print-btn print-btn-merchant  d-flex gap-2 justify-content-center align-items-center" id="qr-code-print-standard">
                                {!! svgIcons('printer') !!}
                                <span>{{ __('Print') }}</span>
                            </a>
                            <div>
                                <a href="javascript:void(0)" class="repeat-btn d-flex justify-content-center align-items-center ml-20 generate-standard-payment-form">
                                  <span class="gilroy-medium">{{ __('Generate Again') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="notfound mt-16 bg-white p-4 shadow">
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-26">
            <div class="image-notfound">
                <img src="{{ asset('public/dist/images/not-found.png') }}" class="img-fluid">
            </div>
            <div class="text-notfound">
                <p class="mb-0 f-20 leading-25 gilroy-medium text-dark">{{ __('Sorry!') }} {{ __('No data found.') }}</p>
                <p class="mb-0 f-16 leading-24 gilroy-regular text-gray-100 mt-12">{{ __('The requested data does not exist for this feature overview.') }}</p>
            </div>
        </div>
    </div>
@endif


<div class="p-0">
    {{ $merchants->links('vendor.pagination.bootstrap-5') }}
</div>

@endsection

@push('js')

    @include('common.restrict_number_to_pref_decimal')
    @include('common.restrict_character_decimal_point')

<script>
    'use strict';
    var printText = "{{ __('Print') }}";
    var payNowText = "{{ __('Pay Now') }}";
    var failedText = "{{ __('Failed!') }}";
    var submitText = "{{ __('Submit') }}";
    var paymentFormUrl = "{{ route('user.merchant.payment_form') }}";
    var standardMerchantQrCodeUrl = "{{ route('user.standard_merchant.payment_qrcode') }}";
    var expressMerchantQrCodeUrl = "{{ route('user.express_merchant.qrcode') }}";
    var expressMerchantGetQrCodeUrl = "{{ route('user.express_merchant.get_qrcode') }}";
    var flasheshDarkPath = "{{ asset('public/frontend/templates/js/flashesh-dark.min.js') }}";
    var bootstrapCssPath = "{{ asset('public/dist/libraries/bootstrap-5.0.2/css/bootstrap.min.css') }}";
    var styleCssPath = "{{ asset('public/frontend/templates/css/style.min.css') }}";
    var favIcon = "{{ faviconPath() }}";
    var jqueryPath = "{{ asset('public/dist/libraries/jquery-3.6.1/jquery-3.6.1.min.js') }}";
    var bootstrapJsPath = "{{ asset('public/dist/libraries/bootstrap-5.0.2/js/bootstrap.min.js') }}";
    var mainJsPath = "{{ asset('public/frontend/templates/js/main.min.js') }}";
    var fontLink = "https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700&display=swap"
    var payButtonTitle = "{{ __('You\'re almost there!') }}";
    var payButtonTxt = "{{ __('Only a few steps to complete your payment. Click the button to continue.') }}";
    var appName = "{{ settings('name') }}";
</script>

 <script src="{{ asset('public/dist/libraries/sweetalert2/sweetalert2.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/user/customs/js/merchant.min.js') }}" type="text/javascript"></script>
@endpush