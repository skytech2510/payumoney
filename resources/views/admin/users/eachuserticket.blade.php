@extends('admin.layouts.master')

@section('title', __('Tickets'))

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
                                <table class="table table-hover pt-3" id="eachuserticket">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Priority') }}</th>
                                        <th>{{ __('Last Reply') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($tickets)
                                        @foreach($tickets as $ticket)
                                            <tr>

                                                <td>{{ dateFormat($ticket->created_at) }}</td>

                                                <td><a href="{{ url(config('adminPrefix').'/tickets/reply/'.$ticket->id) }}">{{ $ticket->subject }}</a></td>

                                                @if ($ticket->ticket_status->name == 'Open')
                                                    <td><span class="label label-success">{{ __('Open') }}</span></td>
                                                @elseif ($ticket->ticket_status->name == 'In Progress')
                                                    <td><span class="label label-primary">{{ __('In Progress') }}</span></td>
                                                @elseif ($ticket->ticket_status->name == 'Hold')
                                                    <td><span class="label label-warning">{{ __('Hold') }}</span></td>
                                                @elseif ($ticket->ticket_status->name == 'Closed')
                                                    <td><span class="label label-danger">{{ __('Closed') }}</span></td>
                                                @endif

                                                <td>{{ $ticket->priority }}</td>

                                                <td>{{ $ticket->last_reply ? dateFormat($ticket->last_reply) :  __('No Reply Yet') }}</td>

                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a class="btn btn-xs btn-primary" href="{{ url(config('adminPrefix').'/tickets/edit/'.$ticket->id) }}"><i class="fa fa-edit"></i></a>

                                                        <form action="{{ url(config('adminPrefix').'/tickets/delete/'.$ticket->id) }}" method="GET">
                                                            {{ csrf_field() }}
                                                            <button class="btn btn-xs btn-danger" data-message="Are you sure you want to delete this ticket?" data-bs-target="#confirmDelete" data-title="Delete Ticket" data-bs-toggle="modal" title="Delete" type="button"><i class="fa fa-trash"></i></button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        {{ __('No Ticket Found!') }}
                                    @endif
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
      $("#eachuserticket").DataTable({
            "order": [],
            "language": '{{Session::get('dflt_lang')}}',
            "pageLength": '{{Session::get('row_per_page')}}'
        });
    });
</script>
@endpush
