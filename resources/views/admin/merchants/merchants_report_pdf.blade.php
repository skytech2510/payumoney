@extends('admin.pdf.app')

@section('title', __('Merchant List pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('ID') }}</td>
                <td>{{ __('Type') }}</td>
                <td>{{ __('Business Name') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Url') }}</td>
                <td>{{ __('Group') }}</td>
                <td>{{ __('Logo') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($merchants as $merchant)
                <tr class="table-body">
                    <td>{{ dateFormat($merchant->created_at) }}</td>
                    <td>{{ $merchant->merchant_uuid ?? '-' }}</td>
                    <td>{{ ucfirst($merchant->type) }}</td>
                    <td>{{ $merchant->business_name }}</td>
                    <td>{{ getColumnValue($merchant->user) }}</td>
                    <td>{{ $merchant->site_url }}</td>
                    <td>{{ optional($merchant->merchant_group)->name ?? '-' }}</td>
                    <td>{{ $merchant->logo ?? '-' }}</td>
                    <td>{{ getStatus($merchant->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
