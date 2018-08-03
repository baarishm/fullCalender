<?php
//index.php
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Jquery Fullcalandar Integration with PHP and Mysql</title>
        <link rel="stylesheet" href="fullcalendar/fullcalendar.css" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.0.0-alpha.6/css/bootstrap.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
        <script src="fullcalendar/fullcalendar.js"></script>
        <script>

            $(document).ready(function () {
                var appointments = 0;
                function makeid() {
                    var text = "";
                    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

                    for (var i = 0; i < 11; i++)
                        text += possible.charAt(Math.floor(Math.random() * possible.length));

                    return text;
                }

                $("#form_id").val(makeid());
                var calendar = $('#calendar').fullCalendar({
                    editable: true,
                    slotDuration: '00:20:00',
//                    slotLabelInterval: 20,
//                    slotLabelFormat: 'h(:mm)a',
                    defaultView: 'agendaWeek',
                    allDaySlot: false,
                    eventConstraint: {
                        start: '00:00', // a start time (start of the day in this example)
                        end: '24:00', // an end time (end of the day in this example)
                    },
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    events: 'load.php?id=' + $("#form_id").val(),
                    eventRender: function (event, element) {
                        if (!event.editable) {
                            element.addClass('fc-event-non-editable');
                            element.draggable = false;
                        }
                    },
                    eventOverlap: false,
                    selectable: true,
                    selectHelper: true,
                    select: function (start, end, allDay, revertFunc)
                    {
                        var myDate = new Date();
                        myDate.setDate(myDate.getDate() - 1);
                        var eventData = {
                            start: start,
                            end: end
                        };
                        if (appointments < 3) {
                            if (start >= myDate.getTime()) {
                                if ((end - start) <= '5400000') {
                                    var title = prompt("Enter Event Title");
                                    if (title)
                                    {
                                        var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                                        var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                                        $.ajax({
                                            url: "insert.php",
                                            type: "POST",
                                            data: {title: title, start: start, end: end, user_id: $("#form_id").val()},
                                            success: function ()
                                            {
                                                alert("Added Successfully");
                                                $('#calendar').fullCalendar('unselect');
                                                $('#calendar').fullCalendar('refetchEvents');
                                                appointments++;
                                            }
                                        })
                                    } else {
                                        alert('Please give a name to your appointment.');
                                        $('#calendar').fullCalendar('unselect');
                                    }
                                } else {
                                    alert('Appointment could be of maximum 1:30 hrs.');
                                    $('#calendar').fullCalendar('unselect');
                                }
                            } else {
                                alert('It is not possible to book an appointment prior to today.');
                                $('#calendar').fullCalendar('unselect');
                            }
                        } else {
                            alert("You can select maximum of 3 dates!");
                            $('#calendar').fullCalendar('unselect');
                        }
                    },
                    selectOverlap: function (event) {
                        if (event.ranges && event.ranges.length > 0) {
                            return (event.ranges.filter(function (range) {
                                return (event.start.isBefore(range.end) &&
                                        event.end.isAfter(range.start));
                            }).length) > 0;
                        } else {
                            return !!event && event.overlap;
                        }
                    },
                    eventResize: function (event, delta, revertFunc)
                    {
                        if ((event.end - event.start) <= '5400000') {
                            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                            var title = event.title;
                            var id = event.id;
                            $.ajax({
                                url: "update.php",
                                type: "POST",
                                data: {title: title, start: start, end: end, id: id},
                                success: function () {
                                    calendar.fullCalendar('refetchEvents');
                                    alert('Event Update');
                                }
                            });
                        } else {
                            revertFunc();
                            alert("Appointment could be of maximum 1:30 hrs.");
                        }
                    },

                    eventDrop: function (event)
                    {
                        var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                        var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                        var title = event.title;
                        var id = event.id;
                        $.ajax({
                            url: "update.php",
                            type: "POST",
                            data: {title: title, start: start, end: end, id: id},
                            success: function ()
                            {
                                calendar.fullCalendar('refetchEvents');
                                alert("Event Updated");
                            }
                        });
                    },

                    eventClick: function (event)
                    {
                        if (event.editable) {
                            if (confirm("Are you sure you want to remove it?"))
                            {
                                var id = event.id;
                                $.ajax({
                                    url: "delete.php",
                                    type: "POST",
                                    data: {id: id},
                                    success: function ()
                                    {
                                        calendar.fullCalendar('refetchEvents');
                                        alert("Event Removed");
                                        appointments--;
                                    }
                                })
                            }
                        }
                    },
                    dayRender: function (date, cell) {
                        var myDate = new Date();
                        myDate.setDate(myDate.getDate() - 1);
                        if (date < myDate) {
                            $(cell).addClass('disabled');
                        }
                    },
                    viewRender: function (view, element) {
                        // Drop the second param ('day') if you want to be more specific
                        if (moment().isAfter(view.intervalStart, 'day')) {
                            $('.fc-prev-button').addClass('fc-state-disabled');
                        } else {
                            $('.fc-prev-button').removeClass('fc-state-disabled');
                        }
                    }
                });
            });

        </script>
        <style>
            .fc-event-non-editable{
                background-image: linear-gradient(45deg, #ffffff 30.77%, #e0aaaa 30.77%, #e0aaaa 50%, #ffffff 50%, #ffffff 80.77%, #e0aaaa 80.77%, #e0aaaa 100%);
                background-size: 18.38px 18.38px;
                border: 1px solid #b78686;
            }
            .fc-event{
                background-color: #dbf3ff;
            }
            .fc-agendaWeek-view .fc-day.fc-past, .disabled{
                pointer-events: none;
                background: #eee;
            }
        </style>
    </head>
    <body>
        <input type="hidden" name="form_id" id ="form_id" />
        <br />
        <!--<h2 align="center"><a href="#">Jquery Fullcalandar Integration with PHP and Mysql</a></h2>-->
        <br />
        <div class="container" style="width:100%; height : 100%">
            <div id="calendar"></div>
        </div>
    </body>
</html>
