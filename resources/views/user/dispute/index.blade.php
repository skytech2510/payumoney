@extends('user.layouts.app')

@push('css')
    <link rel="stylesheet" href="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.css') }}">
@endpush

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20">{{ __('Disputes') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 mt-2 tran-title">{{ __('Your conservations with admin relating problems') }}</p>
</div>

<div class="mt-22 mt-sm-4" id="disputeIndex">
    <div class="d-flex justify-content-between align-items-center r-pb-8 pb-10">
        <p class="mb-0 text-gray-100 f-16 r-f-12 gilroy-medium dark-CDO">{{ __('All lists of disputes') }}</p>
        <div class="d-flex align-items-center">
            <p class="mb-0 text-gray-100 f-16 r-f-12 gilroy-medium dark-CDO">{{ __('Filter') }}</p>
            <a class="fil-btn ml-12">
                <img src="{{ asset('public/dist/images/filter-on.svg') }}" alt="{{ __('Filter') }}">
                <img src="{{ asset('public/dist/images/filter-cross.svg') }}" alt="{{ __('Cross') }}" class="cross-none">
            </a>
        </div>
    </div>
    <form action="" method="get">
        <input id="startfrom" type="hidden" name="from" value="{{ isset($from) ? $from : '' }}">
        <input id="endto" type="hidden" name="to" value="{{ isset($to) ? $to : '' }}">
        <div class="filter-panel">
            <div class="d-flex flex-wrap justify-content-between pb-26">
                <div class="d-flex flex-wrap align-items-center pb-2 pb-xl-0">
                    <!-- DateRange Picker -->
                    <div class="me-2">
                        <div id="daterange-btn" class="cc param-ref filter-ref custom-daterangepicker">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M8 1C8.55229 1 9 1.44772 9 2V3H15V2C15 1.44772 15.4477 1 16 1C16.5523 1 17 1.44772 17 2V3.00163C17.4755 3.00489 17.891 3.01471 18.2518 3.04419C18.8139 3.09012 19.3306 3.18868 19.816 3.43597C20.5686 3.81947 21.1805 4.43139 21.564 5.18404C21.8113 5.66937 21.9099 6.18608 21.9558 6.74817C22 7.28936 22 7.95372 22 8.75868V17.2413C22 18.0463 22 18.7106 21.9558 19.2518C21.9099 19.8139 21.8113 20.3306 21.564 20.816C21.1805 21.5686 20.5686 22.1805 19.816 22.564C19.3306 22.8113 18.8139 22.9099 18.2518 22.9558C17.7106 23 17.0463 23 16.2413 23H7.75868C6.95372 23 6.28936 23 5.74817 22.9558C5.18608 22.9099 4.66937 22.8113 4.18404 22.564C3.43139 22.1805 2.81947 21.5686 2.43597 20.816C2.18868 20.3306 2.09012 19.8139 2.04419 19.2518C1.99998 18.7106 1.99999 18.0463 2 17.2413V8.7587C1.99999 7.95373 1.99998 7.28937 2.04419 6.74817C2.09012 6.18608 2.18868 5.66937 2.43597 5.18404C2.81947 4.43139 3.43139 3.81947 4.18404 3.43597C4.66937 3.18868 5.18608 3.09012 5.74818 3.04419C6.10898 3.01471 6.52454 3.00489 7 3.00163V2C7 1.44772 7.44772 1 8 1ZM7 5.00176C6.55447 5.00489 6.20463 5.01356 5.91104 5.03755C5.47262 5.07337 5.24842 5.1383 5.09202 5.21799C4.7157 5.40973 4.40973 5.71569 4.21799 6.09202C4.1383 6.24842 4.07337 6.47262 4.03755 6.91104C4.00078 7.36113 4 7.94342 4 8.8V9H20V8.8C20 7.94342 19.9992 7.36113 19.9624 6.91104C19.9266 6.47262 19.8617 6.24842 19.782 6.09202C19.5903 5.7157 19.2843 5.40973 18.908 5.21799C18.7516 5.1383 18.5274 5.07337 18.089 5.03755C17.7954 5.01356 17.4455 5.00489 17 5.00176V6C17 6.55228 16.5523 7 16 7C15.4477 7 15 6.55228 15 6V5H9V6C9 6.55228 8.55229 7 8 7C7.44772 7 7 6.55228 7 6V5.00176ZM20 11H4V17.2C4 18.0566 4.00078 18.6389 4.03755 19.089C4.07337 19.5274 4.1383 19.7516 4.21799 19.908C4.40973 20.2843 4.7157 20.5903 5.09202 20.782C5.24842 20.8617 5.47262 20.9266 5.91104 20.9624C6.36113 20.9992 6.94342 21 7.8 21H16.2C17.0566 21 17.6389 20.9992 18.089 20.9624C18.5274 20.9266 18.7516 20.8617 18.908 20.782C19.2843 20.5903 19.5903 20.2843 19.782 19.908C19.8617 19.7516 19.9266 19.5274 19.9624 19.089C19.9992 18.6389 20 18.0566 20 17.2V11Z" fill="currentColor" />
                            </svg>
                            <p class="mb-0 gilroy-medium f-13 px-2">{{ __('Pick a date range') }}</p>
                            <svg width="11" height="11" viewBox="0 0 11 11" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M1.40165 3.23453C1.6403 2.99588 2.02723 2.99588 2.26589 3.23453L5.50043 6.46908L8.73498 3.23453C8.97363 2.99588 9.36057 2.99588 9.59922 3.23453C9.83788 3.47319 9.83788 3.86012 9.59922 4.09877L5.93255 7.76544C5.6939 8.00409 5.30697 8.00409 5.06831 7.76544L1.40165 4.09877C1.16299 3.86012 1.16299 3.47319 1.40165 3.23453Z" fill="currentColor" />
                            </svg>
                        </div>
                    </div>

                    <div class="me-2">
                        <div class="param-ref filter-ref w-135">
                            <select class="select2 f-13"
                            data-minimum-results-for-search="Infinity" id="status" name="status">
                                <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ __('All')  }}</option>
                                <option value="Open" {{ $status == 'Open' ? 'selected' : '' }}>{{ __('Open')  }}</option>
                                <option value="Closed" {{ $status == 'Closed' ? 'selected' : '' }}>{{ __('Closed')  }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center pb-2 pb-xl-0">
                    <a href="{{ route('user.disputes.index') }}" class="reset-btn text-gray-100 f-14 gilroy-medium leading-17 tran-title">{{ __('Reset') }}</a>
                    <button type="submit" class="apply-filter f-14 gilroy-medium leading-17 b-none">{{ __('Apply Filter') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>
@forelse($disputes as $dispute)
    <div class="dispute-parent mb-2">
        <div class="d-flex justify-content-between dispute-child">
            <div class="d-flex dispute-gap gap-32">
            <div class="dis-img-div mt-3p">
                @if (auth()->id() == $dispute->defendant_id)
                    <img src="{{ asset(image($dispute?->claimant?->picture, 'profile')) }}" alt="{{ __('Dispute') }}">
                @else
                    <img src="{{ asset(image($dispute?->defendant?->picture, 'profile')) }}" alt="{{ __('Dispute') }}">
                @endif
            </div>
            <div class="dis-description">
                <p class="mb-0 f-18 gilroy-Semibold text-dark text-break res-w-171">{{ Str::limit($dispute->title, 50, '...') }}</p>
                <div class="d-flex align-items-center mt-8 client-name">
                <p class="mb-0 f-13 leading-16 gilroy-medium text-primary">{{ !empty($dispute->code) ? $dispute->code : '-' }}</p>
                <svg class="dispute-svg mx-10" width="5" height="5" viewBox="0 0 5 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="2.5" cy="2.5" r="2.5" fill="currentColor" />
                </svg>
                @if(auth()->id() != $dispute->claimant_id)
                    <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Claimant') }}: {{ getColumnValue($dispute->claimant) }}</p>
                @endif

                @if(auth()->id() !=$dispute->defendant_id)
                    <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ __('Defendant') }}: {{ getColumnValue($dispute->defendant) }}</p>
                @endif
                </div>
                <div class="d-flex mt-20 transaction dispute">
                <p class="mb-0 f-14 leading-17 text-gray-100 gilroy-medium">{{ __('Transaction ID') }}:</p>
                <p class="mb-0 f-14 text-dark leading-17 gilroy-medium">&nbsp;{{ !empty($dispute->transaction) ? $dispute->transaction->uuid : '-' }}</p>
                </div>
            </div>
            </div>
            <div class="d-flex flex-column align-items-end">
            <a href="{{ route('user.disputes.discussion', $dispute->id) }}" class="cursor-pointer see-deatils-btn d-flex align-items-center justify-content-center mt-2p">
                <p class="mb-0 f-13 leading-20 gilroy-medium see-deatils">{{ __('See Details') }}</p>
            </a>
            <div class="d-flex align-items-center mt-24 dates">
                <p class="mb-0 f-13 leading-16 gilroy-medium {{ getColor($dispute->status) }}">{{ $dispute->status }}</p>
                <svg class="dispute-svg mx-10" width="5" height="5" viewBox="0 0 5 5" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="2.5" cy="2.5" r="2.5" fill="currentColor"/>
                </svg>
                <p class="mb-0 date-text f-13 leading-16 gilroy-medium text-gray-100">&nbsp;{{ dateFormat($dispute->created_at) }}</p>
            </div>
            </div>
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
<div class="mt-4">
    {{ $disputes->appends(['from' => request()->from, 'to' => request()->to, 'status' => request()->status])->links('vendor.pagination.bootstrap-5') }}
</div>
@endsection

@push('js')
<script src="{{ asset('public/dist/plugins/daterangepicker-3.14.1/moment.min.js') }}"></script>
<script src="{{ asset('public/dist/plugins/daterangepicker-3.14.1/daterangepicker.min.js') }}"></script>
<script>
    'use strict';
    let dateRangePickerText = '{{ __("Pick a date range") }}';
    var startDate = "{!! $from !!}";
    var endDate = "{!! $to !!}";
</script>
<script src="{{ asset('public/user/customs/js/dispute.min.js')}}" type="text/javascript"></script>
@endpush