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

/* Remove default link underline */
.row a {
    text-decoration: none;
}

/* Card hover effect */
.row .card {
    transition: all 0.3s ease-in-out;
    cursor: pointer;
}

/* Hover animation */
.row .card:hover {
    transform: translateY(-8px) scale(1.03);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
}

/* Optional: smooth number highlight on hover */
.row .card:hover h1 {
    color: #ffffff;
    transition: color 0.3s ease;
}

/* Optional: slight brightness effect */
.row .card:hover .card-body {
    filter: brightness(1.1);
}

</style>


<div class="container-fluid">
<!-- STATS -->
<div class="row mb-4">
    <!-- <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-light p-3">👥</div>
                <div>
                    <small class="text-muted">On Duty | IT</small>
                    <h3><?= $onDutyCount ?></h3>
                </div>
            </div>
        </div>
    </div> -->

            
            <div class="row">
              <div class="col-md-4">
                <a href="<?= site_url('tech') ?>">
                  <div class="card card-secondary">
                  <div class="card-body skew-shadow">
                    <h1 id="onDutyCount"><?= $onDutyCount ?></h1>
                    <h5 class="op-8">On Duty</h5>
                  </div>
                
                </div>
                </a>
              </div>

              
              <div class="col-md-4">
                <a href="<?= site_url('actlog') ?>">
                <div class="card card-secondary bg-secondary-gradient">
                  <div class="card-body bubble-shadow">
                    <h1 id="totalTSCount"><?= $TotalTSCount ?></h1>
                    <h5 class="op-8">Total Troubleshoots</h5>
                  </div>
                </div>
                </a>
              </div>
            
              
              <div class="col-md-4">
                <a href="<?= site_url('ongoing') ?>">
                <div class="card card-secondary bg-secondary-gradient">
                  <div class="card-body curves-shadow">
                    <h1 id="ongoingCount"><?= $ongoingCount ?></h1>
                    <h5 class="op-8">Ongoing Troubleshoot</h5>
                  </div>
                </div>
              </div>
            </a>
            </div>
            

            <!-- <div class="col-6 col-sm-4 col-lg-2">
                <div class="card">
                  <div class="card-body p-3 text-center">
                    <div class="h1 m-0"><?= $ongoingCount ?></div>
                    <div class="text-muted mb-3">Troubleshoot | Ongoing</div>
                  </div>
                </div>
              </div> -->

    <!-- <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-25 p-3">🛠️</div>
                <div>
                    <small class="text-muted">Troubleshoot | Ongoing</small>
                    <h3><?= $ongoingCount ?></h3>
                </div>
            </div>
        </div>
    </div> -->
</div>

<div class="d-flex justify-content-end mb-3">
    <button class="btn-add-response"
            data-bs-toggle="modal"
            data-bs-target="#addResponseModal">
        <i class="fas fa-plus me-2"></i>
        Add Response
    </button>
</div>
<!-- TABLE -->
<div class="card shadow-sm">    
    <div class="card-body table-responsive">
        <table id="activityTable" class="table table-striped table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Time Called</th>
                    <th>Name of Caller</th>
                    <th>Area</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Personnel Name</th>
                    <th>Time Started</th>
                    <th>Remarks and Action</th>
                    <th>Returned Time</th>
                    <th>Acknowledged By</th>
                </tr>
            </thead>

            <tbody id="todayTableBody">
    <?= view('admin/partials/today_table_rows', ['todayTroubles' => $todayTroubles]) ?>
</tbody>

        </table>
    </div>
</div>

</div>

<!-- ADD RESPONSE MODAL -->
<div class="modal fade" id="addResponseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">

            <form action="<?= site_url('trouble/saveResponse') ?>" method="post">

                <div class="modal-header">
                    <h5 class="modal-title">Add Troubleshoot Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Caller -->
                    <div class="mb-3">
                        <label class="form-label">Caller’s Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <!-- Office (live + allow new) -->
                    <div class="mb-3 position-relative">
                        <label class="form-label">Office/Area</label>
                        <input type="text" name="location" id="officeInput"
                               class="form-control" autocomplete="off"
                               placeholder="Search or type office" required>

                        <div id="officeList" class="list-group position-absolute w-100 d-none"
                             style="max-height:200px; overflow-y:auto; z-index:1056"></div>
                    </div>

                    <!-- Type (regular dropdown) -->
                    <div class="mb-3">
                        <label class="form-label">Concern Type</label>
                        <select name="ts_type" class="form-select" required>
                            <option value="">Select Concern Type</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= esc($t['tstype']) ?>">
                                    <?= esc($t['tstype']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>

                    <!-- Technical (Live Search Multi Select) -->
                    <div class="mb-3 position-relative">
                        <label class="form-label">IT Technical</label>

                        <!-- Search Input -->
                        <input type="text" id="techInput"
                            class="form-control"
                            autocomplete="off"
                            placeholder="Search IT technical">

                        <!-- Selected Technicians Display -->
                        <div id="selectedTechs" class="mt-2"></div>

                        <!-- Live Search List -->
                        <div id="techList"
                            class="list-group position-absolute w-100 d-none"
                            style="max-height:200px; overflow-y:auto; z-index:1056">
                        </div>

                        <small class="text-muted">
                            You can select multiple IT personnel
                        </small>
                    </div>


                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Save</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>

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


/* OFFICE LIVE SEARCH (allow new) */
$('#officeInput').on('keyup', function () {
    let q = $(this).val();
    if (q.length < 1) {
        $('#officeList').addClass('d-none');
        return;
    }

    $.get('<?= site_url("/search/ward") ?>', { q }, function (data) {
        let list = '';
        data.forEach(item => {
            list += `<button type="button"
                     class="list-group-item list-group-item-action office-item">
                     ${item.ward}
                     </button>`;
        });

        $('#officeList').html(list).removeClass('d-none');
    });
});

$(document).on('click', '.office-item', function () {
    $('#officeInput').val($(this).text().trim());
    $('#officeList').addClass('d-none');
});


/* TECHNICAL LIVE SEARCH (must select) */
$('#techInput').on('keyup', function () {
    let q = $(this).val();
    if (q.length < 1) {
        $('#techList').addClass('d-none');
        return;
    }

$.get('<?= site_url("/search/technician") ?>', { q }, function (data) {
    let list = '';
    data.forEach(item => {
        list += `<button type="button"
                 class="list-group-item list-group-item-action tech-item"
                 data-id="${item.id}"
                 data-name="${item.name}">
                 ${item.name}
                 </button>`;
    });

    $('#techList').html(list).removeClass('d-none');
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




let selectedTechIds = [];

$(document).on('click', '.tech-item', function () {

    let id   = parseInt($(this).data('id'));
    let name = $(this).data('name');

    if (selectedTechIds.includes(id)) return;

    selectedTechIds.push(id);

    $('#selectedTechs').append(`
        <span class="badge bg-primary me-2 mb-2 tech-badge d-inline-flex align-items-center">
            ${name}
            <button type="button"
                class="btn-close btn-close-white ms-2 remove-tech"
                data-id="${id}"
                style="font-size:10px;">
            </button>
            <input type="hidden" name="person_id[]" value="${id}">
        </span>
    `);

    $('#techInput').val('');
    $('#techList').addClass('d-none'); // 👈 close after select
});

$(document).on('click', '.remove-tech', function () {

    let id = parseInt($(this).data('id'));

    selectedTechIds = selectedTechIds.filter(t => t !== id);

    $(this).closest('.tech-badge').remove();
});


</script>


<script>
$(document).ready(function () {

let table = $('#activityTable').DataTable({
    pageLength: 10,
     lengthMenu: [[10, 20, 30, 100], [10, 20, 30, 100]],
    order: [[5, 'desc']],
    responsive: true,
    processing: true,
    language: {
        processing: "Loading records..."
    }
});

// Loader
setTimeout(function(){
    $('#pageLoader').fadeOut(400);
}, 500);

// 🔥 FORM SUBMIT FIX (instant refresh after action)
$('form').on('submit', function(){
    $('#pageLoader').fadeIn(200);

    setTimeout(() => {
        refreshCounts(); // 🔥 force update immediately
    }, 500);
});

// Auto numbering
table.on('order.dt search.dt draw.dt', function () {
    table.column(0, { search: 'applied', order: 'applied' })
        .nodes()
        .each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
}).draw();


// ==============================
// 🔔 CHECK NEW TROUBLESHOOTS
// ==============================
let lastId = <?= !empty($todayTroubles) ? max(array_column($todayTroubles, 'id')) : 0 ?>;
let isChecking = false;

function checkNewTrouble() {
    if (isChecking) return;
    isChecking = true;

    $.get('<?= site_url("dashboard/check-new") ?>', { lastId: lastId }, function (response) {

        if (response.new.length > 0) {

            response.new.forEach((item, index) => {

                if (item.id > lastId) {
                    lastId = item.id;
                }

                setTimeout(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: 'New Troubleshoot Request!',
                        text: (item.personnel ?? 'Someone') + ' submitted a new troubleshoot.',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }, index * 800);

            });

        }

    }).always(function () {
        isChecking = false;
    });
}

// Run the check immediately on page load
checkNewTrouble();

// Then continue checking every 5 seconds
setInterval(checkNewTrouble, 5000);



// ==============================
// 🔄 REFRESH TABLE
// ==============================
setInterval(function () {

    // 🔥 Save all input values before refresh
    let inputData = {};
    $('input[name="remarks"], input[name="id_num"], input[name="full_name"]').each(function() {
        let id = $(this).data('id');
        if (id) {
            inputData[$(this).attr('name') + '_' + id] = $(this).val();
        }
    });

    $.get("<?= site_url('dashboard/refreshTodayTable') ?>?t=" + new Date().getTime(), function (data) {

        let temp = $('<tbody>').html(data);

        // Clear old table
        table.clear();

        // Extract rows safely
        temp.find('tr').each(function () {
            let row = [];
            $(this).find('td').each(function () {
                row.push($(this).html());
            });
            if (row.length === 11) {
                table.row.add(row);
            }
        });

        // Redraw table
        table.draw(false);

        // 🔥 Restore all input values after redraw
        $('input[name="remarks"], input[name="id_num"], input[name="full_name"]').each(function() {
            let id = $(this).data('id');
            if (id) {
                let key = $(this).attr('name') + '_' + id;
                if (inputData[key] !== undefined) {
                    $(this).val(inputData[key]);
                }
            }
        });

        // Update counts
        refreshCounts();
    });

}, 100000);


// ==============================
// 🔢 REFRESH COUNTS (FIXED)
// ==============================
function refreshCounts() {
    fetch("<?= site_url('dashboard/refresh-counts') ?>?t=" + new Date().getTime()) // 🔥 prevent cache
        .then(response => response.json())
        .then(data => {
            document.getElementById('onDutyCount').innerText = data.onDuty;
            document.getElementById('totalTSCount').innerText = data.totalTS;
            document.getElementById('ongoingCount').innerText = data.ongoing;
        })
        .catch(error => console.error('Error refreshing counts:', error));
}

// 🔥 run immediately
refreshCounts();

// 🔥 faster refresh (better UX)
setInterval(refreshCounts, 2000);


// ==============================
// 🧹 RESET ENDORSE MODAL
// ==============================
$('#endorseModal').on('hidden.bs.modal', function () {
    endorseSelectedIds = [];
    $('#endorseSelectedTechs').html('');
    $('#endorseTechInput').val('');
    $('#endorseTechList').addClass('d-none');
});

});
</script>



<?php $this->endSection(); ?>
