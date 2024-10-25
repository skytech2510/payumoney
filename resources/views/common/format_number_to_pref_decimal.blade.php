<script type="text/javascript">

    function formatNumberToPrefDecimal(num = 0)
    {
        let decimal_format = '{{ $preference }}';
        num = ((Math.abs(num)).toFixed(decimal_format))
        return num;

        // num = Math.sign(num)*((Math.abs(num)).toFixed(decimal_format))  // removes zero after decimals - example - 1.220000, then it will show 1.22 - removes trailing zeros
    }

</script>