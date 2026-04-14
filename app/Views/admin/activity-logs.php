<?php
$this->extend('layout/main');
$this->section('body');
?>

<style>
.loader-content { text-align: center; }
.action-btn {
    min-width: 110px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
}
.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.card-header { border-bottom: 1px solid #f0f0f0; }
.card { border-radius: 12px; }
.table { font-size: 14px; }
</style>

<div class="container-fluid">
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <!-- Header Section -->
        <div class="mb-4">
            <h4 class="fw-bold mb-1">Troubleshoot Activity Logs</h4>
            <p class="text-muted mb-0">Filter and view all troubleshoot records</p>
        </div>

        <!-- Stats Section -->
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="card border-0 bg-light p-3">
                    <h6 class="text-uppercase text-muted mb-2">Average Completion Time</h6>
                    <h5 id="avgCompletionTime" class="fw-bold mb-0">Calculating...</h5>
                </div>
            </div>
            <!-- You can add more stats cards here if needed -->
        </div>
    </div>
</div>

    <!-- FILTER CARD -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">
        Filter Troubleshoot Records
    </div>

    <div class="card-body">
        <form id="filterForm" class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter name...">
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local" name="start_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local" name="end_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Concern Type</label>
                <select name="ts_type" id="tsTypeSelect" class="form-select">
                    <option value="">All Concern Types</option>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= esc($t['tstype']) ?>">
                            <?= esc($t['tstype']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <button type="button" id="filterBtn" class="btn btn-secondary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>

            <div class="col-md-3">
                <button type="button" id="clearFilterBtn" class="btn btn-secondary w-100">
                    <i class="fas fa-eraser me-1"></i> Clear
                </button>
            </div>
            <?php if (session()->get('role') == 3): ?>
                <div class="col-md-3">
                    <button type="button" id="viewFormBtn" class="btn btn-secondary w-100">
                        <i class="fas fa-file-alt me-1"></i> Generate PDF
                    </button>
                </div>
            <?php endif; ?>

        </form>
    </div>
</div>

    <!-- DATA TABLE -->
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-semibold">
            All Troubleshoot Records
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="activityTable" class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Response Input by</th>
                            <th>Name of Personnel</th>
                            <th>Date and Time Called</th>
                            <th>Completion Date and Time</th>
                            <th>TS Type</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div class="card-header bg-white d-flex justify-content-end gap-2">
            <button id="printTableBtn" class="btn btn-outline-primary btn-sm action-btn">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button id="exportExcelBtn" class="btn btn-outline-success btn-sm action-btn">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
            <button id="printCountBtn" class="btn btn-outline-dark btn-sm action-btn">
                <i class="fas fa-chart-bar me-1"></i> Summary
            </button>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

    $('#viewFormBtn').click(function () {
        const startDate = $('input[name="start_date"]').val();
        const endDate   = $('input[name="end_date"]').val();
        const name      = $('input[name="name"]').val() || '';
        const tsType    = $('#tsTypeSelect').val() || '';

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select both start and end date.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const start = new Date(startDate);
        const end   = new Date(endDate);
        const diffMonths = (end - start) / (1000 * 60 * 60 * 24 * 30);

        if (diffMonths > 6) {
            Swal.fire({
                icon: 'error',
                title: 'Date Range Too Large',
                text: 'Date range should not exceed 6 months.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        const params = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            name: name,
            ts_type: tsType
        });

        const url = "<?= base_url('tshistory/printForm') ?>?" + params.toString();
        window.open(url, '_blank');
    });

    $('#clearFilterBtn').click(function () {
        $('#filterForm')[0].reset();
        $('#tsTypeSelect').val('');
        table.ajax.reload();
    });

    let table = $('#activityTable')
    .on('processing.dt', function (e, settings, processing) {
        if (processing) {
            Swal.fire({
                title: 'Loading Records...',
                text: 'Please wait...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        } else {
            Swal.close();
        }
    })
    .DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        responsive: true,
        processing: true,
        ajax: {
            url: "<?= base_url('tshistory/getData') ?>",
            type: "GET",
            data: function (d) {
                d.start_date = $('input[name="start_date"]').val();
                d.end_date   = $('input[name="end_date"]').val();
                d.name       = $('input[name="name"]').val();
                d.ts_type    = $('#tsTypeSelect').val(); 
            }
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: 'name' },
            { data: 'location' },
            { data: 'description' },
            { data: 'status', render: data => data === 'Done' ? 
                '<span class="badge bg-success">Done</span>' : 
                `<span class="badge bg-warning text-dark">${data}</span>` 
            },
            { data: 'personnel' },
            { data: 'personnel_name', defaultContent: '-' },
            {
                data: 'time',
               render: data => {
                if (!data) return '-';

                const d = new Date(data.replace(' ', 'T'));

                return isNaN(d)
                    ? '-'
                    : d.toLocaleString('en-US', {
                        timeZone: 'Asia/Manila',
                        year: 'numeric',
                        month: 'short',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        
                        hour12: true
                    });
            },
                defaultContent: '-'
            },
           {
                data: 'completion_time',
                render: data => {
                    if (!data) return '-';

                    const d = new Date(data.replace(' ', 'T'));

                    return isNaN(d)
                        ? '-'
                        : d.toLocaleString('en-US', {
                            timeZone: 'Asia/Manila',
                            year: 'numeric',
                            month: 'short',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            
                            hour12: true
                        });
                },
                defaultContent: '-'
            },
            { data: 'ts_type', defaultContent: '-' }
        ]
    });

    $('#activityTable')
    .on('preXhr.dt', function () {
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
    })
    .on('xhr.dt', function () {
        Swal.close();
    });

    $('#filterBtn').click(function () {
        table.ajax.reload();
    });

    // ============================
    // Average Completion Time
    // ============================
function updateAverageCompletionTime() {
    let totalMinutes = 0;
    let validRows = 0;

    table.rows({ search: 'applied' }).every(function () {
        const row = this.data();

        const startTimeStr = row.time_started || row.time;
        const endTimeStr   = row.completion_time;

        if (!startTimeStr || !endTimeStr) return;

        const start = new Date(startTimeStr + ' UTC');
        const end   = new Date(endTimeStr + ' UTC');

        if (isNaN(start) || isNaN(end) || end < start) return;

        const durationMinutes = (end - start) / (1000 * 60);
        totalMinutes += durationMinutes;
        validRows++;
    });

    const avgMinutes = validRows ? Math.round(totalMinutes / validRows) : 0;

    // ✅ Convert to hours + minutes if >= 60
    let displayText = '';

    if (avgMinutes >= 60) {
        const hours = Math.floor(avgMinutes / 60);
        const minutes = avgMinutes % 60;

        displayText = `${hours} hr${hours > 1 ? 's' : ''}` +
                      (minutes ? ` ${minutes} min` : '');
    } else {
        displayText = `${avgMinutes} min`;
    }

    $('#avgCompletionTime').text(displayText);
}

// Update after table loads or redraws
table.on('xhr.dt draw', updateAverageCompletionTime);
});
</script>

<?php $this->endSection(); ?>
