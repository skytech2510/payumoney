'use strict';

$(document).on('click', '#printQrCodeBtn', function (e) {
    e.preventDefault();
    $(this).prop('href', printQrCodeUrl);
    window.open(printQrCodeUrl, '_blank');
});

$('.dash-wallet-box').on('click', function(){
    location.href = walletRoute;
})