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

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card p-3 shadow-sm" style="background-color: #1E90FF; color: #fff;">
                    <div class="d-flex align-items-center">
                        <div class="me-3" style="font-size: 2rem;">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                        <div>
                            <p class="mb-1" style="opacity: 0.8;">Average Completion Time</p>
                            <h5 id="avgCompletionTime" class="mb-0">Calculating...</h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3 shadow-sm" style="background-color: #FF6B6B; color: #fff;">
                    <div class="d-flex align-items-center">
                        <div class="me-3" style="font-size: 2rem;">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div>
                            <p class="mb-1" style="opacity: 0.8;">Most Frequent Trouble</p>
                            <h5 id="mostFrequentTrouble" class="mb-0">Calculating...</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                <input type="date" name="start_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">End Date & Time</label>
                <input type="date" name="end_date" class="form-control">
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
       let startDate = $('input[name="start_date"]').val();
        let endDate   = $('input[name="end_date"]').val();

        // Add default times
        if (startDate) startDate += ' 00:01:00'; // 12:01 AM
        if (endDate)   endDate   += ' 23:59:00'; // 11:59 PM


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
                let start = $('input[name="start_date"]').val();
                let end   = $('input[name="end_date"]').val();

                d.start_date = start ? start + ' 00:01:00' : '';
                d.end_date   = end   ? end   + ' 23:59:00' : '';

                d.name    = $('input[name="name"]').val();
                d.ts_type = $('#tsTypeSelect').val(); 
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
$(document).ready(function () {

    // ============================
    // FUNCTIONS FIRST
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

        let displayText = avgMinutes >= 60
            ? `${Math.floor(avgMinutes / 60)} hr${Math.floor(avgMinutes / 60) > 1 ? 's' : ''} ${avgMinutes % 60} min`
            : `${avgMinutes} min`;

        $('#avgCompletionTime').text(displayText);
    }

    function updateMostFrequentTrouble() {
        let troubleCount = {};

        table.rows({ search: 'applied' }).every(function () {
            const row = this.data();
            let desc = row.description;

            if (!desc) return;

            desc = desc.trim().toLowerCase();
            troubleCount[desc] = (troubleCount[desc] || 0) + 1;
        });

        let mostFrequent = '-';
        let maxCount = 0;

        for (let key in troubleCount) {
            if (troubleCount[key] > maxCount) {
                maxCount = troubleCount[key];
                mostFrequent = key;
            }
        }

        if (mostFrequent !== '-') {
            mostFrequent = mostFrequent.toUpperCase();
        }

        $('#mostFrequentTrouble').text(
            maxCount > 0 ? `${mostFrequent} (${maxCount})` : '-'
        );
    }


    // ============================
    // EVENTS AFTER INIT
    // ============================
    table.on('xhr.dt draw', function () {
        updateAverageCompletionTime();
        updateMostFrequentTrouble();
    });

});



    /* =====================================
       PRINT TABLE (ALL FILTERED DATA)
    ====================================== */
    $('#printTableBtn').click(function () {

        // Force sort by Time column (index 7) descending
        table.order([7, 'asc']).draw();

        // Get sorted data (not DOM nodes)
        let rows = table.rows({ search: 'applied' }).data();

        let html = `
            <h3 style="margin-bottom:20px;">Activity Logs</h3>
            <table border="1" cellspacing="0" cellpadding="8" width="100%">
                <thead>${$('#activityTable thead').html()}</thead>
                <tbody>
        `;

        rows.each(function(row, index){

            html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${row.name}</td>
                    <td>${row.location}</td>
                    <td>${row.description}</td>
                    <td>${row.status}</td>
                    <td>${row.personnel}</td>
                    <td>${row.personnel_name ?? '-'}</td>
                    <td>${new Date(row.time).toLocaleString()}</td>
                    <td>${new Date(row.completion_time).toLocaleString()}</td>
                    <td>${row.ts_type ?? '-'}</td>
                </tr>
            `;
        });

        html += "</tbody></table>";

        let printWindow = window.open('', '', 'height=900,width=1200');

        printWindow.document.write(`
            <html>
            <head>
                <title>Print Table</title>
                <style>
                    body { font-family: Arial, sans-serif; padding:20px; }
                    table { border-collapse: collapse; width:100%; }
                    th { background:#f2f2f2; }
                    th, td { border:1px solid #000; padding:8px; text-align:left; }
                </style>
            </head>
            <body>${html}</body>
            </html>
        `);

        printWindow.document.close();

        setTimeout(function(){
            printWindow.print();
        }, 300);
    });



    /* =====================================
       EXPORT EXCEL (ALL FILTERED DATA)
    ====================================== */
    $('#exportExcelBtn').click(function () {

        let rows = table.rows({ search: 'applied' }).nodes();
        let csv = [];

        let header = [];
        $('#activityTable thead th').each(function(){
            header.push('"' + $(this).text() + '"');
        });
        csv.push(header.join(','));

        $(rows).each(function(){
            let rowData = [];
            $(this).find('td').each(function(){
                rowData.push('"' + $(this).text().replace(/"/g, '""') + '"');
            });
            csv.push(rowData.join(','));
        });

        let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        let downloadLink = document.createElement("a");

        downloadLink.download = "Activity_Logs.csv";
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = "none";

        document.body.appendChild(downloadLink);
        downloadLink.click();
    });


    /* =====================================
       PRINT COUNT BASED ON CONCERN TYPE
    ====================================== */
$('#printCountBtn').click(function () {

    let counts = {};

    table.rows({ search: 'applied' }).every(function () {

        let row = this.data();
        let type = row.ts_type;

        // If empty, null, or blank → set as "No Type"
        if (!type || type.trim() === '') {
            type = 'No Type';
        }

        if (!counts[type]) counts[type] = 0;
        counts[type]++;
    });

    let sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);

    let output = `
        <h3>Concern Type Summary</h3>
        <table border="1" cellpadding="8" cellspacing="0" width="100%">
            <tr>
                <th>Concern Type</th>
                <th>Total</th>
            </tr>
    `;

    let grandTotal = 0;

    sorted.forEach(([type, total]) => {
        output += `
            <tr>
                <td>${type}</td>
                <td>${total}</td>
            </tr>
        `;
        grandTotal += total;
    });

    output += `
            <tr>
                <td><strong>Grand Total</strong></td>
                <td><strong>${grandTotal}</strong></td>
            </tr>
        </table>
    `;

    let printWindow = window.open('', '', 'height=600,width=800');

    printWindow.document.write(`
        <html>
        <head>
            <title>Concern Type Count</title>
            <style>
                body { font-family: Arial; padding:20px; }
                table { border-collapse: collapse; width:100%; }
                th { background:#f2f2f2; }
                th, td { padding:8px; text-align:left; }
            </style>
        </head>
        <body>${output}</body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
});
});
</script>

<?php $this->endSection(); ?>
