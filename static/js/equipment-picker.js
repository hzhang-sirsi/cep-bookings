'use strict';

(function ($, params) {
    let fieldIds = params.fieldIds;

    const makeRequest = (action, data) => {
        data.action = action;
        data._ajax_nonce = params._ajax.nonce[action];
        return $.post(params._ajax.url, data, 'json');
    };
    const convert12hto24h = (time12h) => {
        time12h = time12h.trim();
        const time = time12h.slice(0, time12h.length - 2).trim();
        const modifier = time12h.slice(time12h.length - 2).trim();
        let [hours, minutes] = time.split(':');
        if (hours === '12') {
            hours = '00';
        }

        [hours, minutes] = [parseInt(hours, 10), parseInt(minutes, 10)];
        if (modifier.toLowerCase() === 'pm') {
            hours += 12;
        }

        [hours, minutes] = [hours.toString().padStart(2, '0'), minutes.toString().padStart(2, '0')];
        return `${hours}:${minutes}`;
    };

    $(document).ready(function () {
        $('#' + fieldIds.searchButton).on('click', () => {
            (async function () {
                let root = document.getElementById(fieldIds.results);
                await (makeRequest(
                    'cb_equip_search', {
                        equipmentType: $('#' + fieldIds.equipmentType).val(),
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

                    if (response.data.posts.length === 0) {
                        throw 'No equipment found.';
                    }

                    root.innerHTML = '';

                    for (let post of response.data.posts) {
                        let container = document.createElement('div');
                        container.setAttribute('style', 'display: flex; flex-direction: column; max-width: 150px;')

                        let image = document.createElement('img');
                        image.setAttribute('src', post['thumbnail']);
                        image.setAttribute('style', 'max-height: 100px;');
                        let label = document.createElement('label');
                        label.textContent = post['title'];
                        let quantity = document.createElement('input');
                        quantity.setAttribute('type', 'number');
                        quantity.setAttribute('min', '0');
                        quantity.setAttribute('value', '0');

                        container.appendChild(image);
                        container.appendChild(label);
                        container.appendChild(quantity);

                        root.appendChild(container);
                    }
                }).catch((error) => {
                    root.innerHTML = '';

                    let errorElem = document.createElement('label');
                    errorElem.textContent = error.toString();

                    root.appendChild(errorElem);
                }));
            })();
        });

        $('#' + fieldIds.content).on($.modal.BEFORE_OPEN, function (event, modal) {
            $('#' + fieldIds.eventDate).val($('#EventStartDate').val());
            $('#' + fieldIds.startTime).val(convert12hto24h($('#EventStartTime').val()));
            $('#' + fieldIds.endTime).val(convert12hto24h($('#EventEndTime').val()));
        });
    });
}(jQuery, equipmentPickerAjaxParams));
