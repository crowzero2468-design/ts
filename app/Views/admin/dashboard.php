<?php
$this->extend('layout/main');
$this->section('body');
?>

<style>
.timeline { position: relative; padding-left: 5px; }

/* Each item */
.timeline-item {
    position: relative;
    padding-bottom: 20px;
    display: flex;
}


/* Icon */
.timeline-icon {
    width: 40px;
    height: 40px;
    font-size: 14px;
    flex-shrink: 0;
}

/* REMOVE ANY VERTICAL TIMELINE LINE */
.timeline::before {
    display: none !important;
    content: none !important;
}

.timeline {
    border-left: none !important;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 19px; /* aligns with icon center */
    top: 40px;  /* starts below icon */
    width: 2px;
    height: calc(100% - 20px);
    background: #ddd;
}

</style>

          <div class="page-inner">
            <div
              class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4"
            >
              <div>
                <h3 class="fw-bold mb-3">Main Dashboard</h3>
              </div>
            </div>

<div class="d-flex flex-row flex-wrap gap-3 justify-content-between">

    <!-- Total Users -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #1E90FF; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Total Users</p>
                <h4 class="mb-0"><?= isset($totalUsers) ? $totalUsers : 0 ?></h4>
            </div>
        </div>
    </div>

    <!-- Total Admin -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #FF6B6B; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Total Admin Accounts</p>
                <h4 class="mb-0"><?= isset($totalAdmin) ? $totalAdmin : 0 ?></h4>
            </div>
        </div>
    </div>

    <!-- Active Technicians -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #20B2AA; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fa-solid fa-user-gear"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Active Technicians</p>
                <h4 class="mb-0"><?= isset($totalTech) ? $totalTech : 0 ?></h4>
            </div>
        </div>
    </div>

    <!-- Off Duty -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #4DA6FF; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Off Duty</p>
                <h4 class="mb-0"><?= isset($offDuty) ? $offDuty : 0 ?></h4>
            </div>
        </div>
    </div>

    <!-- Average Ping -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #32CD32; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fa-solid fa-network-wired"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Average Ping</p>
                <h4 class="mb-0"><?= isset($avgPing) ? $avgPing . ' ms' : '0 ms' ?></h4>
            </div>
        </div>
    </div>

    <!-- Avg Server Temp -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #6A5ACD; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fa-solid fa-temperature-arrow-down"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">Avg Server Temp</p>
                <h4 class="mb-0"><?= isset($avgTemp) ? $avgTemp . ' °C' : '0 °C' ?></h4>
            </div>
        </div>
    </div>

    <!-- IT Equipment Inspected -->
    <div class="card flex-fill p-3 shadow-sm" style="background-color: #FFA500; color: #fff; min-width: 180px;">
        <div class="d-flex align-items-center">
            <div class="me-3" style="font-size: 2rem;">
                <i class="fas fa-desktop"></i>
            </div>
            <div>
                <p class="mb-1" style="opacity: 0.8;">IT Equipment Inspected</p>
                <h4 class="mb-0"><?= isset($totalInspected) ? $totalInspected : 0 ?></h4>
            </div>
        </div>
    </div>

</div>



<div class="row g-4">

    <!-- LEFT: LINE CHART -->
    <div class="col-md-6">
        <div class="card shadow-sm rounded-4 h-100">

            <!-- Header -->
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center">
                    <h5 class="mb-0 fw-bold me-2">Trouble Statistics (Line)</h5>

                    <span class="badge bg-primary rounded-pill px-3 py-1">
                        <?= $totalTroubleshoots ?? 0 ?>
                    </span>
                </div>

                <small class="text-muted">
                    <?= $startDate ?> - <?= $endDate ?>
                </small>
            </div>

            <!-- Body -->
            <div class="card-body p-3">
                <div style="min-height: 380px;">
                    <canvas id="statisticsChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <!-- RIGHT: BAR CHART -->
    <div class="col-md-6">
        <div class="card shadow-sm rounded-4 h-100">

            <!-- Header -->
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center">
                    <h5 class="mb-0 fw-bold me-2">Most Frequent Trouble Statistics (10+)</h5>

                    <span class="badge bg-success rounded-pill px-3 py-1">
                        <?= $totalTroubleshoots ?? 0 ?>
                    </span>
                </div>

                <small class="text-muted">
                    <?= $startDate ?> - <?= $endDate ?>
                </small>
            </div>

            <!-- Body -->
            <div class="card-body p-3">
                <div style="min-height: 380px;">
                    <canvas id="statisticsBarChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <div class="row g-4 mt-1">

    <!-- TIMELINE CARD -->
<div class="col-md-6">
    <div class="card shadow-sm rounded-4 h-100">

        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">Most Active Technicians</h5>
        </div>

        <div class="card-body">

            <!-- SCROLL CONTAINER -->
            <div class="timeline" style="max-height: 320px; overflow-y: auto;">

                <?php foreach ($techActivities as $tech): ?>

                    <?php
                        // ✅ FIX: use ID (matches updated controller)
                        $id = $tech->tech_id ?? $tech->person ?? null;

                        $list = $techTroubleMap[$id] ?? [];
                    ?>

                    <div class="timeline-item d-flex align-items-start justify-content-between mb-4 w-100">

                        <!-- LEFT SIDE -->
                        <div class="d-flex">

                            <div class="timeline-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fa fa-user"></i>
                            </div>

                            <div>
                                <div class="fw-bold">
                                    <?= strtoupper($tech->name ?? 'Unknown') ?>
                                </div>

                                <div>
                                    Handled <?= $tech->total ?> trouble(s)
                                </div>
                            </div>

                        </div>

                              <!-- RIGHT SIDE (LIST) -->
                              <div class="text-end" style="max-width: 45%;">

                                  <?php if (!empty($list)): ?>

                                      <?php foreach ($list as $item): ?>
                                          <span class="badge bg-secondary me-1 mb-1">
                                              <?= esc($item) ?>
                                          </span>
                                      <?php endforeach; ?>

                                  <?php else: ?>
                                      <span class="text-muted small">No breakdown data</span>
                                  <?php endif; ?>

                              </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>
    </div>
</div>

</div>




<script>
$(document).ready(function() {

    // ======================
    // LINE CHART (ts_type - unchanged)
    // ======================
    const labels = <?= json_encode($troubleLabels) ?>;
    const data   = <?= json_encode($troubleData) ?>;

    const lineCtx = document.getElementById('statisticsChart').getContext('2d');

    new Chart(lineCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Responded Troubleshoots',
                data: data,
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.3,
                pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                pointBorderColor: '#fff',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Trouble Type'
                    }
                }
            }
        }
    });

    // ======================
    // BAR CHART (description > 1 ONLY)
    // ======================
    const barLabels = <?= json_encode($barLabels) ?>;
    const barData   = <?= json_encode($barData) ?>;

    const barCtx = document.getElementById('statisticsBarChart').getContext('2d');

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: barLabels,
            datasets: [{
                label: 'Repeated Trouble Descriptions',
                data: barData,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Description (Repeated Only)'
                    }
                }
            }
        }
    });

});
</script>


<?php $this->endSection(); ?>