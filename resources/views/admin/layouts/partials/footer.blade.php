<div class="pull-right hidden-xs f-14">
    <b>{{ __('Version') }}</b> {{ config('paymoney.version') }}
</div>
<strong class="f-14">{{ __('Copyright') }} &copy; {{date("Y")}} <a href="{{ route('dashboard') }}" target="_blank">{{ settings('name') }}</a> | </strong> <span class="f-14">{{ __('All rights reserved') }}</span>

<!-- Delete Modal for buttons-->
<div class="modal fade" id="confirmDelete" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content w-100 h-100 aliceblue">
            <div class="modal-header">
                <h4 class="modal-title f-18">{{ __('Confirm Delete') }}</h4>
                <a type="button" class="close f-18" data-bs-dismiss="modal" aria-hidden="true">&times;</a>
            </div>
            <div class="px-3 f-14">
                <p><strong>{{ __('Are you sure you want to delete?') }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger f-14" id="confirm">{{ __('Yes') }}</button>
                <button type="button" class="btn btn-default f-14" data-bs-dismiss="modal">{{ __('No') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal for href-->
<div class="modal fade del-modal" id="delete-warning-modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content w-100 h-100 aliceblue">
            <div class="modal-header">
                <h4 class="modal-title f-18">{{ __('Confirm Delete') }}</h4>
                <a type="button" class="close f-18" data-bs-dismiss="modal">&times;</a>
            </div>
            <div class="px-3 f-14">
                <p><strong>{{ __('Are you sure you want to delete?') }}</strong></p>
            </div>
            <div class="modal-footer">
                <a class="btn btn-danger f-14" id="delete-modal-yes" href="javascript:void(0)">{{ __('Yes') }}</a>
                <button type="button" class="btn btn-default f-14" data-bs-dismiss="modal">{{ __('No') }}</button>
            </div>
        </div>
    </div>
</div>
