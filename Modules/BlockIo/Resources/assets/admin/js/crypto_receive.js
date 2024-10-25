'use strict';

function restrictNumberToPrefdecimalOnInput(e)
{
    var type = $('#network').data('type')
    restrictNumberToPrefdecimal(e, type);
}

if ($('.content').find('#crypto-receive-create').length) {

    var merchantAddress;
    var userAddress;
    var amount;
    var priority = 'low';
    var userId;

    $("#user_id").select2({});

    var userAddressErrorFlag = false;
    var amountErrorFlag = false;

    function checkSubmitBtn()
    {
        if (!userAddressErrorFlag && !amountErrorFlag) {
            $('#admin-crypto-receive-submit-btn').attr("disabled", false);
        } else {
            $('#admin-crypto-receive-submit-btn').attr("disabled", true);
        }
    }

    //Get merchant network address, merchant network balance and user network address
    function getUserNetworkAddressBalanceWithMerchantAddress(userId)
    {
        $.ajax({
            url: userBalanceWithMerchantAddressUrl,
            type: "get",
            dataType: 'json',
            data: {
                'network': network,
                'user_id': userId
            },
            beforeSend: function ()
            {
                swal(pleaseWait, loading, {
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                    buttons: false,
                });
            },
        })
        .done(function(res) {

            if (res.status == 401) {
                $('.amount-validation-error').text(res.message);
                userAddressErrorFlag = true;
                amountErrorFlag = true;
                checkSubmitBtn();

                swal({
                    title: errorText,
                    text:  res.message,
                    icon: "error",
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });
            } else {
                //user-address-div
                $('#user-div').after( `<div class="form-group row" id="user-address-div">
                    <label class="col-sm-3 control-label f-14 fw-bold text-end" for="user-address">${userCryptoAddress}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control f-14" name="userAddress" id="user-address" value="${res.userAddress}"/>
                    </div>
                </div>`);

                //user-balance-div
                $('#user-address-div').after( `<div class="form-group row" id="user-balance-div">
                    <label class="col-sm-3 control-label f-14 fw-bold text-end" for="user-balance">${userCryptoBalance}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control f-14" name="userBalance" id="user-balance" value="${res.userAddressBalance}"/>
                    </div>
                </div>`);

                //merchant-address-div
                $('#user-balance-div').after( `<div class="form-group row" id="merchant-address-div">
                    <label class="col-sm-3 control-label f-14 fw-bold text-end" for="merchant-address">${merchantCryptoAddress}</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control f-14" name="merchantAddress" id="merchant-address" value="${res.merchantAddress}"/>
                    </div>
                </div>`);

                var previousReceivedUrl = window.localStorage.getItem("previousReceivedUrl");
                var confirmationCryptoReceiveUrl = confirmationCryptoReceivedUrl;
                var cryptoReceiveAmount = window.localStorage.getItem('crypto-received-amount');
                var previousPriority    = window.localStorage.getItem('priority');

                if ((confirmationCryptoReceiveUrl == previousReceivedUrl) && cryptoReceiveAmount != null) {
                    //amount-div
                    $('#merchant-address-div').after( `<div class="form-group row" id="amount-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-end require" for="Amount">${cryptoReceivedAmount}</label>
                        <div class="col-sm-6" id="amount-input-div">
                            <input type="text" class="form-control f-14 amount" name="amount" placeholder="0.00000000" id="amount" value="${cryptoReceiveAmount}" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="${requiredField}"/>
                            <span class="amount-validation-error"></span>
                        </div>
                    </div>`);

                    //Get network fees
                    checkMerchantAmountValidity($('.amount').val().trim(), $("#merchant-address").val().trim(), $("#user-address").val().trim(), network, previousPriority)

                    $("#admin-crypto-receive-submit-btn").attr("disabled", false);
                    $(".fa-spin").hide();
                    $("#admin-crypto-receive-submit-btn-text").html(`Next&nbsp;<i class="fa fa-angle-right"></i>`);
                    window.localStorage.removeItem('crypto-received-amount');
                    window.localStorage.removeItem('previousReceivedUrl');
                    window.localStorage.removeItem('priority');
                } else {
                    //amount-div
                    $('#merchant-address-div').after( `<div class="form-group row" id="amount-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-end require" for="Amount">${cryptoReceivedAmount}</label>
                        <div class="col-sm-6" id="amount-input-div">
                            <input type="text" class="form-control f-14 amount" name="amount" placeholder="0.00000000" id="amount" onkeypress="return isNumberOrDecimalPointKey(this, event);" oninput="restrictNumberToPrefdecimalOnInput(this)" required data-value-missing="${requiredField}"/>
                            <span class="amount-validation-error"></span>
                        </div>
                    </div>`);
                }

                $('.amount-validation-error').after(`<div class="clearfix"></div>
                    <small class="form-text text-muted f-12 amount-hint"><b>*${cryptoTransactionText}</b></small><br/>
                    <small class="form-text text-muted f-12"><b>*${minWithdrawan.replace(':x', blockIoMinLimit.amount[network]).replace(':y', network) }</b></small><br/>
                    <small class="form-text text-muted f-12"><b>*${minNetworkFee.replace(':x', blockIoMinLimit.networkFee[network]).replace(':y', network) }</b></small><br/>
                `);

                $('#amount-div').after(`
                    <div class="form-group row" id="priority-div">
                        <label class="col-sm-3 control-label f-14 fw-bold text-end" for="priority">Priority</label>
                        <div class="col-sm-6">
                            <select class="form-control f-14" name="priority" id="priority">
                                    <option value="low">${low}</option>
                                    <option value="medium">${medium}</option>
                                    <option value="high">${high}</option>
                            </select>
                            <small class="form-text text-muted f-12 priority-hint"><b>*${networkFeeText}</b></small><br/>
                            <small class="form-text text-muted f-12 important-text"><b>*${prioritySpecifyText}</b></small>
                        </div>
                    </div>
                `);

                //submit-anchor-div
                $('#priority-div').after( `<div class="form-group row" id="submit-anchor-div">
                    <label class="col-sm-3"></label>
                    <div class="col-sm-6">
                        <a href="${backButtonUrl}" class="btn btn-theme-danger"><span><i class="fa fa-angle-left"></i>&nbsp;${backButton}</span></a>
                        <button type="submit" class="btn btn-theme pull-right" id="admin-crypto-receive-submit-btn">
                            <i class="fa fa-spinner fa-spin d-none"></i>
                            <span id="admin-crypto-receive-submit-btn-text">${nextButton}&nbsp;<i class="fa fa-angle-right"></i></span>
                        </button>
                    </div>
                </div>`);

                $('#user-address, #user-balance, #merchant-address').attr('readonly', true);

                $('.amount-validation-error').text('');
                userAddressErrorFlag = false;
                amountErrorFlag = false;
                checkSubmitBtn();

                $("#priority").select2({});

                // Set focus on amount
                $("#amount").focus();

                //close swal
                swal.close();
            }
        })
        .fail(function(error) {
            swal({
                title: errorText,
                text:  JSON.parse(error.responseText).exception,
                icon: "error",
                closeOnClickOutside: false,
                closeOnEsc: false,
            });
        });
    }

    //Check Minimum Amount
    function checkMinimumAmount(message)
    {
        $('.amount-validation-error').text(message);
        userAddressErrorFlag = true;
        amountErrorFlag = true;
        checkSubmitBtn();
    }

    //Check Amount Validity
    function checkMerchantAmountValidity(amount, merchantAddress, userAddress, network, priority)
    {
        if (amount < blockIoMinLimit.amount[network]) {
            checkMinimumAmount(`${minAmount.replace(':x', blockIoMinLimit.amount[network] + ' ' + network)}`)
        } else {
            $('.amount-validation-error').text('');
            userAddressErrorFlag = false;
            amountErrorFlag = false;
            checkSubmitBtn();

            $.ajax({
                method: "GET",
                url: validateBalanceUrl,
                dataType: "json",
                data: {
                    'network': network,
                    'amount': amount,
                    'merchantAddress': merchantAddress,
                    'userAddress': userAddress,
                    'priority': priority
                },
                beforeSend: function ()
                {
                    swal(pleaseWait, loading, {
                        closeOnClickOutside: false,
                        closeOnEsc: false,
                        buttons: false,
                    });
                },
            })
            .done(function(res) {
                swal.close();
                if (res.status == 401) {
                    $('.amount-validation-error').text(res.message);
                    userAddressErrorFlag = true;
                    amountErrorFlag = true;
                    checkSubmitBtn();
                }
            })
            .fail(function(error) {
                swal({
                    title: errorText,
                    text:  JSON.parse(error.responseText).exception,
                    icon: "error",
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                });
            });
        }
    }

    //Get merchant network address, merchant network balance and user network address
    $(document).on('change', '#user_id', function ()
    {
        //Remove merchant address, merchant balance and amount div on change of network
        $('#user-address-div, #user-balance-div, #merchant-address-div, #amount-div, #submit-anchor-div, #priority-div').remove();

        //Get admin address balance
        userId = $(this).val();
        let userName = $('#user_id option:selected').text()
        $('.user-full-name').text(userName);
        
        if (userId) {
            getUserNetworkAddressBalanceWithMerchantAddress(userId);
        }
    });


    // Validate Amount
    $(document).on('keyup', '.amount', $.debounce(700, function() {
        // Get amount
        network = $('#network').val();
        merchantAddress = $("#merchant-address").val().trim();
        userAddress = $("#user-address").val().trim();
        amount = $(this).val().trim();
        priority = $('#priority').val();

        if (amount.length > 0 && !isNaN(amount)) {
            checkMerchantAmountValidity(amount, merchantAddress, userAddress, network, priority)
        } else {
            $('.amount-validation-error').text('');
            userAddressErrorFlag = false;
            amountErrorFlag = false;
            checkSubmitBtn();
        }
    }));

    $(document).on('change', '#priority', function ()
    {
        network = $('#network').val();
        merchantAddress = $("#merchant-address").val().trim();
        userAddress = $("#user-address").val().trim();
        amount = $('.amount').val().trim();
        priority = $(this).val();

        if (amount.length > 0 && !isNaN(amount)) {
            checkMerchantAmountValidity(amount, merchantAddress, userAddress, network, priority)
        } else {
            $('.amount-validation-error').text('');
            userAddressErrorFlag = false;
            amountErrorFlag = false;
            checkSubmitBtn();
        }
    });

    $(document).on('submit', '#admin-crypto-receive-form', function() {

        //Set amount to localstorage for showing on create page on going back from confirm page
        window.localStorage.setItem("user_id", $('#user_id').val());
        window.localStorage.setItem("crypto-received-amount", $('.amount').val().trim());
        window.localStorage.setItem("priority", $('#priority').val());

        $("#admin-crypto-receive-submit-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('d-none');
        $("#admin-crypto-receive-submit-btn-text").text(receiving);

        setTimeout(function(){
            $(".fa-spinner").addClass('d-none');
            $("#admin-crypto-receive-submit-btn").attr("disabled", false);
            $("#admin-crypto-receive-submit-btn-text").text(receive);
        }, 10000);
    });
}


if ($('.content').find('#crypto-receive-confirm').length) {
    
    function cryptoReceiveConfirmBack()
    {
        window.localStorage.setItem("previousReceivedUrl",document.URL);
        window.location.replace(cryptoReceiveBackUrl);
    }

    //Only go back by back button, if submit button is not clicked
    $(document).on('click', '.admin-user-crypto-receive-confirm-back-btn', function (e)
    {
        e.preventDefault();
        cryptoReceiveConfirmBack();
    });


    $(document).on('submit', '#admin-user-crypto-receive-confirm', function() {
        //Set amount to localstorage for showing on create page on going back from confirm page
        window.localStorage.removeItem('crypto-received-amount');
        window.localStorage.removeItem("previousCrytoSentUrl");
        window.localStorage.removeItem('user_id');
        window.localStorage.removeItem('priority');

        $("#admin-user-crypto-receive-confirm-btn").attr("disabled", true);
        $(".fa-spinner").removeClass('d-none');
        $("#admin-user-crypto-receive-confirm-btn-text").text(confirming);

        $('.admin-user-crypto-receive-confirm-back-btn').attr("disabled", true).click(function (e) {
            e.preventDefault();
        });

        //Make back anchor prevent click
        $('.admin-user-crypto-receive-confirm-back-link').click(function (e) {
            e.preventDefault();
        });

        setTimeout(function(){
            $(".fa-spinner").addClass('d-none');
            $("#admin-user-crypto-receive-confirm-btn").attr("disabled", false);
            $("#admin-user-crypto-receive-confirm-btn-text").text(confirm);
        }, 10000);
    });
}