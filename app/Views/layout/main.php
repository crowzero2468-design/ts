<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="/">

    <script src="<?= base_url("assets/js/core/jquery-3.7.1.min.js"); ?>"></script>

    <!-- Fonts and icons -->
    <script src="<?= base_url("assets/js/plugin/webfont/webfont.min.js"); ?>"></script>
    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["../assets/css/fonts.min.css"],
            },
            active: function() {
                sessionStorage.fonts = true;
            },
        });
    </script>
    <!-- CSS -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> -->

    <!-- <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet"> -->

    <link rel="stylesheet" href="<?= base_url("assets/css/bootstrap.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/plugins.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/kaiadmin.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("fontawesome/css/all.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/select2.min.css"); ?>">
    <link rel="stylesheet" href="<?= base_url("assets/css/select2-bootstrap-5-theme.min.css"); ?>">

    <!-- DataTables Buttons -->
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" /> -->
    <link rel="stylesheet" href="<?= base_url('assets/css/buttons.dataTables.min.css') ?>" />
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/logo.png') ?>">

    <!-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet"> -->
<script src="<?= base_url('assets/fullcalendar/dist/index.global.min.js') ?>"></script>


    <title>Troublescope</title>
</head>


<body>

    <div class="wrapper">


        <div class="wrapper">
            
                <?= $this->include('layout/sidebar'); ?>


                <div class="main-panel">
                    <div class="main-header">
                        <?= $this->include('layout/mainHeaderLogo'); ?>
                        <?= $this->include('layout/navbar'); ?>
                    </div>
                <div class="container">
                    <div class="page-inner">
                        <div class="page-content">
                            <?= $this->renderSection("body") ?>
                        </div>
                    </div>
                </div>
                
        </div>

        <!--   Core JS Files   -->

        <!-- 
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script> -->

        <script src="<?= base_url("assets/js/core/popper.min.js"); ?>"></script>
        <script src="<?= base_url("assets/js/core/bootstrap.min.js"); ?>"></script>
        <!-- jQuery Scrollbar -->
        <script src="<?= base_url("assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"); ?>"></script>
        <!-- Chart JS -->

        <!-- jQuery Sparkline -->
        <script src="<?= base_url("assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"); ?>"></script>
        <!-- Chart Circle -->
        <script src="<?= base_url("assets/js/plugin/chart-circle/circles.min.js"); ?>"></script>
        <!-- Datatables -->
        <script src="<?= base_url("assets/js/plugin/datatables/datatables.min.js"); ?>"></script>
        <!-- Bootstrap Notify -->
        <script src="<?= base_url("assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"); ?>"></script>
        <!-- Kaiadmin JS -->
        <script src="<?= base_url("assets/js/kaiadmin.min.js"); ?>"></script>
        <script src="<?= base_url('assets/js/xlsx.full.min.js') ?>"></script>

        <!-- <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script> -->
        <!-- <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script> -->
        <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> -->

        <script src="<?= base_url('assets/js/dataTables.buttons.min.js') ?>"></script>
        <script src="<?= base_url('assets/js/buttons.html5.min.js') ?>"></script>
        <script src="<?= base_url('assets/js/jszip.min.js') ?>"></script>
        <script src="<?= base_url('assets/js/html2pdf.bundle.min.js'); ?>"></script>
        <script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>"></script>
        <script src="<?= base_url('assets/js/chart.min.js') ?>"></script>
        <script src="<?= base_url('assets/js/chartjs-plugin-datalabels.min.js') ?>"></script>
        <script src="<?= base_url("assets/js/select2.min.js")?>"></script>
        

        <script>
// Dark Mode Toggle Functionality
$(document).ready(function() {
    // Check for saved theme preference or default to light mode
    const currentTheme = localStorage.getItem('theme') || 'light';
    const toggleButton = $('#darkModeToggle');
    const toggleIcon = toggleButton.find('i');

    // Apply the current theme
    if (currentTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
        toggleIcon.removeClass('fa-moon').addClass('fa-sun');
    } else {
        document.documentElement.removeAttribute('data-theme');
        toggleIcon.removeClass('fa-sun').addClass('fa-moon');
    }

    // Toggle theme on button click
    toggleButton.on('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        if (newTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            toggleIcon.removeClass('fa-moon').addClass('fa-sun');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            toggleIcon.removeClass('fa-sun').addClass('fa-moon');
            localStorage.setItem('theme', 'light');
        }
    });
});
</script>

<script>

// Fade in the page content on load
$(document).ready(function() {
    $('.page-content').css('opacity', '1');
});

// Fade out on logout
$(document).on('click', 'a[href*="logout"]', function(e) {
    e.preventDefault();
    $('.page-content').fadeOut(500, function() {
        window.location.href = e.target.href;
    });
});
</script>

<!-- <audio id="messageSound" preload="auto">
    <source src="<?= base_url('assets/sounds/message.mp3') ?>" type="audio/mpeg">
</audio> -->

</body>


<!-- <script>
let lastTotal = null;
let userInteracted = false;

document.addEventListener("click", function () {
    userInteracted = true;
}, { once: true });

function loadNavUnread() {
    fetch("<?= base_url('message/unreadTotal') ?>")
        .then(res => res.json())
        .then(data => {

            let badge = document.getElementById("navMessageBadge");
            if (!badge) return;

            if (data.total > 0) {

                badge.innerText = data.total;
                badge.style.display = "inline-block";

                // 🔔 Only play AFTER first load
                if (lastTotal !== null && data.total > lastTotal && userInteracted) {

                    let audio = document.getElementById("messageSound");
                    if (audio) {
                        audio.currentTime = 0;
                        audio.volume = 0.4;
                        audio.play().catch(() => {});
                    }
                }

            } else {
                badge.style.display = "none";
            }

            lastTotal = data.total;

        })
        .catch(err => console.log("Nav unread error:", err));
}

setInterval(loadNavUnread, 60000);
loadNavUnread();

</script> -->



</html>