<?php $this->extend('layout/main'); ?>
<?php $this->section('body'); ?>

<div class="container mt-4">

    <h3 class="mb-3">Repair Monitoring</h3>
<div class="card shadow-sm">
    <div class="card-body">

        <!-- FILTERS -->
        <div class="row g-3 mb-3">

            <!-- AREA -->
            <div class="col-md-4">
                <label class="form-label">Area</label>
                <select id="filterArea" class="form-control">
                    <option value="">All Area</option>
                    <?php foreach ($areas as $a): ?>
                    <option value="<?= esc($a->area) ?>">
                        <?= esc($a->area) ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>

            <!-- START MONTH -->
            <div class="col-md-4">
                <label class="form-label">Start Month</label>
                <input type="month" id="startMonth" class="form-control">
            </div>

            <!-- END MONTH -->
            <div class="col-md-4">
                <label class="form-label">End Month</label>
                <input type="month" id="endMonth" class="form-control">
            </div>

        </div>

        <!-- ACTIONS -->
        <div class="row g-3">

            <div class="col-md-6">
                <button class="btn btn-primary w-100"
                    data-bs-toggle="modal"
                    data-bs-target="#addModal">
                    Add Repair
                </button>
            </div>

            <div class="col-md-6">
                <a id="btnPDF" href='dca/pdf' class="btn btn-primary w-100"  target="_blank">
                    Generate PDF
                    </a>
            </div>

        </div>

    </div>
</div>

    <!-- DATA TABLE -->
    <table id="repairTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Repair Code</th>
                <th>Date</th>
                <th>Time</th>
                <th>Device</th>
                <th>Problem</th>
                <th>Comments</th>
                <th>Performed By</th>
                <th>Noted By</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>

</div>

<!-- MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="repairForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Repair</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-2">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Time</label>
                        <input type="time" name="time" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Device</label>
                        <select name="device_name" class="form-control" required>
                            <option value="">Select Device</option>
                            <?php foreach ($dev as $d): ?>
                                <option value="<?= $d->computerlabel ?>">
                                    <?= $d->computerlabel ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-2">
                        <label>Problem</label>
                        <textarea name="problem" class="form-control" required></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Comments</label>
                        <textarea name="comments" class="form-control"></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Noted By</label>
                        <input type="text" name="noted_by" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Save</button>
                </div>

            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form id="editForm">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Repair</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="id" id="edit_id">

                    <div class="mb-2">
                        <label>Date</label>
                        <input type="date" name="date" id="edit_date" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Time</label>
                        <input type="time" name="time" id="edit_time" class="form-control" required>
                    </div>

                    <div class="mb-2">
                        <label>Device</label>
                        <input name="device_name" id="edit_device" class="form-control" readonly>

                    </div>

                    <div class="mb-2">
                        <label>Problem</label>
                        <textarea name="problem" id="edit_problem" class="form-control" required></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Comments</label>
                        <textarea name="comments" id="edit_comments" class="form-control"></textarea>
                    </div>

                    <div class="mb-2">
                        <label>Noted By</label>
                        <input type="text" name="noted_by" id="edit_noted" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Update</button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
var table;
var addModal;
var editModal;

$(document).ready(function () {

    // =========================
    // MODALS
    // =========================
    var addModalEl = document.getElementById('addModal');
    var editModalEl = document.getElementById('editModal');

    addModal = addModalEl ? new bootstrap.Modal(addModalEl) : null;
    editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;

    // =========================
    // DATA TABLE
    // =========================
    table = $('#repairTable').DataTable({
        processing: true,
        serverSide: false,

        ajax: {
            url: "<?= base_url('dca/getData') ?>",
            type: "GET",
            dataSrc: 'data',
            data: function (d) {
                return $.extend({}, d, {
                    area: $('#filterArea').val(),
                    start: $('#startMonth').val(),
                    end: $('#endMonth').val()
                });
            }
        },

        columns: [
            { data: 'code' },
            { data: 'date' },
            { data: 'time' },
            { data: 'device' },
            { data: 'problem' },
            { data: 'recommendation' },
            { data: 'performedby' },
            { data: 'notedby' },
            {
                data: 'code',
                render: function (data, type, row) {
                    return `
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary btn-edit" data-id="${row.id}">Edit</button>
                        <button class="btn btn-sm btn-primary btn-delete" data-id="${row.id}">Delete</button>
                    </div>
                `;
                }
            }
        ],

        order: [[1, 'desc']]
    });

    // =========================
    // FILTERS
    // =========================
    $('#filterArea, #startMonth, #endMonth').on('change', function () {
        table.ajax.reload();
    });

    // =========================
    // SAVE
    // =========================
    $('#repairForm').on('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Saving...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "<?= base_url('dca/save') ?>",
            type: "POST",
            data: $(this).serialize(),
            dataType: "json",

            success: function (res) {

                Swal.close();

                if (res.status === 'success') {

                    $('#repairForm')[0].reset();
                    table.ajax.reload(null, false);
                    if (addModal) addModal.hide();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        timer: 2000,
                        showConfirmButton: false,
                        text: res.message
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message
                    });
                }
            },

            error: function () {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error while saving'
                });
            }
        });
    });

    // =========================
    // PDF GENERATE WITH FILTER
    // =========================
    $('#btnPDF').on('click', function (e) {
        e.preventDefault();

        let area = $('#filterArea').val();
        let start = $('#startMonth').val();
        let end = $('#endMonth').val();

        let url = "<?= base_url('dca/pdf') ?>";
        let params = [];

        if (area) {
            params.push('area=' + encodeURIComponent(area));
        }
        if (start) {
            params.push('start=' + encodeURIComponent(start));
        }
        if (end) {
            params.push('end=' + encodeURIComponent(end));
        }

        if (params.length) {
            url += '?' + params.join('&');
        }

        window.open(url, '_blank');
    });

});


// =========================
// DELETE
// =========================
$(document).on('click', '.btn-delete', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: "This will be deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {

        if (!result.isConfirmed) return;

        Swal.fire({
            title: 'Deleting...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "<?= base_url('dca/delete') ?>",
            type: "POST",
            data: { id: id },
            dataType: "json",

            success: function (res) {

                Swal.close();

                if (res.status === 'success') {

                    table.ajax.reload();

                    setTimeout(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Record deleted successfully',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }, 200);

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message
                    });
                }
            },

            error: function () {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Server error while deleting'
                });
            }
        });

    });

});


// =========================
// EDIT
// =========================
$(document).on('click', '.btn-edit', function () {

    let id = $(this).data('id');

    Swal.fire({
        title: 'Loading...',
        text: 'Fetching record',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "<?= base_url('dca/edit') ?>",
        type: "GET",
        data: { id: id },
        
        dataType: "json",

        success: function (res) {

            Swal.close();

            if (res.status === 'success') {
                
                
                let d = res.data;
                let dt = d.datetime.split(' ');

                $('#edit_id').val(d.id);
                $('#edit_date').val(dt[0]);
                $('#edit_time').val(dt[1]);
                $('#edit_device').val(d.device);
                $('#edit_problem').val(d.problem);
                $('#edit_comments').val(d.recommendation);
                $('#edit_noted').val(d.notedby);

                if (editModal) editModal.show();

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        },

        error: function () {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load repair data'
            });
        }
    });

});


// =========================
// UPDATE
// =========================
$('#editForm').on('submit', function (e) {
    e.preventDefault();

    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Record updated successfully',
        timer: 2000,
        showConfirmButton: false,
    });

    $.ajax({
        url: "<?= base_url('dca/update') ?>",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",

        success: function (res) {

            Swal.close();

            if (res.status === 'success') {

                if (editModal) editModal.hide();
                table.ajax.reload(null, false);

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Record updated successfully',
                    timer: 2000,
                    showConfirmButton: false,
                });

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: res.message
                });
            }
        },

        error: function () {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Server error while updating'
            });
        }
    });
});
</script>

<?php $this->endSection(); ?>