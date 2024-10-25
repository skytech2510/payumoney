<header class="main-header">
    <!-- Logo -->
    <div class="full-width">

    <a href="{{ route('dashboard') }}" class="logo pt-12 text-decoration-none">
        <span class="logo-mini"><b>{{ settings('name') }}</b></span>
        <img src="{{ image(settings('logo'), 'logo') }}" width="192" height="49" class="company-logo">
    </a>
    </div>

    <div class="navbar navbar-static-top p-0">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle text-decoration-none" data-toggle="offcanvas" role="button">
            <span class="sr-only">{{ __('Toggle navigation') }}</span>
        </a>
        <div class="mobile-width">
            <a href="{{ route('dashboard') }}" class="mobile-logo">
                <span class="logo-lg f-13"><b>{{ settings('name') }}</b></span>
            </a>
        </div>
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                @include('admin.layouts.partials.nav_user-menu')
            </ul>
        </div>
    </div>
</header>

<!-- Flash Message  -->
<div class="flash-container">
    @if(Session::has('message'))
        <div class="alert mt-20 f-14 {{ Session::get('alert-class') }} text-center mb-0" role="alert">
          {{ Session::get('message') }}
          <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
        </div>
    @endif
    <div class="alert alert-success f-14 text-center mb-0 d-none" id="success_message_div" role="alert">
        <a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a>
        <p id="success_message"></p>
    </div>

    <div class="alert alert-danger f-14 text-center mb-0 d-none" id="error_message_div" role="alert">
        <p><a href="#" class="alert-close float-end" data-bs-dismiss="alert">&times;</a></p>
        <p id="error_message"></p>
    </div>
</div>
<!-- /.Flash Message  -->


