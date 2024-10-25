@extends('admin.layouts.master')

@section('title', __('Current Price Rate'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('Modules/TatumIo/Resources/assets/admin/css/current_price_rate.min.css') }}">
@endsection

@section('page_content')
    <div class="box box-default">
        <div class="box-body">
            <div class="d-flex justify-content-between">
                <div>
                    <div class="top-bar-title padding-bottom pull-left">{{ __('Exchange Rates') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="box">
        <div class="box-body mb-2">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel panel-info">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <div class="card-grid-container">
                                    @foreach ($getCurrentPrices as $getCurrentPrice)
                                        <div class="crypto-card-2">
                                            <div class="d-flex crypto-card-2-img-conf">
                                                <div>
                                                    <img src="https://ui-avatars.com/api/?length=3&name={{ $getCurrentPrice->price_base }}&background=random&rounded=true&bold=true&&size=48%27";" alt="{{ __('Price List') }}">
                                                </div>
                                                <div class="ml-5 crypto-card-2-conf">
                                                    <p class="crypto-name">{{ $getCurrentPrice->price }}</p>
                                                    <h4 class="text-muted">{{ $getCurrentPrice->exchange }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
