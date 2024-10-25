<script type="text/javascript">

	function numberWithCommas(x)
    {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

    function numberWithDot(x)
    {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        return parts.join(",");
    }

    /**
     * [formats number by comma and dot]
     */
    function getDecimalNumberFormat(num = 0)
    {
        // console.log(typeof num)
        let seperator = '{{ $preference['thousand_separator'] }}';
        // console.log(seperator);
        let decimal_format = '{{ $preference['decimal_format_amount'] }}';
        // console.log(decimal_format);

        if (seperator != null && decimal_format != null)
        {
            num = parseFloat(num).toFixed(decimal_format);
            // console.log(num);
            if (seperator == '.')
            {
                num = numberWithDot(num);
            }
            else if (seperator == ',')
            {
                num = numberWithCommas(num);
            }
            return num;
        }
    }

    /**
	 * [amount format before and after]
	 */
	function getMoneyFormat(symbol, amount)
	{
	    let symbol_position = '{{ $preference['money_format'] }}';
	    if (symbol_position != null)
	    {
	        if (symbol_position == "before")
	        {
	            amount = symbol + ' ' + amount;
	        }
	        else if (symbol_position == "after")
	        {
	            amount = amount + ' ' + symbol;
	        }
	        return amount;
	    }
	}

    function formatNumberToDecimalOnly(num = 0)
    {
        // console.log(typeof num)
        let decimal_format = '{{ $preference['decimal_format_amount'] }}';
        num = parseFloat(num).toFixed(decimal_format);
        num = numberWithCommas(num).replace(',', "");
        return num;
    }

    function restrictToPrefdecimals(e)
    {
        let num = e.value;
        let decimal_format = '{{ $preference['decimal_format_amount'] }}';
        if (decimal_format == '2')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
        }
        else if (decimal_format == '3')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,3})?/)[0];
        }
        else if (decimal_format == '4')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,4})?/)[0];
        }
        else if (decimal_format == '5')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,5})?/)[0];
        }
        else if (decimal_format == '6')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,6})?/)[0];
        }
        else if (decimal_format == '7')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,7})?/)[0];
        }
        else if (decimal_format == '8')
        {
            e.value = num.toString().match(/^-?\d+(?:\.\d{0,8})?/)[0];
        }
        console.log(e.value);
        return e.value;
    }

</script>