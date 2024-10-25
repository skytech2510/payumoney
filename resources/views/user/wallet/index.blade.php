@extends('user.layouts.app')

@section('content')
<div class="pb-34">
    <div class="px-61 pb-20 helper-size">
      <p class="mb-0 f-26 gilroy-Semibold text-uppercase text-center text-dark">{{ __('Wallet List') }}</p>
      <p class="mb-0 text-center f-16 leading-26 gilroy-medium text-gray-100 dark-c dark-p mt-8">{{ __('Here you will get all of your Fiat and Crypto wallets including default one. You can also perform crypto send/receive of your crypto coins.') }}</p>
    </div>
    <div class="px-28 helper-div">
        <div class="row r-mt-n">
            @if($wallets->count() > 0)
                @foreach ($wallets as $wallet)
                    @php
                        $walletCurrencyCode = encrypt(optional($wallet->currency)->code);
                        $walletId = encrypt($wallet->id);
                        $provider = isset($wallet->cryptoAssetApiLogs->payment_method->name) && !empty($wallet->cryptoAssetApiLogs->payment_method->name) ? strtolower($wallet->cryptoAssetApiLogs->payment_method->name): '';
                    @endphp
                    <div class="col-12 col-xl-6 mt-19">
                        <div class="balance-box">
                            <div class="d-flex justify-content-between">
                                <div class="wallet-left-box d-flex gap-18">
                                    <div class="curency-box d-flex align-items-center justify-content-center">
                                        <img src="{{ image($wallet->currency?->logo, 'currency') }}" alt="{{ __('Currency') }}">             
                                    </div>
                                    <div class="mt-n3p span-currency">
                                        <span class="f-15 gilroy-medium text-gray">{{ ucwords(str_replace('_', ' ', $wallet->currency?->type)) }}</span>
                                        <p class="mb-0 mt-6"><span class="f-28 gilroy-Semibold text-dark">{{ $wallet->currency?->code }}</span><span class="ml-2p f-15 text-primary gilroy-medium">{{ $wallet->is_default == 'Yes' ? '(default)' : '' }}</span></p>
                                    </div>
                                </div>
                                <div class="wallet-right-box mt-n3p span-currency text-end">
                                    <span class="f-15 gilroy-medium text-gray">{{ __('Balance') }}</span>
                                    <p class="mb-0 mt-6 f-28 gilroy-Semibold text-dark l-s2">{{ formatNumber($wallet->balance, $wallet->currency?->id) }}</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between span-currency">
                                <div class="currency-mt-32">
                                    @php
                                        $lastTransaction = \App\Models\Transaction::with('transaction_type')->where('currency_id', $wallet->currency?->id)->where(function($query){
                                            $query->where('user_id', auth()->id())->orWhere('end_user_id', auth()->id());
                                        })->latest()->first();
                                    @endphp
                                    @if (is_null($lastTransaction)) 
                                        <p class="text-gray mb-0 f-12 leading-16 gilroy-medium">{{ __('Last Action') }}: 
                                            <span class="text-dark">{{ __('No transaction available.') }}</span> 
                                        </p>
                                    @else
                                        <p class="text-gray mb-0 f-12 leading-16 gilroy-medium">{{ __('Last Action') }}: 
                                            <span class="text-dark">{{ moneyFormat($wallet->currency->symbol, formatNumber($lastTransaction->subtotal)) }}</span> 
                                            @php
                                                if (!empty($lastTransaction)) {
                                                    if ($lastTransaction->transaction_type->name == 'Transferred') {
                                                        $transactionName = 'Money Transfer';
                                                    } elseif ($lastTransaction->transaction_type->name == 'Received') {
                                                        $transactionName = 'Money Received';
                                                    } elseif ($lastTransaction->transaction_type->name == 'Exchange_From' || $lastTransaction->transaction_type->name == 'Exchange_To') {
                                                        $transactionName = 'Money Exchange';
                                                    } elseif ($lastTransaction->transaction_type->name == 'Request_Sent' || $lastTransaction->transaction_type->name == 'Request_Received') {
                                                        $transactionName = 'Request Money';
                                                    } else {
                                                        if (str_contains($lastTransaction->transaction_type->name, '_')) {
                                                            $transactionName = str_replace('_', ' ', $lastTransaction->transaction_type->name);
                                                        } else {
                                                            $transactionName = $lastTransaction->transaction_type->name;
                                                        }
                                                    }
                                                }
                                            @endphp
                                            ( {{ $transactionName }} )
                                        </p>
                                    @endif
                                </div>
                                <div class="right-icon-div d-flex">
                                    <div class="btn-block d-flex mt-20">

                                        @if (($wallet->currency->type == 'crypto' || $wallet->currency->type == 'fiat') && $wallet->currency->status == 'Active')
                                            @if(Common::has_permission(auth()->id(), 'manage_deposit'))

                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="deposit">
                                                    <a href="{{ route('user.deposit.create') }}" title="{{ __('deposit') }}">
                                                        {!! svgIcons('wallet_arrow_down') !!}
                                                    </a>
                                                </div>
                                            @endif
                                            {!! Common::has_permission(auth()->id(),'manage_deposit') && Common::has_permission(auth()->id(),'manage_withdrawal') ? '<div class="hr-40"></div>' : '' !!}

                                            @if(Common::has_permission(auth()->id(),'manage_withdrawal'))
                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="{{ __('withdraw') }}">
                                                    <a href="{{ route('user.withdrawal.create') }}" class="mt-1p" >
                                                        {!! svgIcons('wallet_arrow_up') !!}
                                                    </a>
                                                </div>
                                            @endif

                                        @elseif (optional($wallet->currency)->type == 'crypto_asset' && $provider_status == 'Active' && isActive('BlockIo') && (optional($wallet->cryptoAssetApiLogs)->payment_method_id == BlockIo))
                                            @if(Common::has_permission(auth()->id(),'manage_crypto_send_receive'))
                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="{{ __('Crypto Send') }}">
                                                    <a href="{{ route('user.crypto_send.create', [$walletCurrencyCode, $walletId, $provider]) }}" title="deposit">
                                                        {!! svgIcons('wallet_arrow_up') !!}
                                                    </a>
                                                </div>
                                                <div class="hr-40"></div>
                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="{{ __('Crypto Receive') }}">
                                                    <a href="{{ route('user.crypto_receive.create', [$walletCurrencyCode, $walletId, $provider]) }}" class="mt-1p" >
                                                        {!! svgIcons('wallet_arrow_down') !!}
                                                    </a>
                                                </div>
                                            @endif
                                        @elseif (optional($wallet->currency)->type == 'crypto_asset' && $tatum_provider_status == 'Active' && isActive('TatumIo') && (optional($wallet->cryptoAssetApiLogs)->payment_method_id == TatumIo) && (optional($wallet->currency)->status == 'Active'))
                                            @if(Common::has_permission(auth()->id(),'manage_crypto_send_receive'))
                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="{{ __('Crypto Send') }}">
                                                    <a href="{{ route('tatumio.user.crypto_send.create', [$walletCurrencyCode, $walletId, $provider]) }}" title="deposit">
                                                        {!! svgIcons('wallet_arrow_up') !!}
                                                    </a>
                                                </div>
                                                <div class="hr-40"></div>
                                                <div class="d-flex flex-wrap pt-5p wallet-svg show-tooltip" data-bs-toggle="tooltip" data-color="primary-bottom" data-bs-placement="bottom" title="{{ __('Crypto Receive') }}">
                                                    <a href="{{ route('tatumio.user.crypto_receive.create', [$walletCurrencyCode, $walletId, $provider]) }}" class="mt-1p" >
                                                        {!! svgIcons('wallet_arrow_down') !!}
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
@endsection
