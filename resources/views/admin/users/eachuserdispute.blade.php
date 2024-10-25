@extends('admin.layouts.master')

@section('title', __('Disputes'))

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

    <div class="box">
      <div class="box-body">
        <div class="row">
            <div class="col-md-12 f-14">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover pt-3" id="eachuserdispute">
                                <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Dispute ID') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Claimant') }}</th>
                                    <th>{{ __('Transaction ID') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($disputes as $dispute)
                                    <tr>
                                        <td>{{ dateFormat($dispute->created_at) }}</td>

                                        <td><a href="{{ url(config('adminPrefix').'/dispute/discussion/'.$dispute->id) }}">{{ $dispute->code }}</a></td>

                                        <td><a href="{{ url(config('adminPrefix').'/dispute/discussion/'.$dispute->id) }}">{{ $dispute->title }}</a></td>

                                        <td><a href="{{ url(config('adminPrefix').'/users/edit/'. $dispute->claimant?->id) }}">{{ getColumnValue($dispute->claimant) }}</a></td>

                                        <td>
                                            @if ($dispute->transaction)
                                                <a href="{{ url(config('adminPrefix').'/transactions/edit/'.$dispute->transaction?->id) }}" target="_blank">{{ $dispute->transaction?->uuid }}</a>
                                            @endif
                                        </td>

                                        @if($dispute->status == 'Open')
                                            <td><span class="label label-primary">{{ __('Open') }}</span></td>
                                        @else
                                            <td><span class="label label-danger">{{ __('Closed') }}</span></td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.layouts.partials.message_boxes')
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

<script type="text/javascript">
    $(function () {
      $("#eachuserdispute").DataTable({
            "order": [],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
