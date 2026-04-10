<?php $this->extend('layout/main'); ?>
<?php $this->section('body'); ?>

<style>
.input-group-text {
    background: transparent !important;
}

</style>

    <h3 class="mb-4">My Profile</h3>


    <div class="row">

        <!-- LEFT SIDE - PROFILE INFO -->
        <div class="col-md-4">

            <div class="card mb-4">
                <div class="card-body">

                    <h5>User Information</h5>
                    <hr>
                    <!-- Update Username -->
                        <form action="<?= base_url('profile/update') ?>" method="post">
                            <?= csrf_field() ?>

                            <!-- USERNAME -->
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text"
                                    name="username"
                                    class="form-control"
                                    value="<?= esc($user['uname']) ?>">
                                <input type="hidden" name="old_username" value="<?= esc($user['uname']) ?>">
                            </div>

                            <hr>

                            <!-- NAME -->
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    value="<?= esc($user['name']) ?>">
                                <input type="hidden" name="old_name" value="<?= esc($user['name']) ?>">
                            </div>

                            <hr>

                            <!-- PASSWORD -->
                            <div class="mb-3">
                                <label>New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                        name="new_password"
                                        id="newPassword"
                                        class="form-control">

                                    <span class="input-group-text bg-white border-start-0"
                                        style="cursor:pointer;"
                                        id="togglePassword">
                                        <i class="fa fa-eye text-muted"></i>
                                    </span>
                                </div>

                                <!-- Strength -->
                                <div class="mt-2">
                                    <div class="progress" style="height:6px;">
                                        <div id="passwordStrengthBar"
                                            class="progress-bar"
                                            style="width:0%">
                                        </div>
                                    </div>
                                    <small id="passwordStrengthText" class="fw-semibold"></small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100">
                                Save Changes
                            </button>
                        </form>


                </div>
            </div>

        </div>


        <!-- RIGHT SIDE - ACTIVITY LOG -->
        <div class="col-md-8">

            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>From Date</label>
                            <input type="date" id="minDate" class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4">
                            <label>To Date</label>
                            <input type="date" id="maxDate" class="form-control form-control-sm">
                        </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button id="clearDate" class="btn btn-sm btn-outline-secondary w-50">
                            Clear Filter
                        </button>

                        <a href="#" id="generatePdf" class="btn btn-sm btn-danger w-50">
                           <i class="fa-regular fa-file-pdf"></i> Generate PDF
                        </a>
                    </div>
                    </div>
                    <h5>Activity Log</h5>
                    <hr>

                    <?php if(empty($activities)): ?>
                        <p class="text-muted">No activity found.</p>
                    <?php else: ?>

                        <table id="activityTable" class="table table-sm table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Area/Section</th>
                                    <th>Trouble Description</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($activities as $i => $row): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= esc($row['name'] ?? '-') ?></td>
                                    <td><?= esc($row['description'] ?? '-') ?></td>
                                    <td><?= esc($row['remarks'] ?? '-') ?></td>
                                    <td>
                                        <?= esc($row['status'] ?? '-') ?>
                                    </td>
                                    <td data-order="<?= strtotime($row['time']) ?>">
                                        <?= date('M d, Y h:i A', strtotime($row['time'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php endif; ?>

                </div>
            </div>

        </div>

    </div>


<?php if (session()->getFlashdata('success')): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'success',
        title: '<?= session()->getFlashdata('success') ?>',
        showConfirmButton: false,
        timer: 2000
    });
});
</script>
<?php endif; ?>


<?php if (session()->getFlashdata('logout_reason')): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'success',
        title: '<?= session()->getFlashdata('logout_reason') ?>',
        confirmButtonText: 'Logout Now',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "<?= base_url('logout') ?>";
        }
    });
});
</script>
<?php endif; ?>


<?php if (session()->getFlashdata('info')): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    Swal.fire({
        icon: 'info',
        title: '<?= session()->getFlashdata('info') ?>',
        showConfirmButton: false,
        timer: 2000
    });
});
</script>
<?php endif; ?>

<script>
$(document).ready(function () {

    // ================= PDF GENERATE =================
    $('#generatePdf').on('click', function (e) {
        e.preventDefault();

        let min = $('#minDate').val();
        let max = $('#maxDate').val();

        if (!min || !max) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select start and end date.'
            });
            return;
        }

        let startDate = new Date(min);
        let endDate = new Date(max);

        let months =
            (endDate.getFullYear() - startDate.getFullYear()) * 12 +
            (endDate.getMonth() - startDate.getMonth());

        if (months > 6) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Date Range',
                text: 'Please select 6 months or below only.'
            });
            return;
        }

        let url = "<?= base_url('profile/printPdf') ?>?start_date=" + min + "&end_date=" + max;

        window.open(url, '_blank');
    });

    // ================= DATATABLE =================
    var table = $('#activityTable').DataTable({
        pageLength: 4,
        lengthMenu: [[4, 10, 25, 50, 100], [4, 10, 25, 50, 100]],
        ordering: true,
        searching: true,
        responsive: true
    });

    // Date filter
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {

        var min = $('#minDate').val();
        var max = $('#maxDate').val();

        if (!min && !max) return true;

        var rowNode = table.row(dataIndex).node();
        var timestamp = $(rowNode).find('td').eq(4).data('order');

        if (!timestamp) return true;

        var rowDate = new Date(timestamp * 1000);

        if (min) {
            var minDate = new Date(min);
            minDate.setHours(0,0,0,0);
            if (rowDate < minDate) return false;
        }

        if (max) {
            var maxDate = new Date(max);
            maxDate.setHours(23,59,59,999);
            if (rowDate > maxDate) return false;
        }

        return true;
    });

    $('#minDate, #maxDate').on('change', function () {
        table.draw();
    });

    $('#clearDate').on('click', function () {
        $('#minDate').val('');
        $('#maxDate').val('');
        table.draw();
    });

});

document.getElementById("togglePassword").addEventListener("click", function () {
    const passwordInput = document.getElementById("newPassword");
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
});

document.getElementById("newPassword").addEventListener("keyup", function () {

    const password = this.value;
    const strengthBar = document.getElementById("passwordStrengthBar");
    const strengthText = document.getElementById("passwordStrengthText");

    let strength = 0;

    if (password.length >= 6) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[\W]/)) strength++;

    if (password.length === 0) {
        strengthBar.style.width = "0%";
        strengthText.innerHTML = "";
        return;
    }

    if (strength <= 1) {
        strengthBar.style.width = "33%";
        strengthBar.className = "progress-bar bg-danger";
        strengthText.innerHTML = "Weak Password";
        strengthText.className = "text-danger fw-semibold";
    }
    else if (strength === 2 || strength === 3) {
        strengthBar.style.width = "66%";
        strengthBar.className = "progress-bar bg-warning";
        strengthText.innerHTML = "Normal Password";
        strengthText.className = "text-warning fw-semibold";
    }
    else {
        strengthBar.style.width = "100%";
        strengthBar.className = "progress-bar bg-success";
        strengthText.innerHTML = "Strong Password";
        strengthText.className = "text-success fw-semibold";
    }

});

</script>
<?php $this->endSection(); ?>
