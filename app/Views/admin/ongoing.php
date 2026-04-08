<?php
$this->extend('layout/main');
$this->section('body');
?>

<style>
.btn-add-response {
    background: linear-gradient(135deg, #4e73df, #6f42c1);
    color: #fff;
    border: none;
    padding: 10px 22px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 50px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
}

.btn-add-response:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    background: linear-gradient(135deg, #3d5bd1, #5a32b8);
}

.btn-add-response i {
    font-size: 13px;
}
</style>


<div class="container-fluid">

<!-- TABLE -->
<div class="card shadow-sm">    
    <div class="card-body table-responsive">
        <table id="activityTable" class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Date & Time</th>
                    <th>Name of Caller</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Personnel Name</th>
                    <th>Time Started</th> 
                    <th>Remarks / Action</th>
                    <th>Returned Time</th>
                    <th>Acknowledged By</th> 
                </tr>
            </thead>

            <tbody>
            <?php if (!$todayTroubles): ?>
                <tr>
                    <td colspan="11" class="text-center text-muted">
                        No records today
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($todayTroubles as $i => $row): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= date('m/d/Y - h:i A', strtotime($row['time'])) ?></td>
                <td><?= esc($row['name']) ?></td>
                <td><?= esc($row['location']) ?></td>
                <td><?= esc($row['description']) ?></td>
                <td>
                    <span class="badge <?= $row['status'] === 'Ongoing' ? 'bg-warning' : 'bg-success' ?>">
                        <?= esc($row['status']) ?>
                    </span>
                </td>
                <td><?= esc($row['tech_name'] ?? '-') ?></td>

                <!-- Time Started Column -->
                <td>
                    <?php if (empty($row['time_started']) && $row['status'] === 'Ongoing'): ?>
                        <form action="<?= site_url('trouble/startNow') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm">Start Now</button>
                        </form>
                    <?php elseif (!empty($row['time_started'])): ?>
                        <?= date('h:i A', strtotime($row['time_started'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>

                <!-- Remarks / Action Column -->
              <td>
                <?php if ($row['status'] === 'Ongoing' && !empty($row['Acknoby'])): ?>
                    <div class="d-flex align-items-center gap-1">

                        <!-- DONE WITH REMARKS -->
                        <form action="<?= site_url('trouble/markDone') ?>" method="post" class="d-flex gap-1 m-0 align-items-center">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <!-- Remarks input -->
                            <input type="text" name="remarks" class="form-control form-control-sm" placeholder="Add remarks" style="width:150px;">
                            <!-- Done button -->
                            <button class="btn btn-success btn-sm" title="Done">✔</button>
                        </form>

                        <!-- ENDORSE BUTTON -->
                        <button
                            type="button"
                            class="btn btn-danger btn-sm endorse-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#endorseModal"
                            data-id="<?= $row['id'] ?>">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>

                        <!-- DELETE BUTTON -->
                        <form action="<?= site_url('trouble/delete') ?>" method="post" class="delete-form m-0">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="button"
                                    class="btn btn-danger btn-sm btn-delete"
                                    title="Delete">🗑</button>
                        </form>

                    </div>
                <?php else: ?>
                    <?= esc($row['remarks'] ?? '-') ?>
                <?php endif; ?>
            </td>

                <!-- Returned Time Column -->
                <td>
                    <?php
                    if (!empty($row['completion_time']) && !empty($row['time_started'])) {
                        $start = new DateTime($row['time_started']);
                        $end   = new DateTime($row['completion_time']);
                        $diff = $start->diff($end);
                        $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

                        if ($minutes < 60) {
                            echo $minutes . ' min';
                        } else {
                            $hours = floor($minutes / 60);
                            $mins  = $minutes % 60;
                            echo $hours . ' hr' . ($hours > 1 ? 's' : '');
                            if ($mins > 0) echo ' ' . $mins . ' min';
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </td>

                <!-- Acknowledged By Column -->
                
                    <td>
                        <?php if (!empty($row['Acknoby'])): ?>
                            <?php 
                                $ackPerson = null;
                                foreach ($acknos as $a) {
                                    if ($a['id'] == $row['Acknoby']) {
                                        $ackPerson = $a;
                                        break;
                                    }
                                }
                            ?>
                            <?php if ($ackPerson): ?>
                                <div class="mb-1"><strong>ID Number:</strong> <?= esc($ackPerson['id_num']) ?></div>
                                <div><strong>Full Name:</strong> <?= esc($ackPerson['full_name']) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <form action="<?= site_url('trouble/saveAck') ?>" method="post">
                                <?= csrf_field() ?>

                                <!-- Hidden field for trouble ID -->
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                <!-- ID Number -->
                                <input type="text" 
                                    name="id_num"
                                    data-id="<?= $row['id'] ?>" 
                                    class="form-control form-control-sm mb-1" 
                                    placeholder="ID Number" 
                                    required>

                                <!-- Full Name -->
                                <input type="text" 
                                    name="full_name"
                                    data-id="<?= $row['id'] ?>" 
                                    class="form-control form-control-sm mb-1" 
                                    placeholder="Full Name" 
                                    required>

                                <!-- Save Button -->
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    Save
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>



<!-- ENDORSE MODAL -->
<div class="modal fade" id="endorseModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form action="<?= site_url('trouble/endorse') ?>" method="post">
                <?= csrf_field() ?>

                <input type="hidden" name="id" id="endorseId">

                <div class="modal-header">
                    <h5 class="modal-title">Endorse Troubleshoot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3 position-relative">
                        <label class="form-label">Technical</label>

                        <input type="text"
                               id="endorseTechInput"
                               class="form-control"
                               autocomplete="off"
                               placeholder="Search technical">

                        <div id="endorseSelectedTechs" class="mt-2"></div>

                        <div id="endorseTechList"
                             class="list-group position-absolute w-100 d-none"
                             style="max-height:200px; overflow-y:auto; z-index:1056">
                        </div>

                        <small class="text-muted">
                            Select one IT personnel only
                        </small>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger">Confirm Endorse</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>



<!-- SWEETALERT SUCCESS -->
<?php if (session()->getFlashdata('success')): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '<?= session()->getFlashdata('success') ?>',
        timer: 3000,              // 3.5 seconds
        timerProgressBar: true,   // shows loading bar
        showConfirmButton: false  // removes OK button
    });
});

</script>
<?php endif; ?>





<script>
$(document).on('click', '.endorse-btn', function () {
    let id = $(this).data('id');
    $('#endorseId').val(id);
});

$('#endorseModal form').on('submit', function(e){

    e.preventDefault();
    let form = this;

    Swal.fire({
        title: 'Confirm Endorse?',
        text: "This ticket will be endorsed to selected personnel.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, endorse it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });

});

$(document).on('click', '.btn-delete', function () {

    let form = $(this).closest('form');

    Swal.fire({
        title: 'Are you sure?',
        text: "This record will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });

});

// endorse here uses single select for simplicity, adjust if you want multiple
$('#endorseTechInput').on('keyup', function () {

    let q = $(this).val();

    if (q.length < 1) {
        $('#endorseTechList').addClass('d-none');
        return;
    }

    $.get('<?= site_url("/search/technician") ?>', { q }, function (data) {

        let list = '';

        data.forEach(item => {
            list += `<button type="button"
                     class="list-group-item list-group-item-action endorse-tech-item"
                     data-id="${item.id}"
                     data-name="${item.name}">
                     ${item.name}
                     </button>`;
        });

        $('#endorseTechList').html(list).removeClass('d-none');
    });
});


$(document).on('click', '.endorse-tech-item', function () {

    let id   = $(this).data('id');
    let name = $(this).data('name');

    // 👇 REPLACE existing selection (single select only)
    $('#endorseSelectedTechs').html(`
        <span class="badge bg-danger d-inline-flex align-items-center">
            ${name}
            <button type="button"
                class="btn-close btn-close-white ms-2 remove-endorse"
                style="font-size:10px;">
            </button>
            <input type="hidden" name="person_id" value="${id}">
        </span>
    `);

    $('#endorseTechInput').val(name); // optional: show selected name
    $('#endorseTechList').addClass('d-none');
});


$(document).ready(function () {

let table = $('#activityTable').DataTable({
        pageLength: 10,
        order: [[8, 'desc']],
        responsive: true,
        processing: true,
        language: {
            processing: "Loading records..."
        }
    });

    setTimeout(function(){
        $('#pageLoader').fadeOut(400);
    }, 500);

    $('form').on('submit', function(){
        $('#pageLoader').fadeIn(200);
    });


});
</script>


<?php $this->endSection(); ?>
