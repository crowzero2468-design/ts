<?php
$this->extend('layout/main');
$this->section('body');
?>

    <div class="card mb-3 shadow-sm">
        <div class="card-body">

            <div class="row align-items-end g-3">

                <!-- BUTTONS -->
                <div class="col-md-auto">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addPmsModal">
                        <i class="bi bi-plus-circle"></i> Add PMS Record
                    </button>
                </div>

                <div class="col-md-auto">
                    <button id="viewFormBtn" class="btn btn-secondary">
                        <i class="bi bi-file-earmark-text"></i> Generate PDF
                    </button>
                </div>

                <!-- AREA SELECT -->
                <div class="col-md-4 position-relative">
                    <label class="form-label">Office/Area</label>
                    <input type="text" name="location" id="officeInput"
                          class="form-control" autocomplete="off"
                          placeholder="Search or type office" required>

                    <div id="officeList" class="list-group position-absolute w-100 d-none"
                        style="max-height:200px; overflow-y:auto; z-index:1056"></div>
                </div>

                <!-- MONTH SELECT -->
                <div class="col-md-3">
                    <label class="form-label mb-1">Month & Year</label>
                    <input type="month" id="monthSelect" class="form-control">
                </div>

            </div>

        </div>
    </div>
<div class="table-responsive">

    <!-- FILTER BAR -->


    </div>

    <!-- TABLE (hidden initially) -->
    <table id="pmsTable" class="table table-bordered table-sm" style="display:none;">
        <thead>
            <tr>
                <th rowspan="2" style="text-align: center;">Date and Time</th>
                <th rowspan="2" style="text-align: center;">Computer Label</th>
                <th colspan="8" style="text-align: center;">Check Points</th>
                <th rowspan="2" style="text-align: center;">Remarks</th>
                <th rowspan="2" style="text-align: center;">Performed By</th>
                <th rowspan="2" style="text-align: center;">Noted By</th>
            </tr>
            <tr>
                <th>Keyboard</th>
                <th>Mouse</th>
                <th>Display</th>
                <th>VGA</th>
                <th>HDD</th>
                <th>UPS/AVR</th>
                <th>Connect</th>
                <th>Power</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>



<!-- Add PMS Modal Styled Like A4 Print -->
<div class="modal fade" id="addPmsModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content shadow">

      <!-- HEADER -->
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-clipboard-plus"></i> Add PMS Record
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="<?= base_url('savePms') ?>" method="post">

        <div class="modal-body">

          <!-- BASIC INFO -->
          <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-light fw-bold">Basic Information</div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6  position-relative">
                  <label class="form-label">Office/Area</label>
                    <input type="text" name="location" id="modalOfficeInput"
                          class="form-control" autocomplete="off"
                          placeholder="Search or type office" required>

                    <div id="modalOfficeList" class="list-group position-absolute w-100 d-none"
                        style="max-height:200px; overflow-y:auto; z-index:1056"></div>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Date & Time</label>
                  <input type="datetime-local" name="datetime" class="form-control" required>
                </div>
                <div class="col-md-12">
                  <label class="form-label">Computer Label</label>
                  <input type="text" name="computerlabel" class="form-control" placeholder="Enter PC Name / Label" required>
                </div>
              </div>
            </div>
          </div>

          <!-- CHECKLIST -->
          <div class="card mb-3 shadow-sm border-0">
            <div class="card-header bg-light fw-bold d-flex justify-content-between align-items-center">
              Equipment Checklist
              <div>
                <input type="checkbox" id="selectAll" class="form-check-input me-1">
                <label for="selectAll" class="form-check-label small mb-0">Select All</label>
              </div>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="keyboard" value="1" id="keyboard">
                    <label class="form-check-label" for="keyboard">Keyboard</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="mouse" value="1" id="mouse">
                    <label class="form-check-label" for="mouse">Mouse</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="display" value="1" id="display">
                    <label class="form-check-label" for="display">Display</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="vga" value="1" id="vga">
                    <label class="form-check-label" for="vga">VGA</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="hdd" value="1" id="hdd">
                    <label class="form-check-label" for="hdd">HDD</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="ups" value="1" id="ups">
                    <label class="form-check-label" for="ups">UPS / AVR</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="connect" value="1" id="connect">
                    <label class="form-check-label" for="connect">Network Connection</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-check">
                    <input class="form-check-input checklist" type="checkbox" name="powercables" value="1" id="power">
                    <label class="form-check-label" for="power">Power Cables</label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ADDITIONAL INFO -->
          <div class="card mb-2 shadow-sm border-0">
            <div class="card-header bg-light fw-bold">Additional Details</div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Remarks</label>
                  <input type="text" name="remarks" class="form-control">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Performed By</label>
                  <input type="text" name="performedby" value="<?= session()->get('name'); ?>" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Noted By</label>
                  <input type="text" name="notedby" class="form-control">
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- FOOTER -->
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-save"></i> Save PMS
          </button>
        </div>

      </form>
    </div>
  </div>
</div>




<?php if (session()->getFlashdata('success')): ?>
<script>
$(document).ready(function () {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '<?= session()->getFlashdata('success') ?>',
        timer: 2000,
        showConfirmButton: false
    });
});
</script>
<?php endif; ?>

<script>
      // Select / Deselect all checkboxes
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('.checklist');

  selectAll.addEventListener('change', () => {
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
  });
  
$(document).ready(function () {

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

// Modal search
$('#modalOfficeInput').on('keyup', function () {
    let q = $(this).val();
    if (q.length < 1) {
        $('#modalOfficeList').addClass('d-none');
        return;
    }

    $.get('<?= site_url("/search/ward") ?>', { q }, function (data) {
        let list = '';
        data.forEach(item => {
            list += `<button type="button"
                     class="list-group-item list-group-item-action modal-office-item">
                     ${item.ward}
                     </button>`;
        });

        $('#modalOfficeList').html(list).removeClass('d-none');
    });
});

// Click to select in modal
$(document).on('click', '.modal-office-item', function () {
    $('#modalOfficeInput').val($(this).text().trim());
    $('#modalOfficeList').addClass('d-none');
});

$(document).on('click', function(e) {
    // For modal dropdown
    if (!$(e.target).closest('#modalOfficeInput, #modalOfficeList').length) {
        $('#modalOfficeList').addClass('d-none');
    }
    // For inline page dropdown
    if (!$(e.target).closest('#officeInput, #officeList').length) {
        $('#officeList').addClass('d-none');
    }
});

    let table;

    function loadTable() {
        const ward = $('#officeInput').val();
        const monthYear = $('#monthSelect').val();

        if (!ward || !monthYear) {
            $('#pmsTable').hide();
            return;
        }

        $('#pmsTable').show();

        // Destroy existing table if already initialized
        if ($.fn.DataTable.isDataTable('#pmsTable')) {
            table.destroy();
            $('#pmsTable tbody').empty();
        }

        Swal.fire({
            title: 'Loading...',
            text: 'Fetching data, please wait',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        table = $('#pmsTable').DataTable({
            processing: true,
            ajax: {
                url: "<?= base_url('pmc/data') ?>",
                data: { area: ward, month: monthYear },
                dataSrc: function (json) {
                    Swal.close();
                    return json.data;
                },
                error: function () {
                    Swal.fire('Error', 'Failed to load data', 'error');
                }
            },
            columns: [
                { data: 'datetime', render: d => new Date(d).toLocaleString() },
                { data: 'computerlabel' },
                { data: 'keyboard', render: d => d == 1 ? '✔' : '' },
                { data: 'mouse', render: d => d == 1 ? '✔' : '' },
                { data: 'display', render: d => d == 1 ? '✔' : '' },
                { data: 'vga', render: d => d == 1 ? '✔' : '' },
                { data: 'hdd', render: d => d == 1 ? '✔' : '' },
                { data: 'ups_avr', render: d => d == 1 ? '✔' : '' },
                { data: 'connect', render: d => d == 1 ? '✔' : '' },
                { data: 'powercables', render: d => d == 1 ? '✔' : '' },
                { data: 'remarks' },
                { data: 'performedby' },
                { data: 'notedby' }
            ],
            order: [[0, 'desc']]
        });
    }

    // Trigger table reload when either filter changes
    $('#wardSelect, #monthSelect').on('change', loadTable);

});

$('#viewFormBtn').click(function () {
    const area = $('#officeInput').val();
    const month = $('#monthSelect').val();

    if (!area || !month) {
        Swal.fire('Warning', 'Please select Area and Month first', 'warning');
        return;
    }

    const url = "<?= base_url('pmc/form') ?>?area=" + area + "&month=" + month;
    window.open(url, '_blank');
});
</script>

<?php $this->endSection(); ?>