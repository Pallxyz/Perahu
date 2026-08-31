<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Nautech') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            transition: background 0.3s ease;
        }

        :root {
            --bg-primary: #f0f2f5;
            --bg-card: #ffffff;
            --text-primary: #1a1a2e;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
            --input-bg: #f9fafb;
            --input-border: #d1d5db;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            --logo-color: #2563eb;
            --google-bg: #ffffff;
            --google-border: #d1d5db;
            --theme-bg: #ffffff;
            --theme-hover: #f3f4f6;
            --dropdown-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --theme-icon-color: #1a1a2e;
        }

        .dark {
            --bg-primary: #0f0f1a;
            --bg-card: #1a1a2e;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --border-color: #374151;
            --input-bg: #2d2d44;
            --input-border: #4b4b6b;
            --shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            --logo-color: #60a5fa;
            --google-bg: #2d2d44;
            --google-border: #4b4b6b;
            --theme-bg: #2d2d44;
            --theme-hover: #3d3d5c;
            --dropdown-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            --theme-icon-color: #f3f4f6;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 600px;
            background: var(--bg-card);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            margin: 20px;
        }

        .left-panel {
            flex: 1;
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--bg-card);
            transition: background 0.3s ease;
        }

        .right-panel {
            flex: 0 0 45%;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d4a7a 50%, #1a365d 100%);
            padding: 50px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .right-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(96, 165, 250, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .logo-icon img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        .logo-text {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .logo-text span {
            color: var(--logo-color);
        }

        .welcome-text {
            margin-bottom: 35px;
        }

        .welcome-text h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }

        .welcome-text p {
            color: var(--text-secondary);
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 6px;
            transition: color 0.3s ease;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--input-border);
            border-radius: 12px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--logo-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .form-group input::placeholder {
            color: var(--text-secondary);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
            font-size: 14px;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--logo-color);
            cursor: pointer;
        }

        .forgot-link {
            color: var(--logo-color);
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--logo-color);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 25px 0;
            color: var(--text-secondary);
            font-size: 13px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }

        .social-buttons {
            display: flex;
            gap: 12px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--google-border);
            border-radius: 12px;
            background: var(--google-bg);
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .register-link a {
            color: var(--logo-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .right-panel h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .right-panel p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .feature-list {
            list-style: none;
            position: relative;
            z-index: 1;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            font-size: 15px;
            opacity: 0.95;
        }

        .feature-list li::before {
            content: '✓';
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            font-size: 14px;
            flex-shrink: 0;
        }

        /* Theme Toggle Styles */
        .theme-wrapper {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .theme-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: var(--theme-bg);
            color: var(--theme-icon-color);
            border: 2px solid var(--border-color);
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            position: relative;
        }

        .theme-toggle:hover {
            border-color: var(--logo-color);
            transform: scale(1.05);
        }

        .theme-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 120px;
            background: var(--theme-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            box-shadow: var(--dropdown-shadow);
            overflow: hidden;
            display: none;
            transition: all 0.3s ease;
        }

        .theme-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .theme-option {
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-primary);
            font-size: 14px;
            font-weight: 500;
        }

        .theme-option:hover {
            background: var(--theme-hover);
        }

        .theme-option.active {
            color: var(--logo-color);
        }

        .theme-option .check {
            margin-left: auto;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .theme-option.active .check {
            opacity: 1;
        }

        .theme-option i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                margin: 10px;
                border-radius: 16px;
            }

            .right-panel {
                flex: 1;
                padding: 30px;
                min-height: 200px;
            }

            .left-panel {
                padding: 30px 25px;
            }

            .right-panel h2 {
                font-size: 24px;
            }

            .theme-wrapper {
                top: 10px;
                right: 10px;
            }

            .theme-toggle {
                width: 38px;
                height: 38px;
                font-size: 17px;
            }
        }
    </style>
</head>

<body>
    <!-- Theme Toggle -->
    <div class="theme-wrapper">
        <div class="theme-toggle" onclick="toggleDropdown()">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
        </div>
        <div class="theme-dropdown" id="themeDropdown">
            <div class="theme-option" data-theme="light" onclick="setTheme('light')">
                <i class="bi bi-sun-fill"></i>
                Light
                <span class="check"><i class="bi bi-check-lg"></i></span>
            </div>
            <div class="theme-option" data-theme="dark" onclick="setTheme('dark')">
                <i class="bi bi-moon-fill"></i>
                Dark
                <span class="check"><i class="bi bi-check-lg"></i></span>
            </div>
            <div class="theme-option" data-theme="auto" onclick="setTheme('auto')">
                <i class="bi bi-circle-half"></i>
                Auto
                <span class="check"><i class="bi bi-check-lg"></i></span>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Left Panel - Login Form -->
        <div class="left-panel">
            <div class="logo-section">
                <div class="logo-icon">
                    <img src="{{ asset('nautech.png') }}" alt="Nautech Logo">
                </div>
                <div class="logo-text">Nau<span>Tech</span></div>
            </div>

            <div class="welcome-text">
                <h1>Welcome Back</h1>
                <p>Glad to see you again! Login to your account</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" placeholder="Type your email"
                        value="{{ old('email') }}" required autofocus autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2"
                        style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;" />
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" placeholder="Type your password" required
                        autocomplete="current-password">
                    <x-input-error :messages="$errors->get('password')" class="mt-2"
                        style="color: #ef4444; font-size: 13px; margin-top: 4px; display: block;" />
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">Log In</button>
            </form>

            <div class="divider">or continue with</div>

<div class="social-buttons">
    <button class="social-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
            <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
            <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
            <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
            <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
        </svg>
        Google
    </button>
    <button class="social-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 50 50">
            <path d="M 44.527344 34.75 C 43.449219 37.144531 42.929688 38.214844 41.542969 40.328125 C 39.601563 43.28125 36.863281 46.96875 33.480469 46.992188 C 30.46875 47.019531 29.691406 45.027344 25.601563 45.0625 C 21.515625 45.082031 20.664063 47.03125 17.648438 47 C 14.261719 46.96875 11.671875 43.648438 9.730469 40.699219 C 4.300781 32.429688 3.726563 22.734375 7.082031 17.578125 C 9.457031 13.921875 13.210938 11.773438 16.738281 11.773438 C 20.332031 11.773438 22.589844 13.746094 25.558594 13.746094 C 28.441406 13.746094 30.195313 11.769531 34.351563 11.769531 C 37.492188 11.769531 40.8125 13.480469 43.1875 16.433594 C 35.421875 20.691406 36.683594 31.78125 44.527344 34.75 Z M 31.195313 8.46875 C 32.707031 6.527344 33.855469 3.789063 33.4375 1 C 30.972656 1.167969 28.089844 2.742188 26.40625 4.78125 C 24.878906 6.640625 23.613281 9.398438 24.105469 12.066406 C 26.796875 12.152344 29.582031 10.546875 31.195313 8.46875 Z"/>
        </svg>
        Apple
    </button>
</div>

            <div class="register-link">
                Don't have an account? <a href="{{ route('register') }}">Register</a>
            </div>
        </div>

        <!-- Right Panel - Info -->
        <div class="right-panel">
            <h2>CONTROL YOUR DOT</h2>
            <p>ANYWHERE. Your trusted guide to seamless exploration. Register now and take the first step towards your
                next adventure.</p>
            <ul class="feature-list">
                <li>Easy booking and management</li>
                <li>Secure and trusted platform</li>
                <li>24/7 customer support</li>
            </ul>
        </div>
    </div>

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('themeDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const wrapper = document.querySelector('.theme-wrapper');
            if (!wrapper.contains(event.target)) {
                document.getElementById('themeDropdown').classList.remove('show');
            }
        });

        function setTheme(theme) {
            const html = document.documentElement;
            const icon = document.getElementById('themeIcon');

            // Remove all theme classes
            html.classList.remove('light', 'dark');

            if (theme === 'light') {
                html.classList.add('light');
                icon.className = 'bi bi-sun-fill';
            } else if (theme === 'dark') {
                html.classList.add('dark');
                icon.className = 'bi bi-moon-fill';
            } else if (theme === 'auto') {
                icon.className = 'bi bi-circle-half';
                // Check system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    html.classList.add('dark');
                } else {
                    html.classList.add('light');
                }
            }

            // Update active state
            document.querySelectorAll('.theme-option').forEach(opt => {
                opt.classList.remove('active');
            });
            document.querySelector(`.theme-option[data-theme="${theme}"]`).classList.add('active');

            // Save preference
            localStorage.setItem('theme', theme);

            // Close dropdown
            document.getElementById('themeDropdown').classList.remove('show');
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'auto';
        setTheme(savedTheme);

        // Listen for system theme changes when in auto mode
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                const currentTheme = localStorage.getItem('theme') || 'auto';
                if (currentTheme === 'auto') {
                    const html = document.documentElement;
                    const icon = document.getElementById('themeIcon');
                    html.classList.remove('light', 'dark');
                    if (e.matches) {
                        html.classList.add('dark');
                        icon.className = 'bi bi-circle-half';
                    } else {
                        html.classList.add('light');
                        icon.className = 'bi bi-circle-half';
                    }
                }
            });
        }
    </script>
</body>

</html>
