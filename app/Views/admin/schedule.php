<?php
$this->extend('layout/main');
$this->section('body');
?>

<style>
    #calendar {
        width: 100% !important;
    }

    .fc {
        width: 100% !important;
    }

    .fc-scrollgrid {
        width: 100% !important;
    }

    .card {
        overflow: hidden;
    }
</style>

<!-- CALENDAR CARD -->
<div class="card shadow-sm border-0 rounded-4 p-4 mt-4">
    
    <h1 class="fw-bold mb-3 text-primary">
        <i class="fa fa-calendar me-2"></i>
        Technician Schedule Calendar
    </h1>

    <form action="<?= base_url('schedule/import') ?>" method="post" enctype="multipart/form-data">
    
        <div class="mb-3">
            <label class="form-label fw-semibold">Upload Excel File</label>

            <div class="input-group">
                <span class="input-group-text bg-light">
                    <i class="fas fa-file-excel text-success"></i>
                </span>

                <input type="file"
                    name="excel_file"
                    class="form-control"
                    accept=".xlsx,.xls"
                    required>

                <button type="submit" class="btn btn-success fw-semibold">
                    <i class="bi bi-upload me-1"></i>
                    Import
                </button>
            </div>

            <div class="form-text">
                Accepted formats: .xlsx, .xls
            </div>
        </div>

    </form>
    
<div class="card-body p-0">
    <div class="table-responsive">
        <div id="calendar" class="w-100"></div>
    </div>
</div>
</div>


<?php if (session()->getFlashdata('success')): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Import Successful!',
        text: <?= json_encode(session()->getFlashdata('success')) ?>,
        confirmButtonColor: '#198754'
    });
});
</script>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'error',
        title: 'Import Failed!',
        text: <?= json_encode(session()->getFlashdata('error')) ?>,
        confirmButtonColor: '#dc3545'
    });
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    <?php
    $grouped = [];

    if (!empty($schedules)) {
        foreach ($schedules as $row) {

            $key = $row['schedule_date'] . '_' .
                   $row['start_time'] . '_' .
                   $row['end_time'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'date' => $row['schedule_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'techs' => []
                ];
            }

            $grouped[$key]['techs'][] = [
                'name' => $row['name'],
                'location' => $row['location']
            ];
        }
    }

    $events = [];

    foreach ($grouped as $g) {

        $color = '#6c757d';

        switch ($g['start_time']) {
            case '07:00:00': $color = '#0d6efd'; break;
            case '08:00:00': $color = '#198754'; break;
            case '15:00:00': $color = '#fd7e14'; break;
            case '23:00:00': $color = '#6f42c1'; break;
        }

        // Proper datetime handling
        $startDate = $g['date'];
        $endDate   = $g['date'];

        if ($g['end_time'] <= $g['start_time']) {
            $endDate = date('Y-m-d', strtotime($g['date'] . ' +1 day'));
        }

        $startDateTime = $startDate . 'T' . $g['start_time'];
        $endDateTime   = $endDate   . 'T' . $g['end_time'];

        $events[] = [
            'title' => date('g:i A', strtotime($g['start_time'])) . ' - ' .
                       date('g:i A', strtotime($g['end_time'])),
            'start' => $startDateTime,
            'end'   => $endDateTime,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'techs' => $g['techs']
            ]
        ];
    }
    ?>

    var scheduleEvents = <?= json_encode($events); ?>;

   var calendar = new FullCalendar.Calendar(calendarEl, {
    timeZone: 'Asia/Manila',
    initialView: 'dayGridMonth',

    // IMPORTANT: allow expansion
    height: 'auto',
    contentHeight: 'auto',

    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
    },

    slotMinTime: "06:00:00",
    slotMaxTime: "30:00:00",
    nextDayThreshold: "07:00:00",

    dayMaxEventRows: false,
    dayMaxEvents: false,
    expandRows: true,
    handleWindowResize: true,
    slotEventOverlap: false,
    eventOverlap: false,
    eventDisplay: 'block',

    events: scheduleEvents,

eventContent: function(info) {

    var wrapper = document.createElement('div');
    wrapper.style.whiteSpace = "normal";
    wrapper.style.fontSize = "12px";
    wrapper.style.lineHeight = "1.4";
    wrapper.style.padding = "6px";

    // SHIFT TIME (bold top line)
    var shiftTime = document.createElement('div');
    shiftTime.style.fontWeight = "700";
    shiftTime.style.marginBottom = "4px";
    shiftTime.innerText = info.event.title;
    wrapper.appendChild(shiftTime);

    // SHOW TECH LIST IN MONTH + WEEK
    info.event.extendedProps.techs.forEach(function(tech) {
        var nameLine = document.createElement('div');
        nameLine.style.fontSize = "11px";
        nameLine.innerHTML =
            tech.name +
            " <span style='opacity:.85;font-size:10px'>(" +
            tech.location + ")</span>";
        wrapper.appendChild(nameLine);
    });

    return { domNodes: [wrapper] };
},

    eventClick: function(info) {

        let techList = "";

        info.event.extendedProps.techs.forEach(function(tech){
            techList += `<div>${tech.name} <small>(${tech.location})</small></div>`;
        });

        Swal.fire({
            title: info.event.title,
            html: techList,
            icon: 'info',
            confirmButtonColor: '#0d6efd'
        });
    }

});
    calendar.render();
    setTimeout(function(){
    calendar.updateSize();
}, 300);
});
</script>

<?php $this->endSection(); ?>