<?php
$this->extend('layout/main');
$this->section('body');
?>


<style>
   .fc-daygrid-event {
    white-space: normal !important;
}

.fc-daygrid-day-frame {
    overflow: visible !important;
}
</style>

<div class="container-fluid">

    <!-- DATA TABLE CARD -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light fw-semibold">
                <h1 class="fw-bold mb-3 text-primary">
                <i class="fa fa-gear me-2"></i>
                    CVMC IT Technicians
                </h1>
        </div>

        <div class="card-body">

            <!-- <div class="table-responsive">
                <table id="techtable" class="table table-striped table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Action</th>
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
                                <td>
                                    <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-danger text-dark' ?>">
                                        <?= esc($row['status']) ?>
                                    </span>
                                </td>
                                
                    <td class="text-center">
                        <?php if (session()->get('role') == 3): ?>

                            <div class="d-flex justify-content-center gap-2">

                                <form action="<?= base_url('tech/toggleStatus/' . $row['id']) ?>" method="post">
                                    <?= csrf_field() ?>

                                    <?php if ($row['status'] === 'inactive'): ?>
                                        <button type="submit" class="btn btn-sm btn-success">
                                            Active
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Inactive
                                        </button>
                                    <?php endif; ?>
                                </form>

                                
                                <button class="btn btn-sm btn-primary editBtn"
                                        data-id="<?= $row['id'] ?>"
                                        data-name="<?= esc($row['name']) ?>"
                                        data-location="<?= esc($row['location']) ?>"
                                        data-role="<?= esc($row['role']) ?>"
                                        data-status="<?= esc($row['status']) ?>">
                                    <li class="fas fa-edit"></li>
                                </button>

                                <form action="<?= base_url('tech/delete/' . $row['id']) ?>"
                                    method="post"
                                    class="deleteForm">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <li class="fas fa-trash"></li>
                                    </button>
                                </form>

                            </div>
                                     
                        <?php 
                            else:
                                echo 'No actions allowed for this user';
                    endif; ?>
                        </td>


                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div> -->
                    <!-- NAV TABS -->
<div class="mb-3 d-flex justify-content-between align-items-center">

<?php if (session()->get('role') == 3): ?>
<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add Technician
</button>


<div class="form-check form-switch">
    <input 
        class="form-check-input" 
        type="checkbox" 
        role="switch" 
        id="overrideSwitch"
        <?= $override ? 'checked' : '' ?>
    >
    <label class="form-check-label fw-semibold" for="overrideSwitch">
        Manual Active/Inactive
    </label>
</div>

<?php endif; ?>

</div>

<ul class="nav nav-tabs mb-3" id="techTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#itcenter">
            IT Center
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#itfirst">
            IT First Floor
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#er">
            ER
        </button>
    </li>
</ul>

<div class="tab-content">

<!-- ================= IT CENTER ================= -->
<div class="tab-pane fade show active" id="itcenter">
<div class="table-responsive">
<table class="table table-striped table-bordered align-middle techTable">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Location</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php $count = 1; ?>
<?php foreach ($records as $row): ?>
<?php if($row['location'] == 'IT Center'): ?>
<tr>
    <td><?= $count++ ?></td>
    <td><?= esc($row['name']) ?></td>
    <td><?= esc($row['location']) ?></td>
    <td>
        <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-danger text-dark' ?>">
            <?= esc($row['status']) ?>
        </span>
    </td>
    <td class="text-center">
        <?php if (session()->get('role') == 3): ?>
        <div class="d-flex justify-content-center gap-2">

                <form action="<?= base_url('tech/toggleStatus/' . $row['id']) ?>" method="post">
                <?= csrf_field() ?>

                <?php if ($row['status'] === 'inactive'): ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-success"
                <?= $override ? '' : 'disabled' ?>
                >
                Active
                </button>

                <?php else: ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-danger"
                <?= $override ? '' : 'disabled' ?>
                >
                Inactive
                </button>

                <?php endif; ?>

                </form>
            <button class="btn btn-sm btn-primary editBtn"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= esc($row['name']) ?>"
                    data-location="<?= esc($row['location']) ?>"
                    data-role="<?= esc($row['role']) ?>"
                    data-status="<?= esc($row['status']) ?>">
                <i class="fas fa-edit"></i>
            </button>

            <form action="<?= base_url('tech/delete/' . $row['id']) ?>"
                  method="post"
                  class="deleteForm">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </form>

        </div>
        <?php else: echo 'No actions allowed for this user'; endif; ?>
    </td>
</tr>
<?php endif; ?>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>


<!-- ================= IT FIRST FLOOR ================= -->
<div class="tab-pane fade" id="itfirst">
<div class="table-responsive">
<table class="table table-striped table-bordered align-middle techTable">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Location</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php $count = 1; ?>
<?php foreach ($records as $row): ?>
<?php if($row['location'] == 'IT First Floor'): ?>
<tr>
    <td><?= $count++ ?></td>
    <td><?= esc($row['name']) ?></td>
    <td><?= esc($row['location']) ?></td>
    <td>
        <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-danger text-dark' ?>">
            <?= esc($row['status']) ?>
        </span>
    </td>
    <td class="text-center">
        <?php if (session()->get('role') == 3): ?>
        <div class="d-flex justify-content-center gap-2">

                <form action="<?= base_url('tech/toggleStatus/' . $row['id']) ?>" method="post">
                <?= csrf_field() ?>

                <?php if ($row['status'] === 'inactive'): ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-success"
                <?= $override ? '' : 'disabled' ?>
                >
                Active
                </button>

                <?php else: ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-danger"
                <?= $override ? '' : 'disabled' ?>
                >
                Inactive
                </button>

                <?php endif; ?>

                </form>
            <button class="btn btn-sm btn-primary editBtn"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= esc($row['name']) ?>"
                    data-location="<?= esc($row['location']) ?>"
                    data-role="<?= esc($row['role']) ?>"
                    data-status="<?= esc($row['status']) ?>">
                <i class="fas fa-edit"></i>
            </button>

            <form action="<?= base_url('tech/delete/' . $row['id']) ?>"
                  method="post"
                  class="deleteForm">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </form>

        </div>
        <?php else: echo 'No actions allowed for this user'; endif; ?>
    </td>
</tr>
<?php endif; ?>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>


<!-- ================= ER ================= -->
<div class="tab-pane fade" id="er">
<div class="table-responsive">
<table class="table table-striped table-bordered align-middle techTable">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Name</th>
    <th>Location</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>

<?php $count = 1; ?>
<?php foreach ($records as $row): ?>
<?php if($row['location'] == 'ER'): ?>
<tr>
    <td><?= $count++ ?></td>
    <td><?= esc($row['name']) ?></td>
    <td><?= esc($row['location']) ?></td>
    <td>
        <span class="badge <?= $row['status'] === 'active' ? 'bg-success' : 'bg-danger text-dark' ?>">
            <?= esc($row['status']) ?>
        </span>
    </td>
        <td class="text-center">
        <?php if (session()->get('role') == 3): ?>
        <div class="d-flex justify-content-center gap-2">

                <form action="<?= base_url('tech/toggleStatus/' . $row['id']) ?>" method="post">
                <?= csrf_field() ?>

                <?php if ($row['status'] === 'inactive'): ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-success"
                <?= $override ? '' : 'disabled' ?>
                >
                Active
                </button>

                <?php else: ?>

                <button 
                type="submit" 
                class="btn btn-sm btn-danger"
                <?= $override ? '' : 'disabled' ?>
                >
                Inactive
                </button>

                <?php endif; ?>

                </form>
            <button class="btn btn-sm btn-primary editBtn"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= esc($row['name']) ?>"
                    data-location="<?= esc($row['location']) ?>"
                    data-role="<?= esc($row['role']) ?>"
                    data-status="<?= esc($row['status']) ?>">
                <i class="fas fa-edit"></i>
            </button>

            <form action="<?= base_url('tech/delete/' . $row['id']) ?>"
                  method="post"
                  class="deleteForm">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            </form>

        </div>
        <?php else: echo 'No actions allowed for this user'; endif; ?>
    </td>
</tr>
<?php endif; ?>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>

</div>




        </div>



<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form method="post" id="editForm">
                <?= csrf_field() ?>

                <div class="modal-header">
                    <h5 class="modal-title">Edit Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Location</label>
                        <select name="location" id="editLocation" class="form-select">
                            <option value="ER">ER</option>
                            <option value="IT First Floor">IT First Floor</option>
                            <option value="IT Center">IT Center</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="password" id="editPassword" class="form-control">
                        <small class="text-muted">Leave blank if you don't want to change the password.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" id="editRole" class="form-select">
                            <option value="user">Support</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>

        </div>
    </div>
</div>





<!-- ADD TECHNICIAN MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="post" action="<?= base_url('tech/store') ?>">
<?= csrf_field() ?>

<div class="modal-header">
    <h5 class="modal-title">Add Technician</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-3">
        <label>Full Name</label>
        <input type="text" name="fullname" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Username</label>
        <input type="text" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Office</label>
        <select name="location" class="form-select" required>
            <option value="">Select Office</option>
            <option value="IT Center">IT Center</option>
            <option value="IT First Floor">IT First Floor</option>
            <option value="ER">ER</option>
        </select>
    </div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-success">Save</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
</div>

</form>

</div>
</div>
</div>





<script>
$(document).ready(function () {

$('.techTable').DataTable({
    pageLength: 8,
    lengthMenu: [ [8, 16, 25, 50, 100], [8, 16, 25, 50, 100] ],
    order: [[0, 'asc']],
    responsive: true
});

// Hide initial loader
setTimeout(function(){
    $('#pageLoader').fadeOut(400);
}, 500);


/* ===========================
   DELETE CONFIRM
=========================== */
$('.deleteForm').on('submit', function(e) {
    e.preventDefault();
    let form = this;

    Swal.fire({
        title: 'Delete this record?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    });
});


/* ===========================
   STATUS TOGGLE CONFIRM
=========================== */
$('.statusForm').on('submit', function(e) {
    e.preventDefault();
    let form = this;

    Swal.fire({
        title: 'Change Status?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'Yes, change it'
    }).then((result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Updating...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            form.submit();
        }
    });
});


/* ===========================
   EDIT MODAL
=========================== */
$(document).on('click', '.editBtn', function () {

    let id       = $(this).data('id');
    let name     = $(this).data('name');
    let location = $(this).data('location');
    let role     = $(this).data('role');
    let status   = $(this).data('status');

    $('#editName').val(name);
    $('#editLocation').val(location);
    $('#editRole').val(role);
    $('#editStatus').val(status);

    $('#editForm').attr('action', "<?= base_url('tech/update/') ?>" + id);

    $('#editModal').modal('show');
});



/* ===========================
   SUCCESS FLASH MESSAGE
=========================== */
<?php if(session()->getFlashdata('success')): ?>
Swal.fire({
    icon: 'success',
    title: 'Success!',
    text: '<?= session()->getFlashdata('success') ?>',
    timer: 2000,
    showConfirmButton: false
});
<?php endif; ?>

});

$('#overrideSwitch').on('change', function(){

    let status = $(this).is(':checked') ? 1 : 0;

    Swal.fire({
        title: 'Change Scheduler Mode?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes'
    }).then((result)=>{

        if(result.isConfirmed){

            window.location.href = "<?= base_url('tech/setOverride/') ?>" + status;

        }else{

            location.reload();

        }

    });

});
</script>






<?php $this->endSection(); ?>
