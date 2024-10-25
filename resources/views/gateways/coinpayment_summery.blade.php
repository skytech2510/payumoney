@extends('gateways.layouts.payment')

<link rel="stylesheet" href="{{ asset('public/frontend/customs/css/gateway/coinpayments.min.css') }}">

@section('content')
    <div class="bg-white transaction-summery pxy-62 webview-coinpayment">
        <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center">{{ __('Transaction Summery') }}</p>
        <form>
            <div class="mt-32">
                <div class="d-flex justify-content-between flex-wrap">
                    <div class="d-flex gap-1 mb-3">
                        <p class="mb-0 f-15 leading-17 gilroy-medium text-gray-100">{{ __('Status') }}: </p>
                        <p class="mb-0 f-15 leading-17 gilroy-medium text-gray-100">{{ $transactionInfo['result']['status_text'] }}</p>
                    </div>
                    <div class="d-flex gap-1 mb-3">
                        <p class="mb-0 f-15 leading-17 gilroy-medium text-gray-100">{{ __('Balance Remaining') }}: </p>
                        <p class="mb-0 f-15 leading-17 gilroy-medium text-gray-100">{{ $transactionInfo['result']['amountf'] . ' ' . $transactionInfo['result']['coin'] }}</p>
                    </div>
                </div>
                <div class="exchange-send-get d-flex justify-content-between mb-20">
                    <div class="send-left-box w-50">
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-gray-100">{{ __('Total amount to send') }}</p>
                        <p class="mb-0 f-24 l gilroy-Semibold mt-1">
                            <span class="text-dark">{{ $transactionInfo['result']['amountf'] }}</span>
                            <span class="text-primary">{{ $transactionInfo['result']['coin'] }}</span>
                        </p>
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-gray mt-1">
                            <span>{{ __('Total confirms need') }}: </span><strong class="text-primary px-1">{{ $transactionDetails['result']['confirms_needed'] }}</strong>
                        </p>
                    </div>
                    <div class="get-right-box w-50 align-center d-flex flex-column justify-content-center">
                        <p class="mb-0 f-14 leading-17 gilroy-medium text-light">{{ __('Recieve so far') }}</p>
                        <p class="mb-0 f-24 l gilroy-Semibold mt-1">
                            <span class="text-white">{{ $transactionInfo['result']['receivedf'] }}</span>
                            <span class="text-warning">{{ $transactionInfo['result']['coin'] }}</span>
                        </p>

                        <div class="right-box-icon">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12.5" cy="12.5" r="12.5" fill="white" />
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M10.5312 17.4705C10.2709 17.2102 10.2709 16.788 10.5312 16.5277L14.0598 12.9991L10.5312 9.47051C10.2708 9.21016 10.2708 8.78805 10.5312 8.5277C10.7915 8.26735 11.2137 8.26735 11.474 8.5277L15.474 12.5277C15.7344 12.788 15.7344 13.2102 15.474 13.4705L11.474 17.4705C11.2137 17.7309 10.7915 17.7309 10.5312 17.4705Z" fill="#6A6B87" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="right-qr-code text-center mb-20">
                    <img src="{{ $transactionDetails['result']['qrcode_url'] }}" alt="image">
                </div>
                <div class="left-merchant text-center mb-24">
                    <p class="mb-0 f-14 gilroy-medium text-danger">{{ __('Do not send value to us if address status is expired') }}</p>
                    <input type="hidden" name="" value="{{ $transactionInfo['result']['time_created'] }}" id="time_created">
                    <input type="hidden" name="" value="{{ $transactionInfo['result']['time_expires'] }}" id="time_expires">
                </div>
                <div class="row transac-summery gx-2 gy-2">
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 text-lg-end text-start w-break-all">{{ __('Send to Address') }} <span class="mx-1">:</span></p>
                    </div>
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 w-break-all">{{ $transactionInfo['result']['payment_address'] }}</p>
                    </div>
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 text-lg-end text-start w-break-all">{{ __('Time left for us to confirm Funds') }}<span class="mx-1">:</span></p>
                    </div>
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-Semibold text-dark timer" id="txt" data-seconds-left=""></p>
                    </div>
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 text-lg-end text-start w-break-all">{{ __('Payment Id') }}<span class="mx-1">:</span></p>
                    </div>
                    <div class="col-md-6 col-12">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 w-break-all">{{ $transactionDetails['result']['txn_id'] }}</p>
                    </div>
                    <div class="d-flex gap-20 justify-content-center align-items-center">
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 text-lg-end text-start w-break-all"><a href="{{ $transactionDetails['result']['status_url'] }}" target="_blank">{{ __('Alternative Link') }}</a></p>
                        <p class="mb-0"><span>|</span></p>
                        <p class="mb-0 f-16 leading-17 gilroy-medium text-gray-100 w-break-all"><a href="{{ url('transactions') }}">{{ __('Transaction Histories') }}</a></p>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
@push('js')
    <script src="{{ asset('public/user/customs/js/jquery.simple.timer.min.js') }}"></script>
    <script src="{{ asset('public/frontend/customs/js/gateways/coinpayments_summery.min.js') }}"></script>
    <script src="{{ asset('public/user/customs/js/reactwebview.min.js') }}"></script>
@endpush
