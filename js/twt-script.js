//TWT frontend script
jQuery(document).ready(function ($) {
    $('.twt_call_number, .twt_sms_number, .twt_fax_number').intlTelInput({
        autoFormat: true,
        initialCountry: "auto",
        onlyCountries: countries
    });

    $('.twt_call_button').click(function () {
        var button = $(this);
        var agent = $(this).attr('data-agent');
        if ($.trim($('.twt_call_number').val()) == '') {
            $('.twt_call_number').focus();
            return false;
        }
        var user_phone = $.trim($('.twt_call_number').val());
        user_phone = user_phone.replace(/\s/g, "").replace(/[-]/g, "");
        var welcome = $('.twt_welcome_message').val();
        var security = $('#twt_nonce_field').val();
        button.after('<span class="twt-loader"></span>');
        button.attr('disabled', true);
        $('.twt_error').remove();
        var data = {
            'action': 'make_the_call_guest',
            'agent': agent,
            'user': user_phone,
            'welcome': welcome,
            'security': security
        };
        $.ajax({
            url: ajax_url,
            data: data,
            type: 'post',
            success: function (msg) {
                if (msg == 'Done') {
                    $('.twt_error').remove();
                    $('.twt-loader').remove();
                } else {
                    //console.log(msg);
                    $('.twt-loader').remove();
                    button.after('<span class="twt_error"> call failed</span>');
                    button.attr('disabled', false);
                }
            }, complete: function () {
                setTimeout(function () {
                    $('.twt-loader').remove();
                    button.attr('disabled', false);
                }, 5000);
            }
        });
    });

});
