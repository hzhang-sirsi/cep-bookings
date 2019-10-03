(function ($, window, document) {
    'use strict';
    // execute when the DOM is ready
    $(document).ready(function () {
        function handleResponse(response) {
            try {
                if (!response.success) {
                    if ('error' in response && response.error) {
                        throw response.error;
                    } else {
                        throw "Unknown error occurred while processing your request";
                    }
                }
            } catch (e) {
                alert(e);
                return false;
            }

            return true;
        }

        $('#cep-marketo-send-test-email-btn').on('click', function () {
            $('#cep-marketo-send-test-email-btn').attr('disabled', 'disabled');

            $.post(ajaxParams.url,
                {
                    _ajax_nonce: ajaxParams.nonce,
                    action: 'editEvent',
                    command: 'sendSampleEmail',
                    targetEmail: $('#cep-marketo-test-email-address').val(),
                    program: ajaxParams.program,
                }, function (response) {
                    $('#cep-marketo-send-test-email-btn').removeAttr('disabled');
                    if (handleResponse(response)) {
                        $('#cep-marketo-send-test-email-btn').prop('text', 'Email sent. Press to resend');
                    }
                }
            );
        });

        $('#cep-marketo-send-campaign-1-time-btn').on('click', function () {
            $('#cep-marketo-send-campaign-1-time-btn').attr('disabled', 'disabled');

            $.post(ajaxParams.url,
                {
                    _ajax_nonce: ajaxParams.nonce,
                    action: 'editEvent',
                    command: 'scheduleReminder1',
                    program: ajaxParams.program,
                    postId: ajaxParams.postId,
                }, function (response) {
                    if (handleResponse(response)) {
                        $('#cep-marketo-send-campaign-1-time-btn').prop('text', 'Reminder has been scheduled');
                    }
                }
            );
        });

        $('#cep-marketo-send-campaign-2-time-btn').on('click', function () {
            $('#cep-marketo-send-campaign-2-time-btn').attr('disabled', 'disabled');

            $.post(ajaxParams.url,
                {
                    _ajax_nonce: ajaxParams.nonce,
                    action: 'editEvent',
                    command: 'scheduleReminder2',
                    program: ajaxParams.program,
                    postId: ajaxParams.postId,
                }, function (response) {
                    if (handleResponse(response)) {
                        $('#cep-marketo-send-campaign-2-time-btn').prop('text', 'Reminder has been scheduled');
                    }
                }
            );
        });
    });
}(jQuery, window, document));
