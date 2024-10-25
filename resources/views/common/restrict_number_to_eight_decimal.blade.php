<script type="text/javascript">

function hasWhiteSpace(s) {
  return /\s/g.test(s);
}

/**
 * Restrict currency rate upto 8 decimal places (as rate field in database is allowed upto 8 decimal places)
 * @param  {event} event
 * @return {string} value
 */
function restrictNumberToEightdecimals(e)
{
    var num = e.value;
    var checkNumWhitespace = hasWhiteSpace(num);
    if (!checkNumWhitespace && num.length != 0 && !isNaN(num))
    {
    	e.value = num.toString().match(/^-?\d+(?:\.\d{0,8})?/)[0];
    	return $.trim(e.value);
    }
}

</script>