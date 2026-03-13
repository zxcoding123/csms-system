<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .meeting-day {
            background-color: rgba(40, 167, 69, 0.5) !important;
            /* Light green background */
        }
    </style>
</head>

<body>
    <div id="calendar"></div>

    <!-- Add this modal to your HTML for creating a new class meeting -->
    <div class="modal fade" id="createClassModal" tabindex="-1" aria-labelledby="createClassModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createClassModalLabel">Create Class Meeting on <span id="createModalDate"></span></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createClassForm">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="startTime" class="form-label">Start Time</label>
                            <input type="time" class="form-control" id="startTime" required>
                        </div>
                        <div class="mb-3">
                            <label for="endTime" class="form-label">End Time</label>
                            <input type="time" class="form-control" id="endTime" required>
                        </div>
                        <input type="hidden" id="classId" value="42"> <!-- Set this dynamically as needed -->
                        <button type="submit" class="btn btn-primary">Create Meeting</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function createClassMeeting(date) {
            $('#createModalDate').text(date); // Set the date in the modal
            $('#createClassForm')[0].reset(); // Reset the form
            $('#createClassModal').modal('show'); // Show the modal

            $('#createClassForm').off('submit').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                const subject = $('#subject').val();
                const startTime = $('#startTime').val();
                const endTime = $('#endTime').val();
                const classId = $('#classId').val();

                // Create an object to send to the server
                const meetingData = {
                    date: date,
                    subject: subject,
                    start_time: startTime,
                    end_time: endTime,
                    class_id: classId
                };

                // Send the data to the server
                fetch('create_class_meeting.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(meetingData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Handle success (e.g., refresh the calendar)
                            alert('Class meeting created successfully!');
                            $('#createClassModal').modal('hide'); // Close the modal
                            // Optionally refresh the calendar or update UI
                        } else {
                            // Handle error
                            alert('Error creating class meeting: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error creating class meeting.');
                    });
            });
        }
    </script>


    <div class="modal fade" id="classModal" tabindex="-1" aria-labelledby="classModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="classModalLabel">Classes on <span id="modalDate"></span></h5>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="classDetails"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var semesterId = 18; // Set semester dynamically if needed
            var classId = 42; // Set class ID dynamically if needed

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek'
                },
                events: `fetch_schedule_events.php?semester_id=${semesterId}&class_id=${classId}`,
                dateClick: function(info) {
                    Swal.fire({
                        title: `Actions for ${info.dateStr}`,
                        text: "Do you want to create a new class or view existing classes?",
                        icon: 'question',
                        showDenyButton: true,
                        confirmButtonText: 'Create New Class',
                        denyButtonText: 'View Classes'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            createClassMeeting(info.dateStr);
                        } else if (result.isDenied) {
                            viewClasses(info.dateStr, classId); // Pass the classId
                        }
                    });
                },
                datesSet: function(dateInfo) {
                    console.log("Fetching events...");
                    fetch(`fetch_schedule_events.php?semester_id=${semesterId}&class_id=${classId}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log("Event data:", data);
                            const meetingDays = data.meetingDays;

                            // Clear previous highlights
                            calendarEl.querySelectorAll('.fc-day').forEach(day => {
                                day.classList.remove('meeting-day');
                            });

                            meetingDays.forEach(meetingDay => {
                                const dayCell = calendarEl.querySelector(`.fc-day[data-date="${meetingDay}"]`);
                                if (dayCell) {
                                    dayCell.classList.add('meeting-day');
                                }
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                        });
                }
            });

            calendar.render();
        });

        function createClassMeeting(date) {
            $('#createModalDate').text(date); // Set the date in the modal
            $('#createClassForm')[0].reset(); // Reset the form
            $('#createClassModal').modal('show'); // Show the modal

            $('#createClassForm').off('submit').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                const subject = $('#subject').val();
                const startTime = $('#startTime').val();
                const endTime = $('#endTime').val();
                const classId = $('#classId').val();

                // Create an object to send to the server
                const meetingData = {
                    date: date,
                    subject: subject,
                    start_time: startTime,
                    end_time: endTime,
                    class_id: classId
                };

                // Send the data to the server
                fetch('create_class_meeting.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(meetingData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Handle success (e.g., refresh the calendar)
                            alert('Class meeting created successfully!');
                            $('#createClassModal').modal('hide'); // Close the modal
                            // Optionally refresh the calendar or update UI
                        } else {
                            // Handle error
                            alert('Error creating class meeting: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error creating class meeting.');
                    });
            });
        }

        function viewClasses(date, classId) { // Accept classId as a parameter
            $('#modalDate').text(date); // Set the date in the modal
            $('#classDetails').html('Loading...'); // Show loading message

            // Fetch class details for the selected date and class ID
            fetch(`fetch_class_details.php?date=${date}&class_id=${classId}`)
                .then(response => response.json())
                .then(data => {
                    let detailsHtml = '<ul>';
                    if (data.classes && data.classes.length > 0) {
                        data.classes.forEach(cls => {
                            detailsHtml += `<li>${cls.subject_name}: ${cls.start_time} - ${cls.end_time}</li>`;
                        });
                    } else {
                        detailsHtml += '<li>No classes found for this date.</li>';
                    }
                    detailsHtml += '</ul>';
                    $('#classDetails').html(detailsHtml); // Populate the modal body
                    $('#classModal').modal('show'); // Show the modal
                })
                .catch(error => {
                    console.error('Error fetching class details:', error);
                    $('#classDetails').html('<p>aa Error loading class details.</p>');
                    $('#classModal').modal('show'); // Show the modal even if there's an error
                });
        }
    </script>

</body>

</html>