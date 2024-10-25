'use strict';

// preview admin picture and small admin picture on change
$(document).on('change','#admin-picture', function()
{
    let orginalSource = defaultImageSource;
    readFileOnChange(this, $('.user-image'), orginalSource);
    readFileOnChange(this, $('.img-circle'), orginalSource);
    readFileOnChange(this, $('#admin-picture-preview'), orginalSource);
});

$('#profile_form').validate({
    rules: {
        first_name: {
            required: true
        },
        last_name: {
            required: true
        },
        picture: {
            extension: extensionsValidationRule
        },
    },
    messages: {
      picture: {
        extension: extensionsValidationMessage
      },
    },
});