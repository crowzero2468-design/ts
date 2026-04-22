<?php $this->extend('layout/main'); ?>
<?php $this->section('body'); ?>

<div class="container mt-4">
<h3>Server Inventory Management</h3>

<!-- FILTERS -->
<div class="row mb-3">

    <div class="col-md-3">
        <label for="startDate">Start Date</label>
        <input type="date" id="startDate" class="form-control" placeholder="Start Date">
    </div>

    <div class="col-md-3">
        <label for="endDate">End Date</label>
        <input type="date" id="endDate" class="form-control" placeholder="End Date">
    </div>

    <div class="col-md-3">
        <label for="filterAcquisition">Acquisition Date</label>
        <input type="text" id="filterType" class="form-control" placeholder="Type">
    </div>

    <div class="col-md-3">
        <label for="filterType">Type</label>
        <input type="text" id="filterModel" class="form-control" placeholder="Model">
    </div>

<div class="col-md-12 d-flex flex-column flex-md-row gap-2 mt-2">
    <button class="btn btn-secondary" id="filterBtn">Filter</button>
    <button class="btn btn-secondary" id="clearFilterBtn">Clear Filter</button>
    <button class="btn btn-secondary" id="addInventoryBtn">Add Server</button>
    <button class="btn btn-secondary" id="viewFormBtn">Generate PDF</button>
</div>

</div>

<!-- TABLE -->
<table id="inventoryTable" class="table table-striped table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Acquisition</th>
            <th>Type</th>
            <th>Model</th>
            <th>Processor</th>
            <th>Memory</th>
            <th>OS</th>
            <th>Server Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
</div>

<!-- ADD MODAL -->
<div class="modal fade" id="addInventoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Server Inventory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="inventoryForm">

          <div class="mb-3">
            <label>Acquisition Date</label>
            <input type="date" class="form-control" name="acquisition" required>
          </div>

          <div class="mb-3">
            <label>Type</label>
            <input type="text" class="form-control" name="type" required>
          </div>

          <div class="mb-3">
            <label>Model</label>
            <input type="text" class="form-control" name="model" required>
          </div>

          <div class="mb-3">
            <label>Processor</label>
            <input type="text" class="form-control" name="processor">
          </div>

          <div class="mb-3">
            <label>Memory</label>
            <input type="text" class="form-control" name="memory">
          </div>

          <div class="mb-3">
            <label>OS</label>
            <input type="text" class="form-control" name="OS">
          </div>

          <div class="mb-3">
            <label>Server Name</label>
            <input type="text" class="form-control" name="server_name" required>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" id="saveInventory" class="btn btn-success">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editInventoryModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Edit Server Inventory</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="editInventoryForm">
          <input type="hidden" name="id">

          <div class="mb-3">
            <label>Acquisition Date</label>
            <input type="date" class="form-control" name="acquisition" required>
          </div>

          <div class="mb-3">
            <label>Type</label>
            <input type="text" class="form-control" name="type" required>
          </div>

          <div class="mb-3">
            <label>Model</label>
            <input type="text" class="form-control" name="model" required>
          </div>

          <div class="mb-3">
            <label>Processor</label>
            <input type="text" class="form-control" name="processor">
          </div>

          <div class="mb-3">
            <label>Memory</label>
            <input type="text" class="form-control" name="memory">
          </div>

          <div class="mb-3">
            <label>OS</label>
            <input type="text" class="form-control" name="OS">
          </div>

          <div class="mb-3">
            <label>Server Name</label>
            <input type="text" class="form-control" name="server_name">
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button type="button" id="updateInventory" class="btn btn-success">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>

<script>
$(document).ready(function(){

    var table = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: "<?= base_url('serverinventory/fetchData') ?>",
            data: function(d){
                d.startDate = $('#startDate').val();
                d.endDate   = $('#endDate').val();
                d.acquisition = $('#filterAcquisition').val();
                d.type      = $('#filterType').val();
                d.model     = $('#filterModel').val();
            },
            beforeSend: function(){
                Swal.fire({
                    title: 'Loading...',
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false
                });
            },
            complete: function(){
                Swal.close();
            }
        },
        columns: [
            { data: 'acquisition' },
            { data: 'type' },
            { data: 'model' },
            { data: 'processor' },
            { data: 'memory' },
            { data: 'OS' },
            { data: 'server_name' },
            {
                data: null,
                orderable: false,
                render: function(data, type, row){
                    return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary editBtn" data-id="${row.id}">Edit</button>
                            <button class="btn btn-sm btn-danger deleteBtn" data-id="${row.id}">Delete</button>
                        </div>
                    `;
                }
            }
        ]
    });

    // FILTER
    $('#filterBtn').click(function(){
        table.ajax.reload();
    });

    // CLEAR FILTER
    $('#clearFilterBtn').click(function(){
        $('#startDate').val('');
        $('#endDate').val('');
        $('#filterAcquisition').val('');
        $('#filterType').val('');
        $('#filterModel').val('');
        table.ajax.reload();
    });

    // ADD
    $('#addInventoryBtn').click(function(){
        $('#inventoryForm')[0].reset();
        $('#addInventoryModal').modal('show');
    });

    // SAVE
$('#saveInventory').click(function () {

    let form = document.getElementById('inventoryForm');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    Swal.fire({
        title: 'Saving...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "<?= base_url('serverinventory/add') ?>",
        type: "POST",
        data: $('#inventoryForm').serialize(),
        dataType: "json",

        success: function (res) {
            console.log('Response:', res); // Debug log

            // ✅ Close the loading alert FIRST
            Swal.close();

            if (res.success) {
                // Close modal
                $('#addInventoryModal').modal('hide');

                // Show success alert
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: res.message,
                    timer: 2000,
                    showConfirmButton: false,
                    didClose: function(){
                        $('#inventoryForm')[0].reset();
                        $('#inventoryTable').DataTable().ajax.reload();
                    }
                });

            } else {
                Swal.fire('Error', res.message || 'Failed to save record', 'error');
            }
        },

        error: function (xhr) {
            console.log('Error:', xhr.responseText);
            // ✅ Close loading alert first
            Swal.close();
            
            Swal.fire('Error', 'Server error while saving data', 'error');
        }
    });
});

    // EDIT
    $(document).on('click', '.editBtn', function(){
        let id = $(this).data('id');

        $.get("<?= base_url('serverinventory/getEdit') ?>", {id:id}, function(res){

            if(!res.success){
                Swal.fire('Error', res.message, 'error');
                return;
            }

            let d = res.data;

            $('#editInventoryForm input[name="id"]').val(d.id);
            $('#editInventoryForm input[name="acquisition"]').val(d.acquisition);
            $('#editInventoryForm input[name="type"]').val(d.type);
            $('#editInventoryForm input[name="model"]').val(d.model);
            $('#editInventoryForm input[name="processor"]').val(d.processor);
            $('#editInventoryForm input[name="memory"]').val(d.memory);
            $('#editInventoryForm input[name="OS"]').val(d.OS);
            $('#editInventoryForm input[name="server_name"]').val(d.server_name);

            $('#editInventoryModal').modal('show');
        }, 'json');
    });

    // UPDATE
    $('#updateInventory').click(function(){
        $.post("<?= base_url('serverinventory/update') ?>", $('#editInventoryForm').serialize(), function(res){
            $('#editInventoryModal').modal('hide');
            table.ajax.reload();
            Swal.fire('Updated', res.message, 'success');
        });
    });

    // DELETE
    $(document).on('click', '.deleteBtn', function(){
        let id = $(this).data('id');

        Swal.fire({
            title: 'Delete?',
            icon: 'warning',
            showCancelButton: true
        }).then((r)=>{
            if(r.isConfirmed){
                $.post("<?= base_url('serverinventory/delete') ?>", {id:id}, function(res){
                    table.ajax.reload();
                    Swal.fire('Deleted', res.message, 'success');
                });
            }
        });
    });

    // ================= GENERATE PDF =================
    $('#viewFormBtn').click(function () {

        let startDate = $('#startDate').val();
        let endDate   = $('#endDate').val();
        let type      = $('#filterType').val();
        let model     = $('#filterModel').val();

        let url = "<?= base_url('serverinventory/viewForm') ?>";
        let params = [];

        if (startDate) params.push('startDate=' + encodeURIComponent(startDate));
        if (endDate)   params.push('endDate=' + encodeURIComponent(endDate));
        if (type)      params.push('type=' + encodeURIComponent(type));
        if (model)     params.push('model=' + encodeURIComponent(model));

        if (params.length > 0) {
            url += '?' + params.join('&');
        }

        // Optional loading alert
        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Open PDF
        setTimeout(() => {
            Swal.close();
            window.open(url, '_blank');
        }, 500);
    });
    

});
</script>

<?php $this->endSection(); ?>