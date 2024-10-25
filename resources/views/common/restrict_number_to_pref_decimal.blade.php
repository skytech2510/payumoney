<script type="text/javascript">
    function restrictNumberToPrefdecimal(e, type) {
        let decimalFormat =
            type === "fiat" ?
            "{{ preference('decimal_format_amount', 2) }}" :
            "{{ preference('decimal_format_amount_crypto', 8) }}";

        let num = $.trim(e.value);
        if (num.length > 0 && !isNaN(num)) {
            e.value = digitCheck(num, 8, decimalFormat);
            return e.value;
        }
    }

    function digitCheck(num, beforeDecimal, afterDecimal) {
        return num
            .replace(/[^\d.]/g, "")
            .replace(new RegExp("(^[\\d]{" + beforeDecimal + "})[\\d]", "g"), "$1")
            .replace(/(\..*)\./g, "$1")
            .replace(new RegExp("(\\.[\\d]{" + afterDecimal + "}).", "g"), "$1");
    }

</script>