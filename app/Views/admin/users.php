<?php
$this->extend('layout/main');
$this->section('body');
?>

<div class="container-fluid mt-4">

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">👥 User Management</h5>

      <div class="d-flex align-items-center gap-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="showInactive">
          <label class="form-check-label">Show Inactive</label>
        </div>

        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="bi bi-person-plus"></i> Add User
        </button>
      </div>
    </div>

    <div class="card-body">
      <table id="userTable" class="table table-hover align-middle w-100">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Ward/Office</th>
            <th>Role</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

</div>


<!-- Add User Modal -->

 <div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="addUserForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-2">
            <label>First Name</label>
            <input type="text" name="fname" class="form-control" required>
          </div>

          <div class="mb-2">
            <label>Middle Initial</label>
            <input type="text" name="mname" class="form-control" required>
          </div>

          <div class="mb-2">
            <label>Last Name</label>
            <input type="text" name="lname" class="form-control" required>
          </div>

          <div class="mb-2">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>

          <div class="mb-2">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-2">
            <label>Position</label>
            <input type="text" name="position" class="form-control" required>
          </div>

          <div class="mb-2">
              <label class="fw-bold">Ward/Office</label>
                <input type="text"
                      id="ward"
                      name="ward"
                      class="form-control form-control"
                      placeholder="Type to search ward..."
                      autocomplete="off"
                      required>

                <div id="ward_dropdown"
                    class="list-group position-absolute"
                    style="z-index:1000; max-height:200px; overflow-y:auto; display:none; width:95%; border:1px solid #ced4da; border-radius:0 0 .375rem .375rem;">
                    <?php foreach ($wards as $w): ?>
                        <button type="button"
                                class="list-group-item list-group-item-action"
                                data-ward="<?= esc($w['ward']) ?>">
                            <?= esc($w['ward']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
          </div>

          <div class="mb-2">
            <label>Role</label>
            <select name="role" class="form-control">
              <option value="0">User</option>
              <option value="1">Admin</option>
            </select>
          </div>
          
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- End Add User Modal -->

<!-- Upload Signature Modal -->
<div class="modal fade" id="uploadSignatureModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="uploadSignatureForm" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Upload Signature for <span id="signatureUsername"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="user_id" id="signatureUserId">
          <div class="mb-2">
            <label>Signature Image (PNG only)</label>
            <input type="file" name="signature" class="form-control" accept=".png" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Upload</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
<!-- End Upload Signature Modal -->


<script>

let userTable;

$(document).ready(function () {

    // 🔍 Custom filter for inactive users (MUST be inside ready)
    $.fn.dataTable.ext.search.push(function (settings, data) {
        const showInactive = $('#showInactive').is(':checked');
        const statusHtml = data[4]; // status column index

        // Hide inactive users unless checkbox is checked
        if (!showInactive && statusHtml.includes('Inactive')) {
            return false;
        }
        return true;
    });

userTable = $('#userTable').DataTable({
    ajax: "<?= base_url('users/fetch') ?>",
    responsive: true,
    columns: [
        {
            data: null,
            render: row => `
                <strong>${row.fname} ${row.lname}</strong>
            `
        },
        { data: 'username' },
        { data: 'ward' },
        {
            data: 'role',
            render: r => {
                if (r == 3) return '<span class="badge rounded-pill bg-danger">Super Admin</span>';
                if (r == 1) return '<span class="badge rounded-pill bg-secondary">Admin</span>';
                return '<span class="badge rounded-pill bg-info">User</span>';
            }
        },
        {
            data: 'status',
            render: s => s === 'A'
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-danger">Inactive</span>'
        },
        {
            data: null,
            className: 'text-end',
            render: function (row) {

                let signatureText = row.signature ? 'Update Signature' : 'Upload Signature';
                let signatureClass = row.signature ? 'btn-success' : 'btn-info';

                if (row.status === 'I') {
                    return `
                        <button class="btn btn-success btn-sm activateUser" data-id="${row.id}">
                          Activate
                        </button>
                        <button class="btn ${signatureClass} btn-sm uploadSignature"
                                data-id="${row.id}"
                                data-username="${row.username}">
                          ${signatureText}
                        </button>
                    `;
                }

                return `
                    <button class="btn btn-danger btn-sm deleteUser" data-id="${row.id}">
                      Delete
                    </button>
                    <button class="btn ${signatureClass} btn-sm uploadSignature"
                            data-id="${row.id}"
                            data-username="${row.username}">
                      ${signatureText}
                    </button>
                `;
            }
        }
    ]
});

    // 🔁 Redraw table when checkbox changes
    $('#showInactive').on('change', function () {
        userTable.draw();
    });




// Handle Add User Form Submission

$('#addUserForm').submit(function (e) {
    e.preventDefault();

    $.ajax({
        url: "<?= base_url('users/store') ?>",
        type: "POST",
        data: $(this).serialize(),
        success: function () {
            $('#addUserModal').modal('hide');
            $('#addUserForm')[0].reset();
            userTable.ajax.reload();

            Swal.fire({
                icon: 'success',
                title: 'User Created',
                text: 'The user has been successfully added.',
                timer: 2000,
                showConfirmButton: false
            });
        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save user.'
            });
        }
    });
});

const csrfName = '<?= csrf_token() ?>';
const csrfHash = '<?= csrf_hash() ?>';

$(document).on('click', '.deleteUser', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Are you sure?',
        text: 'This user will be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: "<?= site_url('users/delete') ?>/" + id,
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash
            },

            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    userTable.ajax.reload(null, false);
                }
            },

            error: function (xhr) {
                console.error(xhr.status, xhr.responseText);
                Swal.fire('Error', 'Delete failed', 'error');
            }
        });
    });
});

$(document).on('click', '.activateUser', function () {
    let id = $(this).data('id');

    Swal.fire({
        title: 'Activate user?',
        text: 'This user will be activated.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, activate'
    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({
            url: "<?= site_url('users/activate') ?>/" + id,
            type: "POST",
            dataType: "json",
            data: {
                [csrfName]: csrfHash
            },
            success: function (res) {
                if (res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Activated!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    userTable.ajax.reload(null, false);
                }
            },
            error: function () {
                Swal.fire('Error', 'Activation failed', 'error');
            }
        });
    });
});

$(document).on('click', '.uploadSignature', function () {
    let id = $(this).data('id');
    let username = $(this).data('username');
    $('#signatureUserId').val(id);
    $('#signatureUsername').text(username);
    $('#uploadSignatureModal').modal('show');
});

$('#uploadSignatureForm').submit(function (e) {
    e.preventDefault();

    let formData = new FormData(this);

    $.ajax({
        url: "<?= base_url('users/uploadSignature') ?>/" + $('#signatureUserId').val(),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            if (res.status === 'success') {
                $('#uploadSignatureModal').modal('hide');
                $('#uploadSignatureForm')[0].reset();
                Swal.fire({
                    icon: 'success',
                    title: 'Signature Uploaded',
                    text: 'The signature has been successfully uploaded.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', res.message || 'Upload failed', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Upload failed', 'error');
        }
    });
});


});


(function(){
    const input = document.getElementById('ward');
    const dropdown = document.getElementById('ward_dropdown');
    const wardCodeInput = document.getElementById('wardcode');

    if (!input || !dropdown) return;

    // Show & filter dropdown
    input.addEventListener('input', function () {
        const val = this.value.toLowerCase();
        let hasMatch = false;

        dropdown.querySelectorAll('button').forEach(btn => {
            const text = btn.textContent.toLowerCase();
            if (text.includes(val)) {
                btn.style.display = 'block';
                hasMatch = true;
            } else {
                btn.style.display = 'none';
            }
        });

        dropdown.style.display = hasMatch ? 'block' : 'none';
    });

    // Select ward
    dropdown.addEventListener('click', function (e) {
        if (e.target.tagName !== 'BUTTON') return;

        input.value = e.target.dataset.ward;
        if (wardCodeInput) {
            wardCodeInput.value = e.target.dataset.wardcode || '';
        }

        dropdown.style.display = 'none';
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
})();
</script>

<?php $this->endSection(); ?>