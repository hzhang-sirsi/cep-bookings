'use strict';

(function ($, vis, params) {
    let fieldIds = params.fieldIds;

    const makeRequest = (action, data) => {
        data.action = action;
        data._ajax_nonce = params._ajax.nonce[action];
        return Promise.resolve($.post(params._ajax.url, data, 'json'));
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
        let selected = Object.keys(params.selected.reservations).length > 0 ? params.selected : null;
        let pending = {};

        const updateValue = (newValue) => {
            if (newValue === null) {
                return;
            }

            try {
                $('#' + fieldIds.summaryLabel).text(`${Object.keys(newValue.reservations).length} items - ${newValue.date} - ${newValue.startTime} to ${newValue.endTime}`);
                $('#' + fieldIds.value).val(JSON.stringify(newValue)).change();
                selected = newValue;
            } catch (e) {
            }
        };

        $('#' + fieldIds.searchButton).on('click', () => {
            (async function () {
                let root = document.getElementById(fieldIds.results);

                const [eventDate, startTime, endTime] = [
                    $('#' + fieldIds.eventDate).val(),
                    $('#' + fieldIds.startTime).val(),
                    $('#' + fieldIds.endTime).val()
                ];

                makeRequest('cb_equip_search', {
                    equipmentType: $('#' + fieldIds.equipmentType).val(),
                    eventId: params.postId,
                    eventDate: eventDate,
                    startTime: startTime,
                    endTime: endTime,
                }).then((response) => {
                    if (!response.success) {
                        if ('error' in response && response.error) {
                            throw response.error;
                        } else {
                            throw 'Unknown error occurred while processing your request';
                        }
                    }

                    pending = {
                        date: eventDate,
                        startTime: startTime,
                        endTime: endTime,
                        reservations: {},
                    };

                    if (response.data.posts.length === 0) {
                        throw 'No items found.';
                    }

                    return response.data.posts;
                }).then((posts) => {
                    root.innerHTML = '';

                    for (let post of posts) {
                        const [id, title, thumbnail, booked, quantity] = [post.id, post.title, post.thumbnail, post.booked, post.quantity];
                        const container = document.createElement('div');
                        container.classList.add('equipment-group-container');

                        const image = document.createElement('img');
                        if (typeof thumbnail === 'string' || thumbnail instanceof String) {
                            image.src = thumbnail;
                        }

                        const label = document.createElement('label');
                        label.textContent = title;

                        const availableQuantity = Math.max(quantity - booked, 0);
                        const inputContainer = document.createElement('div');
                        const input = document.createElement('input');
                        input.type = 'number';
                        input.min = '0';
                        input.max = availableQuantity.toString();
                        input.value = '0';
                        input.disabled = (availableQuantity === 0);

                        input.addEventListener('input', (e) => {
                            input.classList.add('selected');

                            const quantity = parseInt(e.target.value);
                            if (quantity > 0) {
                                pending.reservations[id] = quantity;
                            }
                            else {
                                delete pending.reservations[id];
                            }
                        });

                        const maxLabel = document.createElement('label');
                        maxLabel.textContent = `Max: ${availableQuantity}`;

                        inputContainer.append(input, maxLabel);
                        container.append(image, label, inputContainer);
                        root.appendChild(container);
                    }
                }).catch((error) => {
                    root.innerHTML = '';

                    let errorElem = document.createElement('label');
                    errorElem.textContent = error.toString();

                    root.appendChild(errorElem);
                });
            })();
        });

        $('#' + fieldIds.saveButton).on('click', () => {
            updateValue(pending);
            $.modal.close();
        });
        updateValue(selected);

        $('#' + fieldIds.content).on($.modal.BEFORE_OPEN, function (event, modal) {
            let [eventDate, startTime, endTime] = [$('#' + fieldIds.eventDate), $('#' + fieldIds.startTime), $('#' + fieldIds.endTime)];
            if (selected !== null) {
                eventDate.val(selected.date);
                startTime.val(selected.startTime);
                endTime.val(selected.endTime);
            } else {
                eventDate.val($('#EventStartDate').val());
                startTime.val(convert12hto24h($('#EventStartTime').val()));
                endTime.val(convert12hto24h($('#EventEndTime').val()));
            }
        });
    });
}(jQuery, vis, equipmentPickerAjaxParams));
