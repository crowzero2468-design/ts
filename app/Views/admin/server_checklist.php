<?php $this->extend('layout/main'); ?>
<?php $this->section('body'); ?>

<div class="container mt-4">
<h3>Server Checklist Monitoring</h3>

<div class="row mb-3">
    <div class="col-md-3">
        <label for="filterStartDate">Start Date</label>
        <input type="date" id="filterStartDate" class="form-control" placeholder="Start Date">
    </div>

    <div class="col-md-3">
        <label for="filterEndDate">End Date</label>
        <input type="date" id="filterEndDate" class="form-control" placeholder="End Date">
    </div>

    <div class="col-md-3">
        <label for="filterServer">Server Name</label>
        <input type="text" id="filterServer" class="form-control" placeholder="Server Name">
    </div>

    <div class="col-md-3">
        <label for="filterBy">Checked By</label>
        <input type="text" id="filterBy" class="form-control" placeholder="Checked By">
    </div>
<div class="col-md-12 d-flex flex-wrap gap-2 mt-2">
    <button class="btn btn-secondary" id="filterBtn">Filter</button>
    <button class="btn btn-secondary" id="clearFilterBtn">Clear Filter</button>
    <button class="btn btn-secondary" id="addServerChecklistBtn">Add Checklist</button>
    <button class="btn btn-secondary" id="viewFormBtn">Generate PDF</button>
</div>
</div>

    <table id="serverchecklistTable" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Time</th>
                <th>Server Name</th>
                <th>Checkpoint</th>
                <th>Problem</th>
                <th>Corrective</th>
                <th>Checked By</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- Add Server Checklist Modal -->
<div class="modal fade" id="addServerChecklistModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Server Checklist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="serverchecklistForm">
          <div class="mb-3">
            <label>Date & Time</label>
            <input type="datetime-local" class="form-control" name="datetime" required>
          </div>
          <div class="mb-3">
            <label>Server Name</label>
            <input type="text" class="form-control" name="servername" required>
          </div>
          <div class="mb-3">
            <label>Checkpoint</label>
            <input type="text" class="form-control" name="checkpoint" required>
          </div>
          <div class="mb-3">
            <label>Problem</label>
            <textarea class="form-control" name="problem" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label>Corrective Action</label>
            <textarea class="form-control" name="corrective" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label>Checked By</label>
            <input type="text" class="form-control" value="<?= session()->get('name'); ?>" name="checked_by" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="saveServerChecklist" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Server Checklist Modal -->
<div class="modal fade" id="editServerChecklistModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Server Checklist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editServerChecklistForm">
          <input type="hidden" name="id">
          <div class="mb-3">
            <label>Date & Time</label>
            <input type="datetime-local" class="form-control" name="datetime" required>
          </div>
          <div class="mb-3">
            <label>Server Name</label>
            <input type="text" class="form-control" name="servername" required>
          </div>
          <div class="mb-3">
            <label>Checkpoint</label>
            <input type="text" class="form-control" name="checkpoint" required>
          </div>
          <div class="mb-3">
            <label>Problem</label>
            <textarea class="form-control" name="problem" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label>Corrective Action</label>
            <textarea class="form-control" name="corrective" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label>Checked By</label>
            <input type="text" class="form-control" name="checked_by" readonly>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" id="updateServerChecklist" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){

    // Clear filters
    $('#clearFilterBtn').click(function() {
        $('#filterStartDate').val('');
        $('#filterEndDate').val('');
        $('#filterServer').val('');
        $('#filterBy').val('');
        table.ajax.reload();
    });

    var table = $('#serverchecklistTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('serverchecklist/fetchData') ?>",
            data: function(d){
                d.start_date = $('#filterStartDate').val();
                d.end_date = $('#filterEndDate').val();
                d.servername = $('#filterServer').val();
                d.checked_by = $('#filterBy').val();
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
            { data: 'servername' },
            { data: 'checkpoint' },
            { data: 'problem' },
            { data: 'corrective' },
            { data: 'checked_by' },
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
        order: [[0, 'desc']]
    });

    // Reload table with filter
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

    // Generate PDF
    $('#viewFormBtn').click(function(){
        let start_date = $('#filterStartDate').val();
        let end_date = $('#filterEndDate').val();
        let servername = $('#filterServer').val();
        let checked_by = $('#filterBy').val();

        let url = "<?= base_url('serverchecklist/viewForm') ?>";
        let params = [];

        if (start_date) params.push('start_date=' + encodeURIComponent(start_date));
        if (end_date) params.push('end_date=' + encodeURIComponent(end_date));
        if (servername) params.push('servername=' + encodeURIComponent(servername));
        if (checked_by) params.push('checked_by=' + encodeURIComponent(checked_by));

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        window.open(url, '_blank');
    });

    // Add server checklist button
    $('#addServerChecklistBtn').click(function(){
        $('#serverchecklistForm')[0].reset();
        $('#addServerChecklistModal').modal('show');
    });

    // Save server checklist
    $('#saveServerChecklist').click(function(){
        let form = document.getElementById('serverchecklistForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "<?= base_url('serverchecklist/add') ?>",
            type: "POST",
            data: $('#serverchecklistForm').serialize(),
            success: function(res){
                if (res.success) {
                    $('#addServerChecklistModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: 'Server checklist successfully added',
                        timer: 2000,
                        showConfirmButton: false,
                        didClose: function(){
                            table.ajax.reload();
                        }
                    });
                } else {
                    Swal.fire('Error!', res.message || 'Failed to save record', 'error');
                }
            },
            error: function(e){
                Swal.fire('Error!', 'Failed to save record', 'error');
            }
        });
    });

    // Edit button
    $(document).on('click', '.editBtn', function(){
        let id = $(this).data('id');

        $.ajax({
            url: "<?= base_url('serverchecklist/getEdit') ?>",
            type: "GET",
            dataType: "json",
            data: {id: id},
            success: function(res){
                if (!res.success) {
                    Swal.fire('Error', res.message || 'Failed to fetch data', 'error');
                    return;
                }

                let row = res.data;

                $('#editServerChecklistForm input[name="id"]').val(row.id);
                $('#editServerChecklistForm input[name="datetime"]').val(row.datetime_local);
                $('#editServerChecklistForm input[name="servername"]').val(row.servername);
                $('#editServerChecklistForm input[name="checkpoint"]').val(row.checkpoint);
                $('#editServerChecklistForm textarea[name="problem"]').val(row.problem);
                $('#editServerChecklistForm textarea[name="corrective"]').val(row.corrective);
                $('#editServerChecklistForm input[name="checked_by"]').val(row.checked_by);

                $('#editServerChecklistModal').modal('show');
            },
            error: function(xhr){
                console.log(xhr.responseText);
                Swal.fire('Error', 'Failed to fetch data', 'error');
            }
        });
    });

    // Update
    $('#updateServerChecklist').click(function(){
        let form = document.getElementById('editServerChecklistForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "<?= base_url('serverchecklist/update') ?>",
            type: "POST",
            data: $('#editServerChecklistForm').serialize(),
            success: function(res){
                $('#editServerChecklistModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Server checklist successfully updated',
                    timer: 2000,
                    didClose: function(){
                        table.ajax.reload();
                    }
                });
            },
            error: function(e){
                Swal.fire('Error!', 'Failed to update record', 'error');
            }
        });
    });

    // Delete
    $(document).on('click', '.deleteBtn', function(){
        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete Record?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?= base_url('serverchecklist/delete') ?>",
                    type: "POST",
                    data: {id: id},
                    success: function(res){
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Record successfully deleted',
                            showConfirmButton: false,
                            timer: 2000,
                            didClose: function(){
                                table.ajax.reload();
                            }
                        });
                    },
                    error: function(e){
                        Swal.fire('Error!', 'Failed to delete record', 'error');
                    }
                });
            }
        });
    });

});
</script>
<?php $this->endSection(); ?>