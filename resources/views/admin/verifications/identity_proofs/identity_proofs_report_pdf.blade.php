@extends('admin.pdf.app')

@section('title', __('Identity Proof pdf'))

@section('content')
    <div class="mt-30">
        <table class="table">
            <tr class="table-header">
                <td>{{ __('Date') }}</td>
                <td>{{ __('User') }}</td>
                <td>{{ __('Identity Type') }}</td>
                <td>{{ __('Identity Number') }}</td>
                <td>{{ __('Status') }}</td>
            </tr>

            @foreach ($identityProofs as $identityProof)
                <tr class="table-body">
                    <td>{{ dateFormat($identityProof->created_at) }}</td>
                    <td>{{ getColumnValue($identityProof->user) }}</td>
                    <td>{{ str_replace('_', ' ', ucfirst($identityProof->identity_type)) }}</td>
                    <td>{{ $identityProof->identity_number }}</td>
                    <td>{{ getStatus($identityProof->status) }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection
