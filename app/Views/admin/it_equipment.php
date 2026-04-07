<?php
$this->extend('layout/main');
$this->section('body');
?>
<?php if(session()->getFlashdata('success')): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session()->getFlashdata('success'); ?>',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
</script>
<?php endif; ?>

<div class="mb-2">
    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
        Add Equipment
    </button>

    <!-- View All -->
    <!-- <a href="<?= base_url('equipment/form') ?>" target="_blank" class="btn btn-primary">
        View All as Form
    </a> -->

    <!-- View Filtered -->
    <button type="button" id="viewFilteredForm" class="btn btn-secondary me-2">
        View as Form
    </button>

    <!-- Trigger Button -->
    <button type="button" class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#importExcelModal">
        Import Excel
    </button>
    <button id="resetFilters" class="btn btn-secondary me-2">Reset</button>
    <!-- Filters -->
    <div class="mb-3 mt-3">
        <div class="row g-2">
            <div class="col-md-2">
                <input type="text" id="filterType" class="form-control form-control-xl" placeholder="Type">
            </div>
            <div class="col-md-2">
                <input type="text" id="filterModel" class="form-control form-control-xl" placeholder="Model">
            </div>
            <div class="col-md-2">
                <input type="text" id="filterArea" class="form-control form-control-xl" placeholder="Area">
            </div>
            <div class="col-md-2">
                <input type="date" id="filterAcquisition" class="form-control form-control-xl" placeholder="Acquisition Date">
            </div>
            <div class="col-md-2">
                <input type="date" id="filterLifeSpan" class="form-control form-control-xl" placeholder="Estimated Life Span">
            </div>
            <div class="col-md-2">
                    <select name="status" class="form-control" id="filterStatus">
                        <option value="" disabled selected>Select Status</option>
                        <option value="NEW">New</option>
                        <option value="OLD">Old</option>
                    </select>
            </div>
            <div class="col-md-2">
                
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header mb-3">
        <h5 class="mb-0">Equipment List</h5>
    </div>
    <div class="card-body p-3" style="position: relative;">
        <!-- Non-blocking loader overlay -->
        <div id="tableLoader" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background: rgba(255,255,255,0.6); z-index:10; text-align:center;">
            <div class="spinner-border text-primary mt-5" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table id="equipmentTable" class="table">
                <thead class="table-light">
                    <tr>
                        <th>Equipment Type</th>
                        <th>Model</th>
                        <th>Label(If any)</th>
                        <th>Accountable Area/Personnel</th>
                        <th>Description/Specification</th>
                        <th>Acquisition Date</th>
                        <th>Estimated Life Span</th>
                        <th>Remarks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>



<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= base_url('equipment/save'); ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addEquipmentModalLabel">Add Equipment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="" disabled selected>Select Type</option>
                        <option value="laptop">Laptop</option>
                        <option value="desktop">Desktop</option>
                        <option value="printer">Printer</option>
                        <option value="router">Router</option>
                        <option value="switch">Switch</option>
                        <option value="cable">Cable</option>
                    </select>
            </div>
            <div class="col-md-6">
              <label>Model</label>
              <input type="text" name="model" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Label (If any)</label>
              <input type="text" name="label" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Accountable Area/Personnel</label>
              <input type="text" name="AccountableArea" class="form-control">
            </div>
            <div class="col-md-12">
              <label>Description/Specification</label>
              <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
              <label>Acquisition Date</label>
              <input type="date" name="acquisitiondate" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Estimated Life Span</label>
              <input type="date" name="estimatedlife" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="" disabled selected>Select Status</option>
                        <option value="NEW">New</option>
                        <option value="OLD">Old</option>
                    </select>
            </div>
            <div class="col-md-6">
              <label>Quantity</label>
              <input type="text" name="quantity" class="form-control">
            </div>

            <div class="col-md-12">
              <label>Remarks</label>
              <textarea name="remarks" class="form-control"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Save Equipment</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editEquipmentForm">
        <div class="modal-header">
          <h5 class="modal-title">Edit Equipment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="editEquipmentId">
          <div class="row g-3">
            <div class="col-md-6">
              <label>Type</label>
              <select name="type" id="editType" class="form-control" required>
                  <option value="" disabled>Select Type</option>
                  <option value="laptop">Laptop</option>
                  <option value="desktop">Desktop</option>
                  <option value="printer">Printer</option>
                  <option value="router">Router</option>
                  <option value="switch">Switch</option>
                  <option value="cable">Cable</option>
              </select>
            </div>
            <div class="col-md-6">
              <label>Model</label>
              <input type="text" name="model" id="editModel" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label>Label</label>
              <input type="text" name="label" id="editLabel" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Accountable Area/Personnel</label>
              <input type="text" name="AccountableArea" id="editAccountableArea" class="form-control">
            </div>
            <div class="col-md-12">
              <label>Description</label>
              <textarea name="description" id="editDescription" class="form-control"></textarea>
            </div>
            <div class="col-md-6">
              <label>Acquisition Date</label>
              <input type="date" name="acquisitiondate" id="editAcquisitionDate" class="form-control">
            </div>
            <div class="col-md-6">
              <label>Estimated Life Span</label>
              <input type="date" name="estimatedlife" id="editEstimatedLife" class="form-control">
            </div>
            <div class="col-md-12">
              <label>Remarks</label>
              <textarea name="remarks" id="editRemarks" class="form-control"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Update Equipment</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Import Excel Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= base_url('equipment/importExcel'); ?>" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="importExcelModalLabel">Import Equipment from Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="excelFile" class="form-label">Select Excel File (.xlsx or .xls)</label>
            <input type="file" name="excelFile" id="excelFile" class="form-control" accept=".xlsx,.xls" required>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control" required>
              <option value="" disabled selected>Select Status</option>
              <option value="OLD">Old</option>
              <option value="NEW">New</option>
            </select>
          </div>

          <p class="text-muted">
            Excel Column Mapping:<br>
            <strong>A</strong> → Label<br>
            <strong>B</strong> → Model<br>
            <strong>C</strong> → Accountable Area<br>
            <strong>D</strong> → Description
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">Import Excel</button>
        </div>
      </form>
    </div>
  </div>
</div>




<script>
$(document).ready(function () {

    // SweetAlert loader only on first page load
    Swal.fire({
        title: 'Loading Equipment...',
        text: 'Please wait...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    let table = $('#equipmentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "<?= base_url('equipment/getData'); ?>",
            type: "GET",
            data: function(d) {
                d.type = $('#filterType').val();
                d.model = $('#filterModel').val();
                d.area = $('#filterArea').val();
                d.acquisitiondate = $('#filterAcquisition').val();
                d.estimatedlife = $('#filterLifeSpan').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
                { data: 'type' },
                { data: 'model' },
                { data: 'label' },
                { data: 'AccountableArea' },
                { data: 'description' },
                {
                    data: 'acquisitiondate',
                    render: function(data) {
                        if (!data || data === '0000-00-00') return '-';
                        let date = new Date(data);
                        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    }
                },
                {
                    data: 'estimatedlife',
                    render: function(data) {
                        if (!data || data === '0000-00-00') return '-';
                        let date = new Date(data);
                        return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                    }
                },
                { data: 'remarks' },
                {
                    data: 'id', // Actions
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-primary editBtn" data-id="${data}" data-bs-toggle="modal" data-bs-target="#editEquipmentModal">Edit</button>
                                <button class="btn btn-sm btn-danger deleteBtn" data-id="${data}">Delete</button>
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                }
            ],
        initComplete: function() {
            // Close SweetAlert after initial load
            Swal.close();
        }
    });

    // Overlay spinner for subsequent reloads
    table.on('preXhr.dt', function() { $('#tableLoader').show(); });
    table.on('xhr.dt', function() { $('#tableLoader').hide(); });

    // Filters
    $('#filterType, #filterModel, #filterArea, #filterAcquisition, #filterLifeSpan, #filterStatus').on('keyup change', function() {
        table.ajax.reload(null, false);
    });

    // Reset filters
    $('#resetFilters').click(function() {
        $('#filterType, #filterModel, #filterArea, #filterAcquisition, #filterLifeSpan, #filterStatus').val('');
        table.ajax.reload(null, false);
    });

    // Open filtered form in new tab
    $('#viewFilteredForm').click(function() {
        let params = $.param({
            type: $('#filterType').val(),
            model: $('#filterModel').val(),
            area: $('#filterArea').val(),
            acquisitiondate: $('#filterAcquisition').val(),
            estimatedlife: $('#filterLifeSpan').val(),
            status: $('#filterStatus').val()
        });
        window.open("<?= base_url('equipment/form'); ?>?" + params, "_blank");
    });

    // Edit button click
$('#equipmentTable').on('click', '.editBtn', function() {
    let id = $(this).data('id');
    $.get("<?= base_url('equipment/get'); ?>/" + id, function(data) {
        $('#editEquipmentId').val(data.id);
        $('#editType').val(data.type);
        $('#editModel').val(data.model);
        $('#editLabel').val(data.label);
        $('#editAccountableArea').val(data.AccountableArea);
        $('#editDescription').val(data.description);
        $('#editAcquisitionDate').val(data.acquisitiondate);
        $('#editEstimatedLife').val(data.estimatedlife);
        $('#editRemarks').val(data.remarks);
    }, 'json');
});

// Submit edit form
$('#editEquipmentForm').submit(function(e) {
    e.preventDefault();
    $.post("<?= base_url('equipment/update'); ?>", $(this).serialize(), function(response) {
        if(response.success){
            Swal.fire('Updated!', response.message, 'success');
            $('#editEquipmentModal').modal('hide');
            table.ajax.reload(null,false);
        } else {
            Swal.fire('Error!', response.message, 'error');
        }
    }, 'json');
});

// Delete button click
$('#equipmentTable').on('click', '.deleteBtn', function() {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Are you sure?',
        text: "This will mark the equipment as inactive!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post("<?= base_url('equipment/delete'); ?>", {id:id}, function(resp){
                if(resp.success){
                    Swal.fire('Deleted!', resp.message, 'success');
                    table.ajax.reload(null,false);
                } else {
                    Swal.fire('Error!', resp.message, 'error');
                }
            }, 'json');
        }
    });
});

});
</script>

<?php $this->endSection(); ?>