@extends('admin.layouts.master')

@section('title', __('Database Backups'))

@section('head_style')
    <!-- dataTables -->
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
  <!-- Main content -->
  <div class="row">
    <div class="col-md-3 settings_bar_gap">
      @include('admin.common.settings_bar')
    </div>
    <div class="col-md-9">
      <div class="box">
        <div class="d-flex align-items-center justify-content-between p-4">
          <h3 class="box-title mb-0 f-18">{{ __('Manage Databases') }}</h3>
            @if(Common::has_permission(auth('admin')->user()->id, 'add_database_backup') && $is_demo == false)
              <div><a class="btn btn-theme f-14 float-end" href="{{ url(config('adminPrefix').'/backup/save') }}">{{ __('New Backup') }}</a></div>
            @endif
        </div>
        <hr>
        <!-- /.box-header -->
        <div class="box-body table-responsive f-14">
          {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
        </div>
      </div>
    </div>
  </div>
@endsection

@push('extra_body_scripts')

<!-- jquery.dataTables js -->
<script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>

{!! $dataTable->scripts() !!}
@endpush

