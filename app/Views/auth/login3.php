
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Futuristic Login</title>

<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Roboto', sans-serif;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Matrix rain effect */
        .matrix-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.1;
        }

        .matrix-bg canvas {
            display: block;
        }

        .login-container {
            background: rgba(15, 15, 35, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 3rem 2.5rem;
            border-radius: 16px;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
            animation: secureSlideIn 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        @keyframes secureSlideIn {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .security-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: linear-gradient(45deg, #00ff88, #00cc6a);
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .it-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
            position: relative;
        }

        .it-logo::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.3), transparent);
            border-radius: 18px;
            z-index: -1;
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .it-logo i {
            font-size: 32px;
            color: white;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #00d4ff, #ffffff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #a0a0c0;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 2rem;
            padding: 0.75rem 1rem;
            background: rgba(0, 212, 255, 0.1);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 10px;
            font-size: 13px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #00ff88;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .form-group {
            position: relative;
            margin-bottom: 1.75rem;
        }

        .form-group label {
            position: absolute;
            left: 16px;
            top: 16px;
            color: #a0a0c0;
            font-size: 14px;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .form-group input:focus + label,
        .form-group input:valid + label {
            top: -8px;
            left: 12px;
            font-size: 12px;
            color: #00d4ff;
            background: rgba(15, 15, 35, 0.95);
            padding: 0 6px;
        }

        .form-group input {
            width: 100%;
            padding: 18px 16px 18px 50px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-group input::placeholder {
            color: transparent;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 4px rgba(0, 212, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0a0c0;
            font-size: 18px;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .form-group:focus-within .form-icon {
            color: #00d4ff;
        }

        .auth-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .auth-option {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #a0a0c0;
            font-size: 13px;
        }

        .auth-option:hover,
        .auth-option.active {
            background: rgba(0, 212, 255, 0.1);
            border-color: #00d4ff;
            color: #00d4ff;
        }

        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: #000;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 212, 255, 0.4);
        }

        .login-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .security-notice {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 13px;
            color: #00ff88;
            text-align: center;
        }

        .error-message {
            background: rgba(255, 68, 68, 0.2);
            border: 1px solid rgba(255, 68, 68, 0.5);
            color: #ff6b6b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: none;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="particles" id="particles"></div>
    
    <div class="login-container">
        <div class="error-message" id="errorMessage"></div>
        
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to your account</p>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <i class="fas fa-envelope form-icon"></i>
                <input type="email" id="email" placeholder="Email address" required>
            </div>

            <div class="form-group">
                <i class="fas fa-lock form-icon"></i>
                <input type="password" id="password" placeholder="Password" required>
            </div>
<!-- 
            <div class="forgot-password">
                <a href="#" id="forgotPassword">Forgot password?</a>
            </div> -->

            <button type="submit" class="login-btn" id="loginBtn">
                Sign In
            </button>
        </form>

        <!-- <div class="divider">
            <span>or continue with</span>
        </div>

        <div class="social-login">
            <a href="#" class="social-btn">
                <i class="fab fa-google"></i>
                Google
            </a>
            <a href="#" class="social-btn">
                <i class="fab fa-apple"></i>
                Apple
            </a>
        </div>

        <div class="register-link">
            <p>Don't have an account? <a href="#">Sign up here</a></p>
        </div> -->
    </div>

    <script>
        // Particle background effect
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                particle.style.width = particle.style.height = (Math.random() * 10 + 5) + 'px';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 4) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Form handling
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginBtn = document.getElementById('loginBtn');
            const errorMessage = document.getElementById('errorMessage');

            // Show loading state
            loginBtn.classList.add('loading');
            loginBtn.textContent = '';
            errorMessage.style.display = 'none';

            // Simulate API call
            setTimeout(() => {
                loginBtn.classList.remove('loading');
                loginBtn.textContent = 'Sign In';

                // Simple validation (replace with real API call)
                if (email === '' || password === '') {
                    errorMessage.textContent = 'Please fill in all fields.';
                    errorMessage.style.display = 'block';
                    return;
                }

                if (password.length < 6) {
                    errorMessage.textContent = 'Password must be at least 6 characters.';
                    errorMessage.style.display = 'block';
                    return;
                }

                // Success simulation
                alert('Login successful! (This is a demo)');
                // In real app: redirect to dashboard
                // window.location.href = '/dashboard';
            }, 2000);
        });

        // Forgot password
        document.getElementById('forgotPassword').addEventListener('click', function(e) {
            e.preventDefault();
            alert('Forgot password functionality would open a modal or redirect to reset page.');
        });

        // Initialize
        createParticles();
    </script>
</body>
</html>