@extends('admin.layouts.master')

@section('title', __('Edit Merchant Payment'))

@section('head_style')

<!-- dataTables -->
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')

<div class="box">
   <div class="panel-body ml-20">
        <ul class="nav nav-tabs f-14 cus" role="tablist">
            <li class="nav-item">
              <a class="nav-link" href='{{ url(config('adminPrefix')."/merchant/edit", $merchant->id)}}'>{{ __('Profile') }}</a>
            </li>

            <li class="nav-item">
              <a class="nav-link active" href="{{ url(config('adminPrefix')."/merchant/payments", $merchant->id)}}">{{ __('Payments') }}</a>
            </li>
       </ul>
      <div class="clearfix"></div>
   </div>
</div>

<div class="row">
    <div class="col-md-10">
        <p class="pull-left mb-0 panel-title text-bold ml-5">{{ $merchant->business_name }}</p>
    </div>
    <div class="col-md-2">
        @if ($merchant->status)
            <p class="pull-right  panel-title mb-0 fw-bold">@if ($merchant->status == 'Approved')<span class="text-green">{{ __('Approved') }}</span>@endif
            @if ($merchant->status == 'Moderation')<span class="text-blue">{{ __('Moderation') }}</span>@endif
            @if ($merchant->status == 'Disapproved')<span class="text-red">{{ __('Disapproved') }}</span>@endif</p>
        @endif
    </div>
</div>

<div class="box mt-20">
  <div class="box-body">
    <div class="row">
        <div class="col-md-12 f-14">
            <div class="panel panel-info">
                <div class="panel-body">
                    <div class="table-responsive">
                            <table class="table table-hover pt-3" id="eachMerchantPayment">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Payment Method') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Fees') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Currency') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        @if(Common::has_permission(auth('admin')->user()->id, 'edit_merchant_payment'))
                                        <th>{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($merchant_payments)
                                        @foreach($merchant_payments as $merchant_payment)
                                            <tr>
                                                <td>{{ dateFormat($merchant_payment->created_at) }}</td>

                                                <td>{{ getColumnValue($merchant_payment->user) }}</td>

                                                <td>{{ ($merchant_payment->payment_method?->name == "Mts") ? settings('name') : $merchant_payment->payment_method?->name }}</td>

                                                <td>{{ formatNumber($merchant_payment->amount) }}</td>

                                                <td>{{ ($merchant_payment->charge_percentage == 0) && ($merchant_payment->charge_fixed == 0) ? "-" : formatNumber($merchant_payment->charge_percentage + $merchant_payment->charge_fixed) }}</td>

                                                @php
                                                    $total = $merchant_payment->charge_percentage + $merchant_payment->charge_fixed + $merchant_payment->amount;
                                                @endphp

                                                @if ($total > 0)
                                                    <td><span class="text-green">+ {{ formatNumber($total) }} </span></td>
                                                @else
                                                    <td><span class="text-red"> {{ ($total) }} </span></td>
                                                @endif

                                                <td>{{ $merchant_payment->currency?->code }}</td>

                                                <td>{!! getStatusLabel($merchant_payment->status) !!}</td>
                                                
                                                @if(Common::has_permission(auth('admin')->user()->id, 'edit_merchant_payment'))
                                                <td>
                                                    <a class="btn btn-xs btn-primary" href="{{url(config('adminPrefix').'/merchant_payments/edit/'.$merchant_payment->id)}}"><i class="fa fa-edit"></i></a>
                                                </td>
                                                @endif

                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
      $("#eachMerchantPayment").DataTable({
            "order": [],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
