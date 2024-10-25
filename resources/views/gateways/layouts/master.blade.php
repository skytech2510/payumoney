<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Payment Via :x | :y', ['x' => ucfirst($gateway ), 'y' => settings('name') ]) }} </title>
    @include('merchantPayment.layouts.common.style')
</head>
<body class="bg-body-muted">
    <div class="container-fluid container-layout px-0">

        <div class="section-payment">

            <div class="transaction-details-module">

                <div class="total-amount">
                    <h2>{{ __('Transaction Details') }}</h2>
                    <div class="d-flex justify-content-between mb-10">
                        <p>{{ __('You are sending') }}</p>
                        <p>{{ __('Medium') }}</p>
                    </div>

                    <div class="d-flex justify-content-between mb-4">
                        <div>
                            <h3>
                                @php
                                    $totalAmount = isset($totalAmount) ? $totalAmount : 0;
                                @endphp
                                {{ moneyFormat( $currencyCode , formatNumber($total, $currency_id)) }}
                                <br>

                            </h3>
                        </div>
                        <div class="amount-logo" id="bank_logo">
                            <img src="{{ asset('public/dist/images/gateways/payments/' . strtolower($gateway) . '.png') }}" class="img-fluid">
                        </div>
                    </div>

                </div>

                @yield('content')

                <div class="d-flex justify-content-center align-items-center mt-2 back-direction">
                    <button  class="text-gray gilroy-medium d-inline-flex align-items-center position-relative back-btn bg-transparent border-0" id="goBackButton">
                        {!! svgIcons('left_angle') !!}
                        <span class="ms-1 back-btn ns depositConfirmBackBtnText">{{ __('Back') }}</span>
                    </button>
                </div>

            </div>
        </div>

    </div>
    @include('merchantPayment.layouts.common.script')
</body>
</html>

