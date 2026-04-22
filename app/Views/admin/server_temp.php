<?php
$this->extend('layout/main');
$this->section('body');
?>

<div class="container mt-4">
    <h3 class="mb-3">Server Room Temperature Monitoring</h3>

    <div class="card shadow-sm">
        <div class="card-body">

            <!-- FILTERS -->
            <div class="row g-3 align-items-end mb-3">

                <div class="col-md-3">
                    <label class="form-label small text-muted">Date</label>
                    <input type="date" id="filterDate" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Monitored By</label>
                    <input type="text" id="filterMonitor" class="form-control" placeholder="Enter name">
                </div>

                <div class="col-md-3">
                    <label class="form-label small text-muted">Temperature</label>
                    <input type="text" id="filterTemp" class="form-control" placeholder="e.g. 22°C">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button id="btnFilter" class="btn btn-secondary w-100">Filter</button>
                    <button id="btnClearFilter" class="btn btn-secondary w-100">Clear</button>
                </div>

            </div>

            <!-- ACTIONS -->
            <div class="row g-3">

                <div class="col-md-6">
                    <button id="btnAddTemp" 
                            class="btn btn-secondary w-100"
                            data-bs-toggle="modal" 
                            data-bs-target="#addTempModal">
                        + Add Temperature
                    </button>
                </div>

                <div class="col-md-6">
                    <button id="btnTempPDF" class="btn btn-secondary w-100">
                        Generate PDF
                    </button>
                </div>

            </div>

        </div>
    </div>


    <table id="tempTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Temperature</th>
                <th>Monitored By</th>
            </tr>
        </thead>
    </table>
</div>



<!-- Add Temperature Modal -->
<div class="modal fade" id="addTempModal" tabindex="-1" aria-labelledby="addTempModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addTempForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addTempModalLabel">Add Temperature Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="tempDate" class="form-label">Date</label>
                <input type="date" class="form-control" id="tempDate" name="datetime" required>
            </div>
            <div class="mb-3">
                <label for="tempTime" class="form-label">Time</label>
                <input type="time" class="form-control" id="tempTime" name="time" required>
            </div>
            <div class="mb-3">
                <label for="temperature" class="form-label">Temperature</label>
                <input type="text" class="form-control" id="temperature" name="temp" placeholder="e.g., 37.5°C" required>
            </div>
            <div class="mb-3">
                <label for="monitoredBy" class="form-label">Monitored By</label>
                <input type="text" class="form-control" id="monitoredBy" name="monitor_by" value="<?= session()->get('name'); ?>" readonly>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
$(document).ready(function() {

document.getElementById('btnTempPDF').addEventListener('click', function() {
    // Optional filters
    let date = document.getElementById('filterDate')?.value || '';
    let monitor_by = document.getElementById('filterBy')?.value || '';
    let status = document.getElementById('filterStatus')?.value || '';

    let url = '<?= base_url("temperature/report") ?>';
    url += '?date=' + encodeURIComponent(date);
    url += '&monitor_by=' + encodeURIComponent(monitor_by);
    url += '&status=' + encodeURIComponent(status);

    window.open(url, '_blank'); // Open PDF in new tab
});

    var table = $('#tempTable').DataTable({
        ajax: {
            url: "<?= base_url('temp/getData') ?>",
            data: function(d) {
                d.date = $('#filterDate').val();
                d.monitor_by = $('#filterMonitor').val();
                d.temp = $('#filterTemp').val();
            },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading...',
                    html: 'Fetching data...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            complete: function() {
                Swal.close();
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to fetch data',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },
       columns: [
                {
                    data: 'date',
                    render: function(data, type, row) {
                        if (type === 'sort') {
                            return row.date_sort;
                        }
                        return data;
                    }
                },
                { data: 'time' },
                { data: 'temp', render: d => d + ' ℃' },
                { data: 'monitor_by' }
            ],
            order: [[0, 'desc']]
    });

    $('#btnFilter').on('click', function() {
        table.ajax.reload();
    });

    // Add Temperature Form submit
    $('#addTempForm').on('submit', function(e) {
        e.preventDefault();

        let formData = {
            datetime: $('#tempDate').val() + ' ' + $('#tempTime').val(),
            temp: $('#temperature').val(),
            monitor_by: $('#monitoredBy').val()
        };

        $.ajax({
            url: "<?= base_url('temp/add') ?>",
            type: "POST",
            data: formData,
            dataType: 'json',
            success: function(res) {
                if(res.status === 'success') {
                    $('#addTempModal').modal('hide'); // hide modal first
                    $('#addTempForm')[0].reset(); // reset form
                    table.ajax.reload(null, false); // reload table without resetting pagination

                    // Show SweetAlert after modal is hidden
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Added!',
                            text: res.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }, 500); // slight delay to ensure modal fully hides
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: res.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to add record',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    });

    $('#btnClearFilter').on('click', function() {
        $('#filterDate').val('');
        $('#filterMonitor').val('');
        $('#filterTemp').val('');
        table.ajax.reload();
    });

});
</script>

<?php $this->endSection(); ?>