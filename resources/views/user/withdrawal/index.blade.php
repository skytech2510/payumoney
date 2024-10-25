@extends('user.layouts.app')

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Withdraw List') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title">{{ __('History of all your withdrawals in your account') }}</p>
</div>
@include('user.common.alert')
<div class="withdraw-list-parent mt-32">
    @forelse($payouts as $payout)
        <div class="d-flex withdraw-list-child">
            <div class="d-flex flex-wraps flex-grows-1">
                <div class="transaction-medium d-flex width-50">
                    <div class="d-flex justify-content-center sm-send_medium">
                        <img src="{{ $payout->payment_method?->name == "Mts" ? logoPath() : image(null, $payout->payment_method?->name)  }}" alt="{{ $payout->payment_method?->name }}">
                    </div>
                    <div class="ml-20 date_and-status">
                        <p class="text-dark gilroy-medium f-16 mb-0 mt-medium-2p leading-20">{{ ($payout->payment_method?->name == "Mts") ? settings('name') : $payout->payment_method?->name }}</p>
                        <p class="f-13 gilroy-regular d-flex justify-content-center align-items-center mb-0 mt-8">
                            <span class="text-gray-100">{{ dateFormat($payout->created_at) }}</span>
                            <svg class="mx-between-date_and_sattus" width="4" height="4" viewBox="0 0 4 4" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="2" cy="2" r="2" fill="#2AAA5E"></circle>
                            </svg>
                            <span class="{{ getColor($payout->status) }}">{{ $payout->status }}</span>
                        </p>
                    </div>
                </div>
                @if($payout->payment_method?->name == "Bank")
                    <div class="d-flex justify-content-center width-75 flex-column r-mt-2p addres">
                        @if ($payout->withdrawal_detail)
                            <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $payout->withdrawal_detail?->bank_name }}</p>
                            <p class="text-start mb-0 f-14 leading-19 text-gray-100 gilroy-regular mt-8">{{ $payout->withdrawal_detail?->account_name.' (********'. substr($payout->withdrawal_detail?->account_number, -4). ')' }}</p>
                        @endif
                    </div>
                @elseif($payout->payment_method?->name == "Mts")
                    <div class="d-flex align-items-center width-75 addres">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ settings('name') }}</p>
                    </div>
                @else
                    <div class="d-flex align-items-center width-75 addres">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $payout->payment_method_info }}</p>
                    </div>
                @endif
            </div>
            <div class="currency-with-fees d-flex align-items-end flex-column mt-medium-2p width-25">
                <p class="mb-0 f-16 leading-20 gilroy-medium text-start w-space l-sp64"><span class="text-dark">{{ moneyFormat($payout->currency?->code, formatNumber($payout->amount, $payout->currency_id)) }}</span></p>
                <p class="mb-0 f-13  text-gray-100 gilroy-regular text-end ml-24 fee-mt-8 w-space">{{ ($payout->amount - $payout->subtotal > 0) ? moneyFormat($payout->currency?->code, formatNumber($payout->amount - $payout->subtotal, $payout->currency_id)) : '-' }}</p>
            </div>
        </div>
    @empty
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
    @endforelse
</div>

<div class="mt-3">
    {{ $payouts->links('vendor.pagination.bootstrap-5') }}
</div>
@endsection