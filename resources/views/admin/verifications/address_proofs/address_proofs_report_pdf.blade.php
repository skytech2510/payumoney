@extends('admin.pdf.app')

@section('title', __('Address Proof pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($addressProofs as $addressProof)
                <tr class="table-body">
                    <td>{{ dateFormat($addressProof->created_at) }}</td>
                    <td>{{ getColumnValue($addressProof->user) }}</td>
                    <td>{{ getStatus($addressProof->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
