'use strict';


$(".deposit-search_item").on('click', function() {
    $(".deposit-search_item").removeClass("item-active");
    $(this).addClass("item-active");
});


$('#search-coin').on('keyup', $.debounce(500, function(){

    let coinName = $(this).val();

    let filteredCoins =  coinList.filter(function(coin) {
        return (coin.name).match(new RegExp("[^,]*"+coinName+"[^,]*",'ig'));
    });


    let coinListHTML = '';

    $( filteredCoins ).each(function( index, filteredCoin ) {
        coinListHTML +=`
            <button class="deposit-search_item coin-div" coin-iso="${filteredCoin.iso}">
                <img class="deposit-search_item-img" src="${filteredCoin.icon}" alt="${coinIcon}">
                <div class="deposit-search_item-content">
                    <p class="mb-0 f-16 gilroy-Semibold text-dark">${filteredCoin.name}</p>
                    <p class="mb-0 f-14 gilroy-medium text-gray-100" coin-rate="${filteredCoin.rate}">${filteredCoin.rate}</p>
                </div>
            </button>
        `;
    });

    $("#coin-list").empty();
    $("#coin-list").html(coinListHTML);
}));

$(document).on('click', '.coin-div', function(e){

    $('.select-div-active').removeClass('select-div-active').addClass('select-div');
    $(this).children('div').addClass("select-div-active").removeClass('select-div');

    let coinIso = $(this).attr('coin-iso');

    let coinRate = $(this).find('p').last().attr('coin-rate');

    $(".coinpayment-submit-button").removeClass('d-none');
    $('#selected-coin').text(coinIso);
    $('#input-selected-coin').val(coinIso);
    $('#selected-iso').text(coinIso);
    $('#selected-coin-rate').text(coinRate);

});


$('.coin-main-div').on('click', function() {
    $('.class-toggle').toggle();
    $('.class-toggle').toggleClass('add-class');
});


$('.coinpayment-submit-button').on('click',function() {
    $(this).attr('disabled', 'disabled');
    $(".spinner").removeClass('d-none');
    $("#coinpaymentSubmitBtnText").text(submitText);
    $(this).parents('form').trigger('submit');
});



