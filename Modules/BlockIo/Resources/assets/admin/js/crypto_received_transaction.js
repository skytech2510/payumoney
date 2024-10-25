'use strict';

var sDate;
var eDate;

$(window).on('load', function (e)
{
    $(".select2").select2({});

    //Date range as a button
    $('#daterange-btn').daterangepicker(
    {
        ranges   : {
          'Today'       : [moment(), moment()],
          'Yesterday'   : [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days' : [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month'  : [moment().startOf('month'), moment().endOf('month')],
          'Last Month'  : [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
         },
        startDate: moment().subtract(29, 'days'),
        endDate  : moment()
    },
    function (start, end)
    {
        var sessionDate = sessionDateFormat;
        var sessionDateFinal = sessionDate.toUpperCase();

        sDate = moment(start, 'MMMM D, YYYY').format(sessionDateFinal);
        $('#startfrom').val(sDate);

        eDate = moment(end, 'MMMM D, YYYY').format(sessionDateFinal);
        $('#endto').val(eDate);

        $('#daterange-btn span').html('&nbsp;' + sDate + ' - ' + eDate + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    })

    var startDate = startFromDate;
    var endDate = startToDate;

    if (startDate == '') {
        $('#daterange-btn span').html('<i class="fa fa-calendar"></i> &nbsp;&nbsp; Pick a date range &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    } else {
        $('#daterange-btn span').html(startDate + ' - ' +endDate + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
    }
});

$("#daterange-btn").mouseover(function() {
    $(this).css('background-color', 'white');
    $(this).css('border-color', 'grey !important');
});

$(document).on('keyup keypress', '#user_input', function (e)
{
    if (e.type=="keyup" || e.type=="keypress") {
        var user_input = $('form').find("input[type='text']").val();
        if(user_input.length === 0) {
            $('#user_id').val('');
            $('#error-user').html('');
            $('form').find("button[type='submit']").prop('disabled',false);
        }
    }
});

$('#user_input').autocomplete(
{
    source:function(req,res)
    {
        if (req.term.length > 0) {
            $.ajax({
                url: cryptoReceivedUserSearch,
                dataType:'json',
                type:'get',
                data:{
                    search:req.term
                },
                success:function (response)
                {
                    $('form').find("button[type='submit']").prop('disabled',true);

                    if(response.status == 'success') {
                        res($.map(response.data, function (item)
                        {
                            return {
                                id : item.user_id,
                                first_name : item.first_name,
                                last_name : item.last_name,
                                value: item.first_name + ' ' + item.last_name
                            }
                        }));
                    }
                    else if(response.status == 'fail') {
                        $('#error-user').addClass('text-danger').html('User Does Not Exist!');
                    }
                }
            })
        } else {
            $('#user_id').val('');
        }
    },
    select: function (event, ui)
    {
        var e = ui.item;
        $('#error-user').html('');
        $('#user_id').val(e.id);
        $('form').find("button[type='submit']").prop('disabled',false);
    },
    minLength: 0,
    autoFocus: true
});

// csv
$(document).on('click', '#csv', function (e)
{
    e.preventDefault();

    var startfrom = $('#startfrom').val();
    var endto = $('#endto').val();
    var status = $('#status').val();
    var currency = $('#currency').val();
    var user_id = $('#user_id').val();

    window.location = ADMIN_URL + "/crypto-received-transactions/csv?startfrom="+startfrom
    +"&endto="+endto
    +"&status="+status
    +"&currency="+currency
    +"&user_id="+user_id;
});

// pdf
$(document).on('click', '#pdf', function (e)
{
    e.preventDefault();

    var startfrom = $('#startfrom').val();
    var endto = $('#endto').val();
    var status = $('#status').val();
    var currency = $('#currency').val();
    var user_id = $('#user_id').val();
    window.location = ADMIN_URL + "/crypto-received-transactions/pdf?startfrom="+startfrom
    +"&endto="+endto
    +"&status="+status
    +"&currency="+currency
    +"&user_id="+user_id;
});