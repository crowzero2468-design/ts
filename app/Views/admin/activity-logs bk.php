<?php
$this->extend('layout/main');
$this->section('body');
?>

<!-- LOADING OVERLAY -->
<!-- <div id="pageLoader">
    <div class="loader-content">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-3 fw-semibold">Loading records...</div>
    </div>
</div> -->

<style>


#pageLoader {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.95);
    z-index: 9999;

    display: flex;
    justify-content: center;
    align-items: center;
}

.loader-content {
    text-align: center;
}

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

.card-header {
    border-bottom: 1px solid #f0f0f0;
}

.card {
    border-radius: 12px;
}

.table {
    font-size: 14px;
}

body.loading {
    overflow: hidden;
    position: fixed;
    width: 100%;
}

</style>

<div class="container-fluid">

    <!-- PAGE TITLE -->
    <div class="mb-4">
        <h4 class="fw-bold">Troubleshoot Activity Logs</h4>
        <small class="text-muted">Filter and view all troubleshoot records</small>
    </div>

            <!-- <div class="card shadow-sm mb-4">
            

            </div> -->


    </div>

    <div id="pageLoader">
    <div class="loader-content">
        <div class="spinner-border text-primary" role="status"></div>
        <div class="mt-3 fw-semibold">Loading records...</div>
    </div>
</div> 
    <!-- FILTER CARD -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light fw-semibold">
        Filter by Date, Time & Name
    </div>

    <div class="card-body">
        <form method="get" action="<?= site_url('actlog') ?>" class="row g-3">

            <div class="col-md-3">
                <label class="form-label">Name</label>
                <input type="text"
                       name="name"
                       class="form-control"
                       placeholder="Enter name..."
                       value="<?= esc($_GET['name'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Start Date & Time</label>
                <input type="datetime-local"
                       name="start_date"
                       class="form-control"
                       value="<?= esc($_GET['start_date'] ?? '') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">End Date & Time</label>
                <input type="datetime-local"
                       name="end_date"
                       class="form-control"
                       value="<?= esc($_GET['end_date'] ?? '') ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>

        </form>
    </div>
</div>


    <!-- DATA TABLE CARD -->
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
                            <th>Time</th>
                            <th>TS Type</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if(empty($records)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No records found
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($records as $i => $row): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= esc($row['name']) ?></td>
                                <td><?= esc($row['location']) ?></td>
                                <td><?= esc($row['description']) ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'Done' ? 'bg-success' : 'bg-warning text-dark' ?>">
                                        <?= esc($row['status']) ?>
                                    </span>
                                </td>
                                <td><?= esc($row['personnel']) ?></td>
                                <td><?= esc($row['personnel_name'] ?? '-') ?></td>
                                <td><?= date('m/d/Y - h:i A', strtotime($row['time'])) ?></td>
                                <td><?= esc($row['ts_type'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
<!-- 
                <div>
                    <h6 class="mb-0 fw-bold">All Troubleshoot Records</h6>
                    <small class="text-muted">Manage and export troubleshoot logs</small>
                </div> -->

                <div class="d-flex gap-2">
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


<script>
$(document).ready(function () {

    // Disable scroll immediately
    $('body').addClass('loading');

    let table = $('#activityTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        responsive: true,
        processing: true,
        initComplete: function () {

            $('#pageLoader').fadeOut(300, function () {
                $('body').removeClass('loading');
            });

        }
    });

    // Show loader when filter submits
    $('form').on('submit', function(){
        $('body').addClass('loading');
        $('#pageLoader').fadeIn(200);
    });


    /* =====================================
       PRINT TABLE (ALL FILTERED DATA)
    ====================================== */
    $('#printTableBtn').click(function () {

        let rows = table.rows({ search: 'applied' }).nodes();

        let html = `
            <h4>Activity Logs</h4>
            <table class="table table-bordered table-striped">
                <thead>${$('#activityTable thead').html()}</thead>
                <tbody>
        `;

        $(rows).each(function(){
            html += "<tr>" + $(this).html() + "</tr>";
        });

        html += "</tbody></table>";

        let printWindow = window.open('', '', 'height=900,width=1200');
        printWindow.document.write(`
            <html>
            <head>
                <title>Print Table</title>
                <link rel="stylesheet" href="<?= base_url("assets/css/bootstrap.min.css"); ?>">
            </head>
            <body>${html}</body>
            </html>
        `);

        printWindow.document.close();
        printWindow.print();
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

        let TYPE_COLUMN_INDEX = 8;
        let counts = {};

        table.rows({ search: 'applied' }).every(function () {

            let data = this.data();
            let tempDiv = $('<div>').html(data[TYPE_COLUMN_INDEX]);
            let type = tempDiv.text().trim();

            if (type === '') return;

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
            <body>
                ${output}
            </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.print();
    });

});
</script>




<?php $this->endSection(); ?>
