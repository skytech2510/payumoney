@extends('user.layouts.app')

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20">{{ __('TICKETS') }}</p>
    <p class="mb-0 gilroy-medium text-gray-100 f-16 r-f-12 ticket-title mt-2 tran-title">{{ __('Ask support for any kinds of problem you face') }}</p>
</div>

<div class="d-flex justify-content-between mt-24 r-mt-22 align-items-center ticket-create" id="ticketList">
    <div class="me-2 me-3">
        <form  method="get" id="ticketSearchForm">
            <div class="param-ref param-ref-withdraw filter-ref r-filter-ref w-135">
                <select class="select2 f-13" data-minimum-results-for-search="Infinity" name="status" id="ticketStatus">
                <option value="all" {{ 'all' == $status ? 'selected' : '' }}>{{ __('All') }}</option>
                    @foreach ($statuses as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $status ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <!-- Button trigger modal -->
    <a href="{{ route('user.tickets.create') }}" class="bg-primary text-white Add-new-btn w-176 addnew text-center green-btn" >
    <span class="f-14 leading-29 gilroy-medium"> + {{ __('Create Ticket') }}</span>
    </a>
</div>

@forelse($tickets as $ticket)
    <div class="mobile-flex web-flex mt-20 ticket-section-parent ticket-section-parent-closed bg-white">
        <div class="d-flex">
            <div class="ticket-pro-img img-close img-52">
                {!! svgIcons('ticket_icon') !!}
            </div>
            <div class="ticket-mid-description ml-24">
                <div class="d-flex mobile-flex mt-2p gap-14">
                    <p class="mb-0 f-18 leading-22 text-dark gilroy-Semibold w-status-m">{{ $ticket->subject }}</p>
                    <div class="custom-badge d-flex justify-content-center align-items-center bg-{{ getBgColor($ticket->ticket_status?->name) }} r-mt-open"><span class="text-white f-11 gilroy-medium">{{ $ticket->ticket_status?->name }}</span></div>
                </div>
                <div class="priority-section d-flex align-items-center mt-6">
                    <p class="mb-0 f-13 leading-16 gilroy-medium text-primary">{{ $ticket->code }}</p>
                    <div class="ticket-dot-svg">
                        {!! svgIcons('dot_icon') !!}
                    </div>
                    <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ $ticket->priority }}</p>
                    <div class="ticket-dot-svg">
                        {!! svgIcons('dot_icon') !!}
                    </div>
                    <p class="mb-0 f-13 leading-16 gilroy-medium text-gray-100">{{ dateFormat($ticket->created_at) }}</p>
                </div>
            </div>
        </div>
        <div class="">
            <a href="{{ route('user.tickets.reply', $ticket->id) }}" class="see-deatils-btn-ml mt-n2p cursor-pointer see-deatils-btn details-bg d-flex align-items-center justify-content-center r-mtop mt-2p ">
                <p class="mb-0 f-13 leading-20 gilroy-medium">{{ __('See Details') }}</p>
            </a>
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
    {{ $tickets->appends(['status' => request()->status])->links('vendor.pagination.bootstrap-5') }}
</div>
@endsection

@push('js')
<script src="{{ asset('public/user/customs/js/ticket.min.js')}}" type="text/javascript"></script>
@endpush