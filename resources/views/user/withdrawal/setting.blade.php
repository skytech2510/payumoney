@extends('user.layouts.app')

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">{{ __('Withdrawal Settings') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title p-inline-block">{{ __('All the options you have or can create to withdraw from your wallet') }}</p>
</div>
@include('user.common.alert')
<div class="d-flex justify-content-between mt-24 r-mt-22 align-items-center withdrawal-filter">
    <div class="me-2 me-3">
        <form method="get" id="withdrawalSettingSearchForm">
            <div class="param-ref param-ref-withdraw filter-ref r-filter-ref w-135">
                <select name="payment_method" id="payment_method" class="select2 f-13" id="filter-ref-2"
                data-minimum-results-for-search="Infinity">
                    <option value="all" {{ $paymentMethod == 'all' ? 'selected' : '' }} >{{ __('All') }}</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}" {{ $paymentMethod == $method->id ? 'selected' : '' }} >{{ $method->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <!-- Button trigger modal -->
    <button type="button" class="btn bg-primary text-light Add-new-btn btn-mt w-176 addnew" id="addBtn" data-bs-toggle="modal" data-bs-target="#addModal">
        <span class="f-14 gilroy-medium">+ {{ __('Add Setting') }}</span>
    </button>

    <!-- Modal -->
    <div class="modal fade modal-overly" id="addModal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-content">
                    <div class="modal-header w-modal-header">
                        <p class="modal-title gilroy-Semibold text-dark" id="modalHeading">{{ __('Add Withdrawal Setting') }}</p>
                        <button type="button" class="" data-bs-dismiss="modal" aria-label="Close">
                            <span class="close-div position-absolute modal-close-btn rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                                {!! svgIcons('cross_icon') !!}
                            </span>
                        </button>
                    </div>
                    <div class="modal-body modal-body-pxy">
                        <!-- Payment Method -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="param-ref param-ref-withdraw  money-ref r-mt-11">
                                    <label class="gilroy-medium text-gray-100 mb-7 f-14 leading-17 r-mt-0">{{ __('Withdrawal Type') }}</label>
                                    <select id="type" class="select2 withdraw-type" data-minimum-results-for-search="Infinity">
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}" {{ old('type') == $method->id ? 'selected' : '' }} >{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Paypal Form -->
                        <form method="post" action="{{ route('user.withdrawal.setting.store') }}" id="paypalPayoutSettingForm" class="payoutSettingForm">
                            @csrf
                            <input type="hidden" name="type" id="paypalType" value="{{ Paypal }}">

                            <span id="paypalSettingId" class="settingId"></span>
                            <!-- Paypal Email -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6" >{{ __('Email Address') }}</label>
                                        <input type="email" class="form-control input-form-control input-form-control-withdraw apply-bg" name="email" id="email" onkeyup="this.value = this.value.replace(/\s/g, '')" placeholder="{{ __('Ex: example@gmail.com') }}" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-20">
                                <div class="col-md-12 pb-2">
                                    <button type="submit"  class="btn bg-primary add-option-btn w-100 setting-btn f-16 leading-20 gilroy-medium" id="paypalWithdrawalSettingSubmitBtn">
                                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="paypalWithdrawalSettingSpinner">
                                            <span class="visually-hidden"></span>
                                        </div>
                                        <span id="paypalWithdrawalSettingSubmitBtnText">{{ __('Add Settings') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Bank Form -->
                        <form method="post" action="{{ route('user.withdrawal.setting.store') }}" id="bankPayoutSettingForm" class="payoutSettingForm d-none">
                            @csrf
                            <input type="hidden" name="type" id="bankType" value="{{ Bank }}">
                            <span id="bankSettingId" class="settingId"></span>
                            <!-- Account Holders Name -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Account Holders Name') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="account_name"  id="account_name" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <!-- Account Number/IBAN -->
                                <div class="col-md-6 column-pr-unset">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Account Number/IBAN') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="account_number" id="account_number" onkeyup="this.value = this.value.replace(/\s/g, '')" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                                <!-- Bank Name -->
                                <div class="col-md-6 column-pl-unset">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Bank Name') }}</label>
                                        <input type="text"  class="form-control input-form-control input-form-control-withdraw apply-bg" name="bank_name" id="bank_name" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Branch Name -->
                                <div class="col-md-6 column-pr-unset">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Branch Name') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="branch_name" id="branch_name" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                                <!-- Branch City -->
                                <div class="col-md-6 column-pl-unset">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Branch City') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="branch_city" id="branch_city"  required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                            </div>
                            <!-- Branch Address -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Branch Address') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="branch_address" id="branch_address" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- SWIFT Code -->
                                <div class="col-md-6 column-pr-unset">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('SWIFT Code') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="swift_code" id="swift_code" onkeyup="this.value = this.value.replace(/\s/g, '')" required data-value-missing="{{ __('This field is required') }}">
                                    </div>
                                </div>
                                <!-- Country -->
                                <div class="col-md-6 column-pl-unset">  
                                    <div class="param-ref param-ref-withdraw param-ref-withdraw-modal money-ref-2">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-0">{{ __('Country') }}</label>
                                        <select class="select2" name="country" id="country">
                                            @foreach($countries as $country)
                                                <option value="{{ $country->id }}" >{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-20">
                                <div class="col-md-12 pb-2">
                                    <button type="submit"  class="btn bg-primary add-option-btn w-100 setting-btn f-16 leading-20 gilroy-medium" id="bankWithdrawalSettingSubmitBtn">
                                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="bankWithdrawalSettingSpinner">
                                            <span class="visually-hidden"></span>
                                        </div>
                                        <span id="bankWithdrawalSettingSubmitBtnText">{{ __('Add Settings') }}</span>
                                    </button>
                                </div>
                            </div>

                        </form>

                        <!-- Crypto Form -->                
                        <form method="post" action="{{ route('user.withdrawal.setting.store') }}" id="cryptoPayoutSettingForm" class="payoutSettingForm d-none">
                            @csrf
                            <input type="hidden" name="type" id="cryptoType" value="{{ Crypto }}">
                            <span id="cryptoSettingId" class="settingId"></span>
                            <!-- Currency -->           
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="param-ref param-ref-withdraw money-ref r-mt-11">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 mt-4 r-mt-0">{{ __('Currency') }}</label>
                                        <select class="select2 select-currency" data-minimum-results-for-search="Infinity" name="currency" id="currency">
                                            @foreach($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                            @endforeach
                                        </select>
                                        <label id="currency-error" class="text-danger d-none"></label>
                                    </div>
                                </div>
                            </div>
                            <!--Crypto Address --> 
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="label-top mt-withdraw">
                                        <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Crypto Address') }}</label>
                                        <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="crypto_address" id="crypto_address" placeholder="{{ __('Ex: bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh') }}" required data-value-missing="{{ __('This field is required') }}">
                                        <label id="crypto-address-error" class="text-danger d-none"></label>
                                    </div>
                                    <small class="form-text text-muted"><b>{{ __('*Providing wrong address may permanent loss of your coin') }}</b></small>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-20">
                                <div class="col-md-12 pb-2">
                                    <button type="submit"  class="btn bg-primary add-option-btn w-100 setting-btn f-16 leading-20 gilroy-medium" id="cryptoWithdrawalSettingSubmitBtn">
                                        <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="cryptoWithdrawalSettingSpinner">
                                            <span class="visually-hidden"></span>
                                        </div>
                                        <span id="cryptoWithdrawalSettingSubmitBtnText">{{ __('Add Settings') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Mobile Money Form -->  
                         @if (config('mobilemoney.is_active')) 
                            <form method="post" action="{{ route('user.withdrawal.setting.store') }}" id="mobileMoneyPayoutSettingForm" class="payoutSettingForm d-none">
                                @csrf
                                <input type="hidden" name="type" id="mobileMoneyType" value="{{ MobileMoney }}">
                                <span id="mobileMoneySettingId" class="settingId"></span>
                            
                                <!-- Network -->           
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="param-ref param-ref-withdraw money-ref r-mt-11">
                                            <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 mt-4 r-mt-0">{{ __('Network') }}</label>
                                            <select name="" class="select2 select-currency" data-minimum-results-for-search="Infinity" name="mobilemoney_id" id="mobilemoney_id">
                                                @foreach($networks as $id => $network)
                                                    <option value="{{ $id }}">{{ $network }}</option>
                                                @endforeach
                                            </select>
                                            <label id="mobile-money-error" class="error d-none"></label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="label-top mt-withdraw">
                                            <label class="gilroy-medium text-gray-100 mb-2 f-14 leading-17 mt-20 r-mt-amount r-mt-6">{{ __('Mobile Number') }}</label>
                                            <input type="text" class="form-control input-form-control input-form-control-withdraw apply-bg" name="mobile_number" id="mobile_number" required data-value-missing="{{ __('This field is required') }}">
                                        </div>
                                    </div>
                                </div>
                            

                                <!-- Submit Button -->
                                <div class="row mt-20">
                                    <div class="col-md-12 pb-2">
                                        <button type="submit"  class="btn bg-primary add-option-btn w-100 setting-btn f-16 leading-20 gilroy-medium" id="mobileMoneyWithdrawalSettingSubmitBtn">
                                            <div class="spinner spinner-border text-white spinner-border-sm mx-2 d-none" role="status" id="mobileMoneyWithdrawalSettingSpinner">
                                                <span class="visually-hidden"></span>
                                            </div>
                                            <span id="mobileMoneyWithdrawalSettingSubmitBtnText">{{ __('Add Settings') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                         @endif
                    </div>
                </div>
            </div>
        </div>                       
    </div>
</div>
<div class="modal fade modal-overly" id="deletemodal" aria-labelledby="edit-modal-header" aria-hidden="true">
    <div class="delete-custom-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="edit-modal-header">
                    <p class="modal-title f-20 gilroy-Semibold text-dark mb-0">{{ __('Delete Option') }}</p>
                    <button type="button" class="b-unset" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-div position-absolute modal-close-btn btn-close rtl-wrap-four text-gray-100 d-flex align-items-center justify-content-center">
                            {!! svgIcons('cross_icon') !!}
                        </span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0 text-gray-100 f-16 leading-26 gilroy-medium">{{ __('Are you sure you want to delete this?') }}</p>
                </div>

                <div class="modals-bottom d-flex justify-content-end delete-gap">
                    <button class="btn btn-secondary-cancel f-14 leading-17 text-gray-100 gilroy-medium" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button class="ml-delete btn btn-secondary-delete f-14 leading-17 text-dark gilroy-medium"id="delete-modal-yes">{{ __('Yes, Delete') }}</button>
                </div>
            </div>
        </div>
    </div> 
</div>

<div class="withdraw-list-parent mt-15 r-mt-11">
     @forelse($payoutSettings as $row)
        <div class="d-flex withdraw-list-child">
            <div class="d-flex flex-wraps flex-grows-1">
                <div class="transaction-medium d-flex width-50 responsive-top">
                    <div class="d-flex justify-content-center sm-send_medium">
                    <img src="{{ image(null, $row->paymentMethod?->name) }}" alt="{{ __('Payment method') }}">
                    </div>
                    <div class="ml-20 date_and-status extra-flex">
                    <p class="text-dark gilroy-medium f-16 mb-0 leading-20">{{ $row->paymentMethod?->name }}</p>
                    </div>
                </div>
                @if($row->paymentMethod?->id == Paypal)
                    <div class="d-flex align-items-start justify-content-center width-75 flex-column addres r-mt-n3p">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $row->email }}</p>
                    </div>
                @elseif ($row->paymentMethod?->id == Bank)
                    <div class="d-flex align-items-start width-75 flex-column mt-2p r-mt-n9p addres">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $row->bank_name }}</p>
                        <p class="text-start mb-0 f-14 leading-17 text-gray-100 gilroy-regular mt-8">{{ $row->account_name. ' (********'. substr($row->account_number, -4). ')' }}</p>
                    </div>
                @elseif ($row->paymentMethod?->id == Crypto)
                    <div class="d-flex align-items-start width-75 flex-column mt-2p r-mt-n9p addres">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $row->currency?->code }}</p>
                        <p class="text-start mb-0 f-14 leading-17 text-gray-100 gilroy-regular mt-8">{{ $row->crypto_address }}</p>
                    </div>
                @elseif(config('mobilemoney.is_active') && $row->paymentMethod?->id == (defined('MobileMoney') ? MobileMoney : ''))
                    <div class="d-flex align-items-start width-75 flex-column mt-2p r-mt-n9p addres">
                        <p class="text-start mb-0 f-16 leading-20 text-dark gilroy-medium">{{ $row->mobilemoney?->mobilemoney_name }}</p>
                        <p class="text-start mb-0 f-14 leading-17 text-gray-100 gilroy-regular mt-8">{{ '(********'. substr($row->mobile_number, -4). ')' }}</p>
                    </div>
                @endif
            </div>
            <div class="currency-with-fees w-currency d-flex justify-content-end align-items-center trash-edit-svg position-r">
                <div class="edit-hover-effect">
                    <a href="javascript:void(0)" class="edit-setting"  data-id="{{ $row->id }}" data-type="{{ $row->type }}" data-obj="{{ json_encode($row->getAttributes()) }}">
                        {!! svgIcons('edit_icon_lg') !!}
                    </a>
                    <span class="tooltips tooltip-right gilroy-regular f-14 text-white">{{ __('Edit') }}</span>
                </div>
                <div class="border-36h"></div>
                <form action="{{ route('user.withdrawal.setting.delete') }}" method="post">
                    <input type="hidden" name="id" value="{{ $row->id }}">
                    @csrf 
                    <div class="delete-hover-effect">
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#deletemodal" data-message="{{ __("Are you sure you want to delete this Data ?") }}" data-row="{{ $row->id }}">
                            {!! svgIcons('delete_icon_lg') !!}
                        </a>
                        <span class="tooltips-delete tooltip-delete-right gilroy-regular f-14 text-white">{{ __('Delete') }}</span>
                    </div>
                </form>
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
    {{ $payoutSettings->appends(['payment_method' => request()->payment_method])->links('vendor.pagination.bootstrap-5') }}
</div>

@endsection

@push('js')
    <script src="{{ asset('public/dist/plugins/html5-validation-1.0.0/validation.min.js') }}"></script>
    
    <script>
        'use strict';
        var isActiveMobileMoney = "{{ config('mobilemoney.is_active') }}";
        var settingStoreUrl = "{{ route('user.withdrawal.setting.store') }}";
        var settingUpdateUrl = "{{ route('user.withdrawal.setting.update') }}";
        var mobileMoneyPaymentMethod = "{{ defined('MobileMoney') ? MobileMoney : '' }}";
        var bankPaymentMethod = "{{ Bank }}";
        var paypalPaymentMethod = "{{ Paypal }}";
        var cryptoPaymentMethod = "{{ Crypto }}";
        var submitButtonText = "{{ __('Submitting...') }}";
        var updateButtonText = "{{ __('Update Settings') }}";
        var addButtonText = "{{ __('Add Settings') }}";
        var updateModalHeadingText = "{{ __('Update Withdrawal Setting') }}";
        var addModalHeadingText = "{{ __('Add Withdrawal Setting') }}";
        var userStatus = "{{ auth()->user()->status }}";
        var userStatusCheckUrl = "{{ url('check-user-status') }}";
    </script>
    <script src="{{ asset('public/user/customs/js/user-status.min.js') }}"></script>
    <script src="{{ asset('public/user/customs/js/withdrawal-setting.min.js') }}"></script>
@endpush