function determineDecimalPoint(type = 'fiat') 
{
    var currencyType = type;
    if (currencyType == 'crypto') {
        $('.pFees, .fFees, .total_fees').text(CRYPTODP);
        $("#amount").attr('placeholder', CRYPTODP);
    } else if (currencyType == 'fiat') {
        $('.pFees, .fFees, .total_fees').text(FIATDP);
        $("#amount").attr('placeholder', FIATDP);
    }
}