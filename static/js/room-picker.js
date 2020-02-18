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
        let selected = params.selected.length > 0 ? params.selected[0] : null;
        let pending = null;

        const updateValue = (newValue) => {
            if (newValue === null) {
                return;
            }

            try {
                $('#' + fieldIds.summaryLabel).text(`${newValue.title} - ${newValue.date} - ${newValue.startTime} to ${newValue.endTime}`);
                $('#' + fieldIds.value).val(JSON.stringify(newValue)).change();
                selected = newValue;
            } catch (e) {
            }
        };

        const searchHandler = () => {
            (async function () {
                let root = document.getElementById(fieldIds.results);

                const [eventDate, startTime, endTime] = [
                    $('#' + fieldIds.eventDate).val(),
                    $('#' + fieldIds.startTime).val(),
                    $('#' + fieldIds.endTime).val()
                ];

                makeRequest('cb_room_search', {
                    roomType: $('#' + fieldIds.roomType).val(),
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

                    if (response.data.posts.length === 0) {
                        throw 'No rooms available.';
                    }

                    return response.data.posts;
                }).then((posts) => {
                    // Create a DataSet (allows two way data-binding)
                    let groups = new vis.DataSet([]);
                    let items = new vis.DataSet([
                        {
                            id: 'background',
                            start: new Date(eventDate + 'T' + startTime),
                            end: new Date(eventDate + 'T' + endTime),
                            type: 'background',
                        }
                    ]);

                    let startDate = new Date(eventDate + 'T00:00:00');
                    let endDate = new Date(eventDate + 'T00:00:00').setDate(startDate.getDate() + 1);
                    // Configuration for the Timeline
                    let options = {
                        width: '100%',
                        height: '100%',
                        start: startDate,
                        end: endDate,
                        moveable: false,
                        selectable: false,
                        zoomable: false,
                        stack: false,
                        stackSubgroups: false,
                        margin: 0,
                        groupHeightMode: 'fixed',
                        groupTemplate: (group) => {
                            const container = document.createElement('div');
                            container.classList.add('timeline-group-container');
                            if (group.available !== true) {
                                container.classList.add('timeline-group-disabled');
                            }
                            else {
                                container.addEventListener('click', (e) => {
                                    root.querySelectorAll('.timeline-group-selected').forEach((e) => {
                                        e.classList.remove('timeline-group-selected');
                                    });
                                    container.classList.add('timeline-group-selected');

                                    pending = {
                                        post_id: group.id,
                                        title: group.content,
                                        date: eventDate,
                                        startTime: startTime,
                                        endTime: endTime,
                                    };
                                });
                            }
                            if (selected !== null && group.id === selected.post_id) {
                                container.classList.add('timeline-group-selected');
                            }

                            const label = document.createElement('label');
                            label.textContent = group.content;
                            container.appendChild(label);

                            return container;
                        },
                    };

                    root.innerHTML = '';
                    for (let post of posts) {
                        if (post.reservations !== undefined && post.reservations !== null) {
                            for (let reservation of post.reservations) {
                                let start = new Date(eventDate + 'T' + reservation.start_time);
                                let end = new Date(eventDate + 'T' + reservation.end_time);

                                items.add({
                                    group: post.id,
                                    id: reservation.event_id,
                                    content: reservation.event_name,
                                    start: start,
                                    end: end
                                });
                            }
                        }

                        groups.add({id: post.id, content: post.title, available: post.available})
                    }

                    root.innerHTML = '';
                    // Create a Timeline
                    let timeline = new vis.Timeline(root, items, groups, options);
                }).catch((error) => {
                    root.innerHTML = '';

                    let errorElem = document.createElement('label');
                    errorElem.textContent = error.toString();

                    root.appendChild(errorElem);
                });
            })();
        };

        $('#' + fieldIds.searchButton).on('click', searchHandler);

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

            searchHandler();
        });
    });
}(jQuery, vis, roomPickerAjaxParams));
