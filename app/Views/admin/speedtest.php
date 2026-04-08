<?php $this->extend('layout/main'); ?>
<?php $this->section('body'); ?>

<div class="container mt-4">
<h3>Speedtest Monitoring</h3>

<div class="row mb-3">
    <div class="col-md-3">
        <input type="date" id="filterDate" class="form-control" placeholder="Date">
        <div class="card text-center shadow-sm p-3 mt-3">
            <small class="text-muted">Average Ping</small>
            <h4 id="avgPing" class="mb-0">0 ms</h4>
        </div>
    </div>
    <div class="col-md-3">
        <input type="text" id="filterNode" class="form-control" placeholder="Location / Node">
        <div class="card text-center shadow-sm p-3 mt-3">
            <small class="text-muted">Average Download</small>
            <h4 id="avgDownload" class="mb-0">0 Mbps</h4>
        </div>
    </div>
    <div class="col-md-3">
        <input type="text" id="filterBy" class="form-control" placeholder="Monitored By">
        <div class="card text-center shadow-sm p-3 mt-3">
            <small class="text-muted">Average Upload</small>
            <h4 id="avgUpload" class="mb-0">0 Mbps</h4>
        </div>
    </div>
    <div class="col-md-3 d-flex flex-column gap-2">

        <form action="<?= base_url('speedtest/importExcel') ?>" method="post" enctype="multipart/form-data">
            <input class="form-control form-control-xl mb-1" type="file" name="excel_file" required>
            <button type="submit" class="btn btn-secondary w-100">Import Excel</button>
        </form>
        <button class="btn btn-secondary w-100" id="filterBtn">Filter</button>
        <button class="btn btn-secondary w-100" id="clearFilterBtn">Clear Filter</button>
        <button class="btn btn-secondary w-100" id="addSpeedtestBtn">Add Speedtest</button>
        <button class="btn btn-secondary w-100" id="viewFormBtn">Generate PDF</button>
    </div>

</div>

    <table id="speedtestTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Provider</th>
                <th>Location</th>
                <th>Ping (ms)</th>
                <th>Download (Mbps)</th>
                <th>Upload (Mbps)</th>
                <th>Monitored By</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Add Speedtest Modal -->
<div class="modal fade" id="addSpeedtestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Speedtest</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="speedtestForm">
          <div class="mb-3">
            <label>Date & Time</label>
            <input type="datetime-local" class="form-control" name="datetime" required>
          </div>
          <div class="mb-3">
            <label>Node</label>
            <input type="text" class="form-control" name="node" required>
          </div>
          <div class="mb-3">
            <label>Location</label>
            <input type="text" class="form-control" name="location" required>
          </div>
          <div class="mb-3">
            <label>Ping (ms)</label>
            <input type="number" class="form-control" name="ping" required>
          </div>
          <div class="mb-3">
            <label>Download (Mbps)</label>
            <input type="number" class="form-control" name="download" required>
          </div>
          <div class="mb-3">
            <label>Upload (Mbps)</label>
            <input type="number" class="form-control" name="upload" required>
          </div>
          <div class="mb-3">
            <label>Monitored By</label>
            <input type="text" class="form-control" value="<?= session()->get('name'); ?>" name="checked_by" readonly>
          </div>
          <div class="mb-3">
            <label>Remarks</label>
            <input type="text" class="form-control" name="remarks">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveSpeedtest" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- Edit Speedtest Modal -->
<div class="modal fade" id="editSpeedtestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Speedtest</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editSpeedtestForm">
          <input type="hidden" name="id">
          <div class="mb-3">
            <label>Date & Time</label>
            <input type="datetime-local" class="form-control" name="datetime" required>
          </div>
          <div class="mb-3">
            <label>Node</label>
            <input type="text" class="form-control" name="node" required>
          </div>
          <div class="mb-3">
            <label>Location</label>
            <input type="text" class="form-control" name="location" required>
          </div>
          <div class="mb-3">
            <label>Ping (ms)</label>
            <input type="number" class="form-control" name="ping" required>
          </div>
          <div class="mb-3">
            <label>Download (Mbps)</label>
            <input type="number" class="form-control" name="download" required>
          </div>
          <div class="mb-3">
            <label>Upload (Mbps)</label>
            <input type="number" class="form-control" name="upload" required>
          </div>
          <div class="mb-3">
            <label>Monitored By</label>
            <input type="text" class="form-control" name="checked_by" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="updateSpeedtest" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>




<script>
$(document).ready(function(){

    // Clear filters
    $('#clearFilterBtn').click(function() {
        $('#filterDate').val('');
        $('#filterNode').val('');
        $('#filterBy').val('');
        table.ajax.reload();
    });

    var table = $('#speedtestTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('speedtest/fetchData') ?>",
            data: function(d){
                d.date = $('#filterDate').val();
                d.node = $('#filterNode').val();
                d.checked_by = $('#filterBy').val();
            },
            dataSrc: function(json) {
                    let totalPing = 0;
                    let totalDownload = 0;
                    let totalUpload = 0;
                    let count = json.data.length;

                    function formatNumber(num) {
                        // Round to 2 decimals
                        let rounded = Number(num.toFixed(2));
                        return rounded;
                    }

                    if (count > 0) {
                        json.data.forEach(row => {
                            totalPing += parseFloat(row.ping) || 0;
                            totalDownload += parseFloat(row.download) || 0;
                            totalUpload += parseFloat(row.upload) || 0;
                        });

                        $('#avgPing').text(formatNumber(totalPing / count) + ' ms');
                        $('#avgDownload').text(formatNumber(totalDownload / count) + ' Mbps');
                        $('#avgUpload').text(formatNumber(totalUpload / count) + ' Mbps');
                    } else {
                        $('#avgPing').text('0 ms');
                        $('#avgDownload').text('0 Mbps');
                        $('#avgUpload').text('0 Mbps');
                    }

                    return json.data;
                },
            beforeSend: function() {
                Swal.fire({
                    title: 'Loading data...',
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false
                });
            },
            complete: function() {
                Swal.close();
            }
        },
        columns: [
            { data: 'date' },
            { data: 'time' },
            { data: 'node' },
            { data: 'location' },
            { data: 'ping' },
            { data: 'download' },
            { data: 'upload' },
            { data: 'checked_by' },
            { data: 'remarks' },
            { data: null, orderable: false, render: function(data, type, row) {
                <?php if(session()->get('role') == 3): ?>
                return `
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}">Edit</button>
                        <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.id}">Delete</button>
                    </div>
                `;
                <?php else: ?>
                return '';
                <?php endif; ?>
            }}
        ],
        columnDefs: [
            {
                targets: [0,1],       // date and time columns
                render: function(data, type, row) {
                    if(type === 'sort') return row.datetime_raw; // use raw datetime for sorting
                    return data; // display formatted
                }
            }
        ],
        order: [[0, 'desc']]
    });

    // Reload table with SweetAlert when filter button clicked
    $('#filterBtn').click(function(){
        Swal.fire({
            title: 'Loading data...',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false
        });
        table.ajax.reload(function(){
            Swal.close();
        });
    });

    // Add speedtest button
    $('#addSpeedtestBtn').click(function(){
        $('#speedtestForm')[0].reset();
        $('#addSpeedtestModal').modal('show');
    });

    // Save speedtest with validation
    $('#saveSpeedtest').click(function(){
        let form = document.getElementById('speedtestForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "<?= base_url('speedtest/add') ?>",
            type: "POST",
            data: $('#speedtestForm').serialize(),
            success: function(res){
                $('#addSpeedtestModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: 'Speedtest successfully added',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => table.ajax.reload());
            },
            error: function(){
                Swal.fire('Error', 'Failed to save', 'error');
            }
        });
    });

    $('#viewFormBtn').click(function () {
        let date = $('#filterDate').val();
        let node = $('#filterNode').val();
        let checked_by = $('#filterBy').val();
        let url = "<?= base_url('speedtest/viewForm') ?>?" +
            "date=" + encodeURIComponent(date) +
            "&node=" + encodeURIComponent(node) +
            "&checked_by=" + encodeURIComponent(checked_by);
        window.open(url, '_blank');
    });

    // Edit button click
    $('#speedtestTable').on('click', '.editBtn', function(){
        let id = $(this).data('id');
        $.get("<?= base_url('speedtest/get') ?>/" + id, function(res){
            let form = $('#editSpeedtestForm');
            form.find('[name=id]').val(res.id);
            form.find('[name=datetime]').val(res.datetime_raw);
            form.find('[name=node]').val(res.node);
            form.find('[name=location]').val(res.location);
            form.find('[name=ping]').val(res.ping);
            form.find('[name=download]').val(res.download);
            form.find('[name=upload]').val(res.upload);
            form.find('[name=checked_by]').val(res.checked_by);

            $('#editSpeedtestModal').modal('show');
        });
    });

    // Update speedtest with validation
    $('#updateSpeedtest').click(function(){
        let form = document.getElementById('editSpeedtestForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.post("<?= base_url('speedtest/update') ?>", $('#editSpeedtestForm').serialize(), function(res){
            $('#editSpeedtestModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => table.ajax.reload());
        });
    });

    // Delete button click
    $('#speedtestTable').on('click', '.deleteBtn', function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This will mark it as inactive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if(result.isConfirmed){
                $.post("<?= base_url('speedtest/delete') ?>", {id: id}, function(res){
                    Swal.fire('Deleted!', '', 'success');
                    table.ajax.reload();
                });
            }
        });
    });

});
</script>

<?php $this->endSection(); ?>