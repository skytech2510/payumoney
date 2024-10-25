"use strict";
const vErC = 'error';
const pErC = 'has-validation-error';
const i = document.querySelectorAll('.form-control');
i.forEach(function (inp) {
    inp.addEventListener('input', function () {
        // We can only update the error or hide it on inp.
        // Otherwise it will show when typing.
        checkValidity(inp, {inErr: false});
    })
    inp.addEventListener('invalid', function (e) {
        // prevent showing the default display
        e.preventDefault()
        // We can also create the error in invalid.
        checkValidity(inp, {inErr: true})
    })
});

function checkValidity (inp, options) {
    var inErr = options.inErr;
    var p = inp.parentNode;
    var err = p.querySelector(`.${vErC}`) || document.createElement('label');
    if (!inp.validity.valid && inp.validationMessage) {
        err.className = vErC;
        err.textContent = inp.validationMessage;
        if (inErr) {
            if (inp.validity.valueMissing && inp.getAttribute('data-value-missing')) {
                inp.setCustomValidity(inp.getAttribute('data-value-missing'));
                err.innerHTML = inp.getAttribute('data-value-missing');
            } else if (inp.validity.typeMismatch && inp.getAttribute('data-type-mismatch')) {
                inp.setCustomValidity(inp.getAttribute('data-type-mismatch'));
                err.innerHTML = inp.getAttribute('data-type-mismatch');
            } else if (inp.validity.patternMismatch && inp.getAttribute('data-pattern')) {
                inp.setCustomValidity(inp.getAttribute('data-pattern'));
                err.innerHTML = inp.getAttribute('data-pattern');
            } else if (inp.validity.tooShort && inp.getAttribute('data-min-length')) {
                inp.setCustomValidity(inp.getAttribute('data-min-length'));
                err.innerHTML = inp.getAttribute('data-min-length');
            } else if (inp.validity.tooLong && inp.getAttribute('data-max-length')) {
                inp.setCustomValidity(inp.getAttribute('data-max-length'));
                err.innerHTML = inp.getAttribute('data-max-length');
            } else if (inp.validity.stepMismatch && inp.getAttribute('data-step-mismatch')) {
                inp.setCustomValidity(inp.getAttribute('data-step-mismatch'));
                err.innerHTML = inp.getAttribute('data-step-mismatch');
            } else if (inp.validity.rangeUnderflow && inp.getAttribute('data-min')) {
                inp.setCustomValidity(inp.getAttribute('data-min'));
                err.innerHTML = inp.getAttribute('data-min');
            } else if (inp.validity.rangeOverflow && inp.getAttribute('data-max')) {
                inp.setCustomValidity(inp.getAttribute('data-max'));
                err.innerHTML = inp.getAttribute('data-max');
            } else if (inp.validity.badInput && inp.getAttribute('data-bad-input')) {
                inp.setCustomValidity(inp.getAttribute('data-bad-input'));
                err.innerHTML = inp.getAttribute('data-bad-input');
            } else if (inp.validity.customError && inp.getAttribute('data-custom-error')) {
                inp.setCustomValidity(inp.getAttribute('data-custom-error'));
                err.innerHTML = inp.getAttribute('data-custom-error');
            }
            p.append(inp, err);
            p.classList.add(pErC);
        } else  {
            var hasAttr = inp.getAttribute('data-related');
            if (hasAttr) {
                var el = document.getElementById(hasAttr);
                var elP = el.parentNode;
                var elEr = elP.querySelector(`.${vErC}`)
                el.setCustomValidity('');
                elP.classList.remove(pErC);
                if (elEr != '') {
                  elEr.remove();
                }
            }
            inp.setCustomValidity('');
            p.classList.remove(pErC);
            err.remove();
        }
        if (typeof(once) != "undefined") {
            if (once == true) {
                once = false;
                $('html, body').animate({
                  scrollTop: $('.error').offset().top
                }, 1000);
            }
        }
    } else {
        inp.setCustomValidity('');
        p.classList.remove(pErC);
        err.remove();
    }
}

// remove select2 error messages
$(document).on('change', '.sl_common_bx', function (e) {
    this.setCustomValidity('');
    if ($(e.currentTarget).val() != '') {
        $('#'+ $(this).attr('id')).parent('div').find('.error').hide();
    } else {
        $('#'+ $(this).attr('id')).parent('div').find('.error').show();
    }
});

// clearing the input field
$(".onblur-clear-this-input").on("blur", function() {
    const a = document.getElementById(this.id),
        b = a.closest(".form");
    if (b) var c = b.querySelector(".onblur-clear-icon");
    c.style.display = 0 == document.getElementById(this.id).value.length ? "none" : "block", c.addEventListener("click", function() {
        a.value = "", c.style.display = "none"
    })
}), $(".onblur-clear-this-input").on("focus", function() {
    const a = document.getElementById(this.id),
        b = a.closest(".form");
    if (b) var c = b.querySelector(".onblur-clear-icon");
    c.style.display = "none"
});
