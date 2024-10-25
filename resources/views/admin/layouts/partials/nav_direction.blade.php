<li><a href="#"><span id="direction"> {{ __('Change Direction') }}</span></a></li>
<script type="text/javascript">
    $(document).ready(function()
    {
        $("#direction").click(function()
        {
            var $head = $("head");
            var link_element = "<link rel='stylesheet' href='{{ URL::asset('public/dist/css/AdminLTE-rtl.css') }}'>";
            $head.append(link_element).slideToggle();
            console.log(link_element);
        });
    });
</script>
