<?php 
$current = service('uri')->getSegment(1);
?>

<style>

/* ===============================
   CYBER LOGO HEADER
================================= */

.logo-header {
    display: flex;
    align-items: center;
    background: linear-gradient(180deg, #050810, #02040a);
    border-bottom: 1px solid rgba(0, 255, 255, 0.15);
}

.cyber-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.logo-ring {
    width: 56px;
    height: 56px;
    min-width: 56px;
    min-height: 56px;
    padding: 3px;
    border-radius: 50%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle, #00ffff, #003333);
    box-shadow:
        0 0 14px rgba(0, 255, 255, 0.85),
        inset 0 0 10px rgba(0, 255, 255, 0.6);
    animation: pulse 3s infinite ease-in-out;
}

.logo-ring img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    background: #000;
}

.logo-text {
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.logo-title {
    font-size: 15px;
    font-weight: 600;
    letter-spacing: 2px;
    color: #00ffff;
    text-shadow: 0 0 10px #00ffff;
}

.logo-sub {
    font-size: 11px;
    letter-spacing: 1px;
    color: #7efcff;
    opacity: 0.75;
}

@keyframes pulse {
    0% { box-shadow: 0 0 10px rgba(0,255,255,0.4); }
    50% { box-shadow: 0 0 26px rgba(0,255,255,1); }
    100% { box-shadow: 0 0 10px rgba(0,255,255,0.4); }
}

.cyber-logo:hover .logo-ring {
    box-shadow: 0 0 36px #00ffff;
}

/* ===============================
   ACTIVE SIDEBAR STYLE
================================= */

.nav-item.active > a {
    background: linear-gradient(90deg, rgba(0,255,255,0.15), rgba(0,255,255,0.05));
    border-left: 3px solid #00ffff;
    color: #00ffff !important;
    font-weight: 600;
}

.nav-item.active i {
    color: #00ffff !important;
}

.nav-collapse .active > a {
    color: #00ffff !important;
    font-weight: 600;
}

</style>


<div class="sidebar sidebar-style-2" data-background-color="dark">
  <div class="sidebar-logo">

    <div class="logo-header" data-background-color="dark">
      <a href="/" class="logo cyber-logo">
        <span class="logo-ring">
          <img src="<?= base_url('assets/img/logo.png') ?>" alt="IHOMP" />
        </span>
        <span class="logo-text">
          <span class="logo-title">IHOMP</span>
          <span class="logo-sub">Troublescope</span>
        </span>
      </a>

      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar">
          <i class="gg-menu-right"></i>
        </button>
        <button class="btn btn-toggle sidenav-toggler">
          <i class="gg-menu-left"></i>
        </button>
      </div>
    </div>

  </div>

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">

       <!-- DASHBOARD -->
      <li class="nav-item <?= in_array($current, ['', 'dashboard', 'dash2']) ? 'active submenu' : '' ?>">
          <a data-bs-toggle="collapse"
            href="#dashboard"
            class="<?= in_array($current, ['', 'dashboard', 'dash2']) ? '' : 'collapsed' ?>"
            aria-expanded="<?= in_array($current, ['', 'dashboard', 'dash2']) ? 'true' : 'false' ?>">
              <i class="fas fa-home"></i>
              <p>Dashboard</p>
              <span class="caret"></span>
          </a>

          <div class="collapse <?= in_array($current, ['', 'dashboard', 'dash2']) ? 'show' : '' ?>" id="dashboard">
              <ul class="nav nav-collapse">
                
              <?php if (session('role') == 3): ?>
                      <li class="<?= ($current == 'dash2') ? 'active' : '' ?>">
                          <a href="<?= base_url('/dash2'); ?>">
                              <span class="sub-item">Dashboard</span>
                          </a>
                      </li>
              <?php endif; ?>


                  <li class="<?= ($current == 'dashboard' || $current == '') ? 'active' : '' ?>">
                      <a href="<?= base_url('/dashboard'); ?>">
                          <span class="sub-item">Troubleshoot</span>
                      </a>
                  </li>
                 
              </ul>
          </div>
      </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon">
            <i class="fa fa-ellipsis-h"></i>
          </span>
          <h4 class="text-section">Tools</h4>
        </li>

        <!-- HISTORY -->
        <li class="nav-item <?= $current == 'actlog' ? 'active' : '' ?>">
          <a href="<?= base_url('/actlog'); ?>">
            <i class="fa-solid fa-hourglass-half"></i>
            <p>Troubleshoot History</p>
          </a>
        </li>

        <!-- TECH -->
        <li class="nav-item <?= $current == 'tech' ? 'active' : '' ?>">
          <a href="<?= base_url('/tech'); ?>">
            <i class="fa-solid fa-users"></i>
            <p>Technical's</p>
          </a>
        </li>

        <!-- ONGOING -->
        <li class="nav-item <?= $current == 'ongoing' ? 'active' : '' ?>">
          <a href="<?= base_url('/ongoing'); ?>">
            <i class="fa-solid fa-screwdriver-wrench"></i>
            <p>Ongoing Troubleshoot</p>
          </a>
        </li>

        <!-- MESSAGE -->
        <!-- <li class="nav-item <?= $current == 'message' ? 'active' : '' ?>">
          <a href="<?= base_url('/message'); ?>" 
            class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-envelope"></i>
              <p class="mb-0">Message</p>
            </div>

            <span id="navMessageBadge"
                  class="badge rounded-pill bg-danger"
                  style="display:none; min-width:20px;">
            </span>

          </a>
        </li> -->

        <!-- <li class="nav-item <?= $current == 'sched' ? 'active' : '' ?>">
          <a href="<?= base_url('/sched'); ?>">
            <i class="fa-solid fa-calendar"></i>
            <p>Schedule</p>
          </a>
        </li> -->

        <!-- IT Equipment -->
        <!-- <li class="nav-item <?= $current == 'equip' ? 'active' : '' ?>">
          <a href="<?= base_url('/equip'); ?>">
            <i class="fa-solid fa-laptop"></i>
            <p>IT Equipment</p>
          </a>
        </li> -->

        <!-- IT Equipment -->
        <!-- <li class="nav-item <?= $current == 'pmc' ? 'active' : '' ?>">
          <a href="<?= base_url('/pmc'); ?>">
            <i class="fa-solid fa-laptop"></i>
            <p>IT Equipment PMS</p>
          </a>
        </li> -->

        <!-- IT EQUIPMENT GROUP -->
        <li class="nav-item <?= ($current == 'equip' || $current == 'pmc' || $current == 'temp' || $current == 'speedtest') ? 'active submenu' : '' ?>">
          <a data-bs-toggle="collapse"
            href="#itEquipment"
            class="<?= ($current == 'equip' || $current == 'pmc' || $current == 'temp' || $current == 'speedtest') ? '' : 'collapsed' ?>"
            aria-expanded="<?= ($current == 'equip' || $current == 'pmc' || $current == 'temp' || $current == 'speedtest') ? 'true' : 'false' ?>">

            <i class="fa-solid fa-laptop"></i>
            <p>Monitoring and Inspection</p>
            <span class="caret"></span>
          </a>

          <div class="collapse <?= ($current == 'equip' || $current == 'pmc' || $current == 'temp' || $current == 'speedtest') ? 'show' : '' ?>" id="itEquipment">
            <ul class="nav nav-collapse">
              <?php if (session('role') == 3): ?>
              <li class="<?= $current == 'equip' ? 'active' : '' ?>">
                <a href="<?= base_url('/equip'); ?>">
                  <span class="sub-item">Equipment List</span>
                </a>
              </li>
              <?php endif; ?>

              <li class="<?= $current == 'pmc' ? 'active' : '' ?>">
                <a href="<?= base_url('/pmc'); ?>">
                  <span class="sub-item">PMS Monitoring</span>
                </a>
              </li>

              <li class="<?= $current == 'temp' ? 'active' : '' ?>">
                <a href="<?= base_url('/temp'); ?>">
                  <span class="sub-item">Server Temperature</span>
                </a>
              </li>

              <li class="<?= $current == 'speedtest' ? 'active' : '' ?>">
                <a href="<?= base_url('/speedtest'); ?>">
                  <span class="sub-item">Speed Test</span>
                </a>
              </li>

            </ul>
          </div>
        </li>

      </ul>
    </div>
  </div>
</div>
