<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom no-print">
    <div class="container-fluid">
        <nav class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">
            <span class="profile-username">
                <span class="op-7">Office</span>
                <span class="fw-bold"><?= session()->get('location'); ?></span>
            </span>
        </nav>

        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
        <!-- <li class="nav-item">
          <a href="<?= base_url('/message'); ?>" 
            class="d-flex align-items-center justify-content-between">

            <div class="d-flex align-items-center gap-2">
              <i class="fa-solid fa-envelope"></i> |
              <p class="mb-0">Message</p>
            </div>

            <span id="navMessageBadge"
                  class="badge rounded-pill bg-danger"
                  style="display:none; min-width:20px;">
            </span>

          </a>
        </li>     -->


            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <span class="profile-username">
                        <span class="op-7">
                            <?php
                            date_default_timezone_set('Asia/Manila');
                            $hour = date('H');


                            if ($hour < 12) {
                            echo 'Good Morning,';
                            } elseif ($hour < 18) {
                            echo 'Good Afternoon,';
                            } else {
                            echo 'Good Evening,';
                            }
                            ?>
                            </span>
                        <span class="fw-bold"><?= session()->get('name'); ?></span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>

                            <a class="dropdown-item" href="<?= base_url('/profile'); ?>">My Profile</a>
                            <!-- <a class="dropdown-item" href="#">My Balance</a>
                            <a class="dropdown-item" href="#">Inbox</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Account Setting</a>
                            <div class="dropdown-divider"></div> -->
                            <a class="dropdown-item" href="<?= site_url('/logout'); ?>">Logout</a>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    </div>
</nav>