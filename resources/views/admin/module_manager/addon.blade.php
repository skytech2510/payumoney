@extends('admin.layouts.master')

@section('title', __('Module Manager'))

@section('page_content')
<div class="box">
    @include('addons::index')
 </div>
@endsection