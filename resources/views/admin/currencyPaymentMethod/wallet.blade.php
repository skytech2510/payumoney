@php
    $modules = collect(addonPaymentMethods('Wallet'))->sortBy('type')->reverse()->toArray();
    $type = array_column($modules, 'type');
@endphp
@if (array_filter($type))
<!-- wallet - Activated for -->
<input type="hidden" name="processing_time" value="0">
<input type="hidden" name="mts" value="">
@endif