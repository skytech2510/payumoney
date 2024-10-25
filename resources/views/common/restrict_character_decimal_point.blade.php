<script>
    var isNumberOrDecimalPointKey = function(value, e) {

        var charCode = (e.which) ? e.which : e.keyCode;

        if (charCode == 46) {
            if (value.value.indexOf('.') === -1) {
                return true;
            } else {
                return false;
            }
        } else {
            if (charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
        }
        return true;
    }
</script>
