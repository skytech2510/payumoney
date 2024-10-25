@extends('admin.layouts.master')

@section('title', __('Wallets'))

@section('head_style')
    <!-- dataTables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
    <div class="box">
       <div class="panel-body ml-20">
            <ul class="nav nav-tabs f-14 cus" role="tablist">
                @include('admin.users.user_tabs')
           </ul>
          <div class="clearfix"></div>
       </div>
    </div>

    @if ($users->status == 'Inactive')
        <h3>{{ getColumnValue($users) }}&nbsp;<span class="label label-danger">{{ __('Inactive') }}</span></h3>
    @elseif ($users->status == 'Suspended')
        <h3>{{ getColumnValue($users) }}&nbsp;<span class="label label-warning">{{ __('Suspended') }}</span></h3>
    @elseif ($users->status == 'Active')
        <h3>{{ getColumnValue($users) }}&nbsp;<span class="label label-success">{{ __('Active') }}</span></h3>
    @endif

    <div class="box">
      <div class="box-body">
        <div class="row">
            <div class="col-md-12 f-14">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover pt-3" id="eachuserwallet">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Balance') }}</th>
                                        <th>{{ __('Currency') }}</th>
                                        <th>{{ __('Default') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($wallets)
                                        @foreach($wallets as $wallet)
                                            <tr>
                                                <td>{{ dateFormat($wallet->created_at) }}</td>

                                                <td>{{ $wallet->currency?->type != 'fiat' ? $wallet->balance : formatNumber($wallet->balance) }}</td>

                                                <td>{{ $wallet->currency?->code }}</td>

                                                @if ($wallet->is_default == 'Yes')
                                                    <td><span class="label label-success">{{ __('Yes') }}</span></td>
                                                @elseif ($wallet->is_default == 'No')
                                                    <td><span class="label label-danger">{{ __('No') }}</span></td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        {{ __('No wallet Found!') }}
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
      $("#eachuserwallet").DataTable({
            "order": [],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
