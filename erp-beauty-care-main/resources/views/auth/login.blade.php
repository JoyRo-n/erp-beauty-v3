<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — ERP PT Arunika Beauty Nusantara (NusaGlow)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --odoo-primary: #714B67;
            --odoo-dark: #5A3C52;
            --odoo-bg: #F0F2F5;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--odoo-bg);
        }
        .odoo-bg-primary {
            background-color: var(--odoo-primary);
        }
        .odoo-btn-primary {
            background-color: var(--odoo-primary);
            color: white;
        }
        .odoo-btn-primary:hover {
            background-color: var(--odoo-dark);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-gray-800">

    <!-- Top Navigation Header -->
    <header class="odoo-bg-primary text-white h-12 flex items-center justify-between px-6 shadow">
        <div class="flex items-center space-x-3">
            <span class="w-7 h-7 rounded bg-white/20 flex items-center justify-center font-bold text-sm">
                <i class="fa-solid fa-gem text-pink-200"></i>
            </span>
            <span class="font-bold text-sm tracking-wide">PT Arunika Beauty Nusantara</span>
            <span class="text-xs text-purple-200 opacity-80 hidden sm:inline">&bull; NusaGlow ERP Web App</span>
        </div>
        <div class="text-xs text-purple-100 flex items-center space-x-2">
            <i class="fa-solid fa-lock text-xs"></i>
            <span class="font-semibold">SR-19 Access Control & Security</span>
        </div>
    </header>

    <!-- Main Container: Centered Clean Professional Card -->
    <main class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-purple-800 to-indigo-800 p-7 text-white text-center relative">
                <div class="w-16 h-16 mx-auto rounded-full bg-white/10 backdrop-blur flex items-center justify-center text-3xl shadow-inner mb-3">
                    <i class="fa-solid fa-shield-halved text-pink-300"></i>
                </div>
                <h1 class="text-xl font-bold tracking-tight">Portal Masuk Karyawan</h1>
                <p class="text-xs text-purple-200 mt-1">Gunakan email & kata sandi resmi untuk membuka akses modul</p>
            </div>

            <!-- Form Content -->
            <div class="p-8">
                <!-- Session Flash Alerts -->
                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <span class="font-bold">Akses Ditolak:</span> {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-5 p-3.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-lock text-amber-600 mt-0.5 flex-shrink-0"></i>
                        <div>{{ session('warning') }}</div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-5 p-3.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 flex-shrink-0"></i>
                        <div>{{ session('info') }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-start space-x-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            <i class="fa-solid fa-envelope text-gray-400 mr-1.5"></i> Email Karyawan
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="login-email" 
                            value="{{ old('email', $prefillEmail ?? '') }}" 
                            required 
                            autofocus
                            placeholder="warehouse@gmail.com" 
                            class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition bg-gray-50 focus:bg-white"
                        >
                        @error('email')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            <i class="fa-solid fa-key text-gray-400 mr-1.5"></i> Kata Sandi (Password)
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="login-password" 
                                required 
                                placeholder="Masukkan kata sandi akun" 
                                class="w-full text-sm px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-600 focus:border-purple-600 outline-none transition bg-gray-50 focus:bg-white pr-10"
                            >
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1">
                                <i class="fa-solid fa-eye text-xs" id="pwd-eye-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between text-xs text-gray-600 pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <span>Ingat sesi saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        id="login-submit-btn" 
                        class="w-full odoo-btn-primary font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2 text-sm mt-2"
                    >
                        <i class="fa-solid fa-right-to-bracket text-sm"></i>
                        <span>Buka Kunci & Masuk Dashboard</span>
                    </button>
                </form>
            </div>

            <!-- Card Footer -->
            <div class="p-4 bg-gray-50 border-t border-gray-100 text-center text-xs text-gray-500">
                <i class="fa-solid fa-lock text-emerald-600 mr-1"></i>
                Setiap admin wajib mengetahui email dan kata sandi akun pribadinya
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-3 text-center text-xs text-gray-400">
        &copy; 2026 PT Arunika Beauty Nusantara (NusaGlow). All rights reserved.
    </footer>

    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('login-password');
            const eyeIcon = document.getElementById('pwd-eye-icon');
            if (pwdInput && eyeIcon) {
                if (pwdInput.type === 'password') {
                    pwdInput.type = 'text';
                    eyeIcon.classList.remove('fa-eye');
                    eyeIcon.classList.add('fa-eye-slash');
                } else {
                    pwdInput.type = 'password';
                    eyeIcon.classList.remove('fa-eye-slash');
                    eyeIcon.classList.add('fa-eye');
                }
            }
        }
    </script>
</body>
</html>
