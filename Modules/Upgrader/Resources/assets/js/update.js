'use strict';

$(document).on("change", "#validatedCustomFile", function (e) {
    
    $('.custom-file-label').text(this?.files[0]?.name ?? jsLang('Choose file'));

});