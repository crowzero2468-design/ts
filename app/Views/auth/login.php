<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>IHOMP</title>
<link rel="icon" type="image/png" href="<?= base_url('assets/img/logo.png') ?>">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600&display=swap" rel="stylesheet">

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        height: 100vh;
        font-family: 'Orbitron', sans-serif;
        overflow: hidden;
        background: #000;
        color: #0ff;
    }

    canvas {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 0;
    }

    .login-wrapper {
        position: relative;
        z-index: 1;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-box {
        width: 360px;
        padding: 40px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(12px);
        border-radius: 18px;
        box-shadow:
            0 0 25px rgba(0, 255, 255, 0.35),
            inset 0 0 20px rgba(0, 255, 255, 0.15);
        text-align: center;
        animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); }
    }

    h2 {
        margin-bottom: 30px;
        letter-spacing: 3px;
        text-shadow: 0 0 10px #0ff;
    }

    input {
        width: 100%;
        padding: 12px;
        margin: 12px 0;
        background: transparent;
        border: 1px solid #0ff;
        border-radius: 10px;
        color: #0ff;
        outline: none;
        font-size: 14px;
        text-align: center;
        transition: 0.3s;
    }

    input:focus {
        box-shadow: 0 0 12px #0ff;
    }

    button {
        width: 100%;
        padding: 12px;
        margin-top: 20px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(90deg, #00ffff, #0077ff);
        color: #000;
        font-weight: bold;
        letter-spacing: 2px;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        box-shadow: 0 0 25px #0ff;
        transform: scale(1.03);
    }

    .error {
        color: #ff4d4d;
        margin-bottom: 10px;
        text-shadow: 0 0 6px #ff4d4d;
    }

    .footer-text {
        margin-top: 20px;
        font-size: 11px;
        opacity: 0.6;
        letter-spacing: 1px;
    }

    .password-wrapper {
    position: relative;
}

.password-wrapper input {
    padding-right: 40px;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #0ff;
    font-size: 14px;
    opacity: 0.7;
    transition: 0.3s;
}

.toggle-password:hover {
    opacity: 1;
    text-shadow: 0 0 8px #0ff;
}

</style>
</head>
<body>

<canvas id="binary"></canvas>

<div class="login-wrapper">
    <div class="login-box">
        <img src="<?= base_url('assets/img/logo.png') ?>" alt="IHOMP Logo" style="width:100px; margin-bottom:10px;">
        <h2>CVMC IHOMP</h2>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="error"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= base_url('login') ?>">
            <?= csrf_field() ?>

            <input type="text" name="username" placeholder="USERNAME" required>
                <div class="input-group">
            <div class="password-wrapper">
                <input type="password"
                    name="password"
                    id="loginPassword"
                    placeholder="PASSWORD"
                    required>

                <span class="toggle-password" onclick="toggleLoginPassword(this)">
                    👁
                </span>
            </div>


            <button type="submit">LOGIN</button>
        </form>

        <div class="footer-text">
            TROUBLESCOPE V2.0
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('binary');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const letters = '01';
    const fontSize = 14;
    const columns = canvas.width / fontSize;

    const drops = [];
    for (let x = 0; x < columns; x++) {
        drops[x] = Math.random() * canvas.height;
    }

    function draw() {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.08)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        ctx.fillStyle = '#00ffff';
        ctx.font = fontSize + 'px Orbitron';

        for (let i = 0; i < drops.length; i++) {
            const text = letters.charAt(Math.floor(Math.random() * letters.length));
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);

            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                drops[i] = 0;
            }
            drops[i]++;
        }
    }

    setInterval(draw, 33);

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });

function toggleLoginPassword(el) {
    const passwordField = document.getElementById("loginPassword");

    if (passwordField.type === "password") {
        passwordField.type = "text";
        el.textContent = "🙈";
    } else {
        passwordField.type = "password";
        el.textContent = "👁";
    }
}
</script>

</body>
</html>
