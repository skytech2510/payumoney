<div class="box box-primary">

    <div class="box-header with-border ps-3">
        <h3 class="box-title underline">{{ __('Payment Methods') }}</h3>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked flex-column">
			@if ($currency->type == 'fiat')
                <li {{ isset($list_menu) && $list_menu == 'stripe' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/stripe/' . $currency->id) }}'>{{ __('Stripe') }}</a>
                </li>

                <li {{ isset($list_menu) && $list_menu == 'paypal' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/paypal/' . $currency->id) }}'>{{ __('Paypal') }}</a>
                </li>

                <li {{ isset($list_menu) && $list_menu == 'payUmoney' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/payUmoney/' . $currency->id) }}'>{{ __('PayUMoney') }}</a>
                </li>

                <li {{ isset($list_menu) && $list_menu == 'coinpayments' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/coinpayments/' . $currency->id) }}'>{{ __('CoinPayments') }}</a>
                </li>
                <li {{ isset($list_menu) && $list_menu == 'payeer' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/payeer/' . $currency->id) }}'>{{ __('Payeer') }}</a>
                </li>
                <li {{ isset($list_menu) && $list_menu == 'bank' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/bank/' . $currency->id) }}'>{{ __('Banks') }}</a>
                </li>
                @php
                    $modules = addonPaymentMethods('Wallet');
                    $type = array_column($modules, 'type');
                @endphp
                @if (array_filter($type))
                <li {{ isset($list_menu) && $list_menu == 'mts' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/mts/' . $currency->id) }}'>{{ __('Wallet') }}</a>
                </li>
                @endif

                @if (config('mobilemoney.is_active'))
                    <li {{ isset($list_menu) && $list_menu == 'mobilemoney' ? 'class=active' : '' }}>
                        <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/mobilemoney/' . $currency->id) }}'>{{ __('MobileMoney') }}</a>
                    </li>
                @endif
			@elseif($currency->type == 'crypto')
                <li {{ isset($list_menu) && $list_menu == 'coinpayments' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/coinpayments/' . $currency->id) }}'>{{ __('Coinpayments') }}</a>
                </li>
                @php
                    $modules = addonPaymentMethods('Wallet');
                    $type = array_column($modules, 'type');
                @endphp
                @if (array_filter($type))
                <li {{ isset($list_menu) && $list_menu == 'mts' ? 'class=active' : '' }}>
                    <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/mts/' . $currency->id) }}'>{{ __('Wallet') }}</a>
                </li>
                @endif
			@endif
            <li {{ isset($list_menu) && $list_menu == 'coinbase' ? 'class=active' : '' }}>
                <a data-spinner="true" href='{{ url(config('adminPrefix') . '/settings/payment-methods/coinbase/' . $currency->id) }}'>{{ __('Coinbase') }}</a>
            </li>
        </ul>
    </div>
</div>
