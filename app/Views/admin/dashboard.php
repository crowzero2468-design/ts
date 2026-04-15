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
    left: 19px;
    top: 40px;
    width: 2px;
    height: 100%;
    background: #ddd;
}

/* ❌ REMOVE line for last item */
.timeline-item:last-child::before {
    display: none;
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

<div class="row g-3">

    <!-- Total Users -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #1E90FF;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Total Users Accounts</p>
                    <h4 class="mb-0"><?= $totalUsers ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Admin -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #FF6B6B;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Total Admin Accounts</p>
                    <h4 class="mb-0"><?= $totalAdmin ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Technicians -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #20B2AA;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Active Techs (User,Admin,SuperAdmin)</p>
                    <h4 class="mb-0"><?= $totalTech ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Off Duty -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #000000;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fa-solid fa-user-xmark"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Off Duty</p>
                    <h4 class="mb-0"><?= $offDuty ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Ping -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #32CD32;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fa-solid fa-network-wired"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Avg Ping</p>
                    <h4 class="mb-0"><?= $avgPing ?? 0 ?> ms</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Avg Server Temp -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #6A5ACD;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fa-solid fa-temperature-arrow-down"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Avg Temp</p>
                    <h4 class="mb-0"><?= $avgTemp ?? 0 ?> °C</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- IT Equipment Inspected -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white" style="background-color: #FFA500;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fas fa-desktop"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Inspected</p>
                    <h4 class="mb-0"><?= $totalInspected ?? 0 ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Trouble Done (NEW COLOR) -->
    <div class="col-md-3 col-sm-6">
        <div class="card p-3 shadow-sm text-white"
            style="background: linear-gradient(135deg, #ff512f, #dd2476); color: #fff; min-width: 200px;">
            <div class="d-flex align-items-center">
                <div class="me-3 fs-3">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                </div>
                <div>
                    <p class="mb-1 opacity-75">Completed Troubleshoots</p>
                    <h4 class="mb-0"><?= $totalTroubleshoots ?? 0 ?></h4>
                </div>
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
                    <h5 class="mb-0 fw-bold me-2">Trouble Statistics (Type)</h5>

                    <!-- <span class="badge bg-primary rounded-pill px-3 py-1">
                        <?= $totalTroubleshoots ?? 0 ?>
                    </span> -->
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

                    <!-- <span class="badge bg-success rounded-pill px-3 py-1">
                        <?= $totalTroubleshoots ?? 0 ?>
                    </span> -->
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

    <!-- TIMELINE CARD -->
<div class="col-md-12">
    <div class="card shadow-sm rounded-4 h-100">

        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 fw-bold">Most Active Technicians</h5>
        </div>

        <div class="card-body">
            <div class="row">

                <!-- ================= USER SIDE ================= -->
                <div class="col-md-6 border-end">

                    <h6 class="fw-bold mb-3">BOYS</h6>

                    <div class="timeline" style="max-height: 320px; overflow-y: auto;">

                        <?php foreach ($techActivitiesUser as $tech): ?>

                            <?php
                                $id = $tech->tech_id ?? null;
                                $list = $techTroubleMap[$id] ?? [];
                            ?>

                            <div class="timeline-item d-flex align-items-start justify-content-between mb-4 w-100">

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

                <!-- ================= ADMIN SIDE ================= -->
                <div class="col-md-6">

                    <h6 class="fw-bold mb-3">GIRLS</h6>

                    <div class="timeline" style="max-height: 320px; overflow-y: auto;">

                        <?php foreach ($techActivitiesAdmin as $tech): ?>

                            <?php
                                $id = $tech->tech_id ?? null;
                                $list = $techTroubleMap[$id] ?? [];
                            ?>

                            <div class="timeline-item d-flex align-items-start justify-content-between mb-4 w-100">

                                <div class="d-flex">
                                    <div class="timeline-icon bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        <i class="fa fa-user-shield"></i>
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