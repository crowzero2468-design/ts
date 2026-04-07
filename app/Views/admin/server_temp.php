<?php
$this->extend('layout/main');
$this->section('body');
?>

<div class="container mt-4">
    <h3>Server Room Temperature Monitoring</h3>

    <div class="row mb-3">
        <div class="col-md-3">
            <input type="date" id="filterDate" class="form-control" placeholder="Date">
        </div>
        <div class="col-md-3">
            <input type="text" id="filterMonitor" class="form-control" placeholder="Monitored By">
        </div>
        <div class="col-md-3">
            <input type="text" id="filterTemp" class="form-control" placeholder="Temperature">
        </div>
        <div class="col-md-3 d-flex flex-column gap-2">
            <button id="btnFilter" class="btn btn-secondary w-100">Filter</button>
            <button id="btnClearFilter" class="btn btn-secondary w-100">Clear Filter</button>
            <button id="btnAddTemp" class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#addTempModal">
                Add Temperature
            </button>
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

    function showLoading() {
        Swal.fire({
            title: 'Loading...',
            html: 'Please wait while data is being fetched.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });
    }

    function hideLoading() {
        Swal.close();
    }

    var table = $('#tempTable').DataTable({
        ajax: {
            url: "<?= base_url('temp/getData') ?>",
            data: function(d) {
                d.date = $('#filterDate').val();
                d.monitor_by = $('#filterMonitor').val();
                d.temp = $('#filterTemp').val();
            },
            beforeSend: function() {
                showLoading(); // Show SweetAlert loading before request
            },
            complete: function() {
                hideLoading(); // Close SweetAlert after request
            },
            error: function(xhr, status, error) {
                hideLoading();
                Swal.fire('Error', 'Failed to fetch data', 'error');
            }
        },
        columns: [
            { data: 'date' },
            { data: 'time' },
            { data: 'temp',  
                render: function(data, type, row) {
                    return data + ' ℃';}
            },
            { data: 'monitor_by' }
        ],
        order: [[0, 'desc'], [1, 'desc']]
    });

    $('#btnFilter').on('click', function() {
        table.ajax.reload();
    });


    // Add Temperature Form submit
    $('#addTempForm').on('submit', function(e) {
        e.preventDefault();

        let data = {
            datetime: $('#tempDate').val() + ' ' + $('#tempTime').val(),
            temp: $('#temperature').val(),
            monitor_by: $('#monitoredBy').val()
        };

        $.ajax({
            url: "<?= base_url('temp/add') ?>", // create this route in your controller
            type: "POST",
            data: data,
            success: function(res) {
                Swal.fire('Success', 'Temperature record added!', 'success');
                $('#addTempModal').modal('hide');
                $('#tempTable').DataTable().ajax.reload();
            },
            error: function(err) {
                Swal.fire('Error', 'Failed to add record', 'error');
            }
        });
    });

    $('#btnClearFilter').on('click', function() {
        // Clear all filter inputs
        $('#filterDate').val('');
        $('#filterMonitor').val('');
        $('#filterTemp').val('');

        // Reload the DataTable
        table.ajax.reload();
    });

});
</script>

<?php $this->endSection(); ?>