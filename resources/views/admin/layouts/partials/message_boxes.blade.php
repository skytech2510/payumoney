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