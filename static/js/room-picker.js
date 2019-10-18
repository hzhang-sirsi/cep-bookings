'use strict';

(function ($, params) {
    let fieldIds = params.fieldIds;

    function makeRequest(action, data) {
        data.action = action;
        data._ajax_nonce = params._ajax.nonce[action];
        return $.post(params._ajax.url, data, 'json');
    }

    $(document).ready(function () {
        $('#' + fieldIds.searchButton).on('click', () => {
            (async function () {
                await (makeRequest(
                        'cb_room_search', {
                            roomType: $('#' + fieldIds.roomType).val(),
                            eventDate: $('#' + fieldIds.eventDate).val(),
                            startTime: $('#' + fieldIds.startTime).val(),
                            endTime: $('#' + fieldIds.endTime).val(),
                        }).then(function handleResponse(response) {
                        if (!response.success) {
                            if ('error' in response && response.error) {
                                throw response.error;
                            } else {
                                throw "Unknown error occurred while processing your request";
                            }
                        }

                        let root = document.getElementById(fieldIds.results);
                        root.innerHTML = '';

                        if (response.data.posts.length > 0) {
                            for (let post of response.data.posts) {
                                let container = document.createElement('div');
                                container.setAttribute('style', 'display: flex; flex-direction: column;')

                                let image = document.createElement('img');
                                image.setAttribute('src', post['thumbnail']);
                                image.setAttribute('style', 'max-width: 150px; max-height: 100px;');
                                let label = document.createElement('label');
                                label.textContent = post['title'];
                                container.appendChild(image);
                                container.appendChild(label);

                                root.appendChild(container);
                            }
                        } else {
                            let error = document.createElement('label');
                            error.textContent = 'No rooms available.';

                            root.appendChild(error);
                        }
                    })
                );
            })();
        });

        $('#' + fieldIds.content).on($.modal.BEFORE_OPEN, function (event, modal) {
            $('#' + fieldIds.eventDate).val($('#EventStartDate').val());
            $('#' + fieldIds.startTime).val($('#EventStartTime').val());
            $('#' + fieldIds.endTime).val($('#EventEndTime').val());
        });
    });
}(jQuery, roomPickerAjaxParams));
