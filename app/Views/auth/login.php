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

        <div style="margin-top:10px;">
            <a href="#" onclick="openForgotModal()" style="color:#0ff; font-size:12px;">
                Forgot Password?
            </a>
        </div>

        <div class="footer-text">
            TROUBLESCOPE V2.0
        </div>
    </div>
</div>


<div id="forgotModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999; justify-content:center; align-items:center;">

    <div style="width:350px; padding:30px; background:#000; border-radius:15px; box-shadow:0 0 20px #0ff; text-align:center;">

        <h3 style="margin-bottom:15px;">Forgot Password</h3>
            <div id="fp_error" class="error" style="display:none;"></div>
        <!-- STEP 1 -->
        <div id="step1">
            <input type="text" id="fp_identity" placeholder="Username or Full Name">
            <button onclick="checkUser()">VERIFY</button>
        </div>

        <!-- STEP 2 -->
        <div id="step2" style="display:none;">
            <input type="password" id="new_password" placeholder="New Password">
            <input type="password" id="confirm_password" placeholder="Confirm Password">
            <button onclick="updatePassword()">UPDATE PASSWORD</button>
        </div>

        <button onclick="closeForgotModal()" style="margin-top:10px; background:#222; color:#0ff;">CLOSE</button>

    </div>
</div>


<script>
    const canvas = document.getElementById('binary');
    const ctx = canvas.getContext('2d');

    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const letters = '01';
    const fontSize = 14;
    let columns = canvas.width / fontSize;

    let drops = [];
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

        columns = canvas.width / fontSize;
        drops = [];

        for (let x = 0; x < columns; x++) {
            drops[x] = Math.random() * canvas.height;
        }
    });

    // TOGGLE PASSWORD
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

    // ERROR HANDLING (same style as login)
    function showFpError(message, type = 'error') {
        const el = document.getElementById('fp_error');
        el.style.display = 'block';
        el.innerText = message;

        if (type === 'success') {
            el.style.color = '#00ff99';
            el.style.textShadow = '0 0 6px #00ff99';
        } else {
            el.style.color = '#ff4d4d';
            el.style.textShadow = '0 0 6px #ff4d4d';
        }
    }

    function clearFpError() {
        const el = document.getElementById('fp_error');
        el.style.display = 'none';
        el.innerText = '';
    }

    // MODAL CONTROL
    function openForgotModal() {
        document.getElementById('forgotModal').style.display = 'flex';
        clearFpError();
    }

    function closeForgotModal() {
        document.getElementById('forgotModal').style.display = 'none';

        // reset all fields
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('fp_identity').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('confirm_password').value = '';
        clearFpError();
    }

    // STEP 1: VERIFY USER
    function checkUser() {
        let identity = document.getElementById('fp_identity').value.trim();

        clearFpError();

        if (!identity) {
            showFpError("Please enter username or full name");
            return;
        }

        fetch("<?= base_url('forgot/checkUser') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "<?= csrf_hash() ?>"
            },
            body: JSON.stringify({ identity: identity })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
            } else {
                showFpError("User not found");
            }
        })
        .catch(() => {
            showFpError("Something went wrong");
        });
    }

    // STEP 2: UPDATE PASSWORD
    function updatePassword() {
        let identity = document.getElementById('fp_identity').value;
        let password = document.getElementById('new_password').value;
        let confirm = document.getElementById('confirm_password').value;

        clearFpError();

        if (!password || !confirm) {
            showFpError("Please fill all fields");
            return;
        }

        if (password.length < 6) {
            showFpError("Password must be at least 6 characters");
            return;
        }

        if (password !== confirm) {
            showFpError("Passwords do not match");
            return;
        }

        fetch("<?= base_url('forgot/updatePassword') ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "<?= csrf_hash() ?>"
            },
            body: JSON.stringify({
                identity: identity,
                password: password
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                showFpError("Password updated successfully!", "success");

                setTimeout(() => {
                    closeForgotModal();
                }, 1500);
            } else {
                showFpError("Error updating password");
            }
        })
        .catch(() => {
            showFpError("Something went wrong");
        });
    }
</script>

</body>
</html>
