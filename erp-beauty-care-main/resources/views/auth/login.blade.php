<!DOCTYPE html>
@php
    $isEnLogin = app()->getLocale() === 'en';
@endphp
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isEnLogin ? 'Sign In' : 'Masuk' }} — ERP PT Arunika Beauty Nusantara (NusaGlow)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --odoo-primary: #E879A6;
            --odoo-dark: #D65C8C;
            --odoo-bg: #FFFBFD;
        }
        html {
            font-size: clamp(13.5px, 0.32vw + 11.8px, 18px);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: linear-gradient(180deg, #FFF4F8 0%, #FFFBFD 45%, #FFF2F7 100%);
            overflow-x: hidden;
        }
        .odoo-bg-primary {
            background: linear-gradient(90deg, #FFF0F5 0%, #FADADD 50%, #F8C8DC 100%);
            border-bottom: 1px solid #F2B6CE;
            box-shadow: 0 3px 12px -2px rgba(232, 121, 166, 0.18);
        }
        .odoo-btn-primary {
            background: linear-gradient(90deg, #EC709F 0%, #D95386 100%);
            color: white;
        }
        .odoo-btn-primary:hover {
            background: linear-gradient(90deg, #DF5D8E 0%, #C74174 100%);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between text-gray-800">

    <!-- Top Navigation Header -->
    <header class="odoo-bg-primary text-rose-950 h-14 flex items-center justify-between px-6">
        <div class="flex items-center space-x-3">
            <span class="w-8 h-8 rounded-lg bg-white/80 border border-pink-200 flex items-center justify-center font-bold text-sm shadow-sm">
                <i class="fa-solid fa-spa text-rose-500"></i>
            </span>
            <span class="font-bold text-sm tracking-wide text-rose-950">PT Arunika Beauty Nusantara</span>
            <span class="text-xs text-rose-700/80 hidden sm:inline">&bull; NusaGlow ERP Web App</span>
        </div>
        <div class="text-xs text-rose-900 flex items-center space-x-3">
            @if(!empty($currentUser))
                <a href="{{ $returnUrl ?? route('s01.home') }}" class="bg-rose-500 hover:bg-rose-600 text-white font-bold px-3 py-1.5 rounded-lg shadow-sm transition flex items-center space-x-1.5">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>{{ $isEnLogin ? 'Return to ' : 'Kembali ke ' }}{{ $currentUser->role }}</span>
                </a>
            @endif
            <!-- Language Switcher (ID / EN) -->
            <form action="{{ route('locale.switch') }}" method="POST" class="inline-flex items-center bg-white/80 border border-pink-200 rounded-lg p-0.5 shadow-sm">
                @csrf
                <button
                    type="submit"
                    name="locale"
                    value="id"
                    class="px-2 py-1 rounded-md text-[11px] font-bold transition {{ !$isEnLogin ? 'bg-gradient-to-r from-[#EC709F] to-[#D95386] text-white shadow-xs' : 'text-rose-700 hover:bg-pink-50' }}"
                    title="Bahasa Indonesia"
                >
                    ID
                </button>
                <button
                    type="submit"
                    name="locale"
                    value="en"
                    class="px-2 py-1 rounded-md text-[11px] font-bold transition {{ $isEnLogin ? 'bg-gradient-to-r from-[#EC709F] to-[#D95386] text-white shadow-xs' : 'text-rose-700 hover:bg-pink-50' }}"
                    title="English"
                >
                    EN
                </button>
            </form>
            <div class="hidden sm:flex items-center space-x-2 bg-white/75 border border-pink-200 px-3 py-1 rounded-full shadow-sm">
                <i class="fa-solid fa-lock text-xs text-rose-500"></i>
                <span class="font-semibold text-rose-900">Access Control & Security</span>
            </div>
        </div>
    </header>

    <!-- Main Container: Centered Clean Professional Card -->
    <main class="flex-1 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#F2B6CE] overflow-hidden">
            
            <!-- Card Header -->
            <div class="bg-gradient-to-br from-[#FFF5F8] via-[#FADADD] to-[#F4B8CE] p-7 text-rose-950 text-center relative border-b border-[#EDA4C0]">
                <div class="w-16 h-16 mx-auto rounded-full bg-white/85 border border-pink-200 backdrop-blur flex items-center justify-center text-3xl shadow-sm mb-3">
                    <i class="fa-solid fa-shield-halved text-rose-500"></i>
                </div>
                <h1 class="text-xl font-bold tracking-tight text-rose-950">{{ $isEnLogin ? 'Employee Sign-In Portal' : 'Portal Masuk Karyawan' }}</h1>
                <p class="text-xs text-rose-800/85 mt-1">{{ $isEnLogin ? 'Use your official email & password to unlock module access' : 'Gunakan email & kata sandi resmi untuk membuka akses modul' }}</p>
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

                @if(!empty($currentUser))
                    <div class="mb-5 p-3.5 rounded-xl bg-gradient-to-r from-[#FFF0F5] to-[#FADADD]/75 border border-[#F2B6CE] text-rose-950 text-xs flex items-center justify-between gap-3 shadow-xs">
                        <div>
                            <div class="font-bold flex items-center space-x-1.5">
                                <i class="fa-solid fa-user-shield text-rose-600"></i>
                                <span>Mode Inspeksi: {{ $currentUser->name }} ({{ $currentUser->role }})</span>
                            </div>
                            <div class="text-[11px] text-rose-700/90 mt-0.5">Salah pilih akun staf? Kembali tanpa mengakhiri sesi {{ $currentUser->role }} Anda.</div>
                        </div>
                        <a href="{{ $returnUrl ?? route('s01.home') }}" class="flex-shrink-0 bg-gradient-to-r from-[#EC709F] to-[#D95386] hover:from-[#DF5D8E] hover:to-[#C74174] text-white font-bold px-3 py-2 rounded-lg transition flex items-center space-x-1.5 shadow-sm">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="target_role" value="{{ $targetRole ?? '' }}">
                    <input type="hidden" name="inspect" value="{{ !empty($isInspect) ? '1' : '0' }}">
                    
                    <!-- Email Field -->
                    <div>
                        <label class="block text-xs font-semibold text-rose-900 mb-1.5">
                            <i class="fa-solid fa-envelope text-rose-400 mr-1.5"></i> {{ $isEnLogin ? 'Employee Email' : 'Email Karyawan' }}
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="login-email" 
                            value="{{ old('email', $prefillEmail ?? '') }}" 
                            required 
                            autofocus
                            placeholder="{{ $isEnLogin ? 'Enter your official account email' : 'Masukkan email akun resmi Anda' }}" 
                            class="w-full text-sm px-4 py-2.5 border border-pink-200 rounded-lg focus:ring-2 focus:ring-[#E879A6] focus:border-[#E879A6] outline-none transition bg-[#FFF8FB] focus:bg-white text-rose-950"
                        >
                        @error('email')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-xs font-semibold text-rose-900 mb-1.5">
                            <i class="fa-solid fa-key text-rose-400 mr-1.5"></i> {{ $isEnLogin ? 'Password' : 'Kata Sandi (Password)' }}
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="login-password" 
                                required 
                                placeholder="{{ $isEnLogin ? 'Enter account password' : 'Masukkan kata sandi akun' }}" 
                                class="w-full text-sm px-4 py-2.5 border border-pink-200 rounded-lg focus:ring-2 focus:ring-[#E879A6] focus:border-[#E879A6] outline-none transition bg-[#FFF8FB] focus:bg-white text-rose-950 pr-10"
                            >
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-1/2 -translate-y-1/2 text-rose-400 hover:text-rose-600 focus:outline-none p-1">
                                <i class="fa-solid fa-eye text-xs" id="pwd-eye-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-rose-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between text-xs text-rose-800/80 pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="rounded border-pink-300 text-rose-500 focus:ring-rose-400 accent-rose-500">
                            <span>{{ $isEnLogin ? 'Remember my session on this device' : 'Ingat sesi saya di perangkat ini' }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        id="login-submit-btn" 
                        class="w-full odoo-btn-primary font-bold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition flex items-center justify-center space-x-2 text-sm mt-2"
                    >
                        <i class="fa-solid fa-right-to-bracket text-sm"></i>
                        <span>{{ $isEnLogin ? 'Unlock & Enter Dashboard' : 'Buka Kunci & Masuk Dashboard' }}</span>
                    </button>
                </form>
            </div>

            <!-- Card Footer -->
            <div class="p-4 bg-gradient-to-r from-[#FFF5F8] via-[#FADADD]/45 to-[#FFF5F8] border-t border-pink-200 text-center text-xs text-rose-800/85">
                <i class="fa-solid fa-lock text-emerald-600 mr-1"></i>
                {{ $isEnLogin ? 'Every staff member must sign in with their own personal account email and password' : 'Setiap admin wajib mengetahui email dan kata sandi akun pribadinya' }}
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-[#FFF5F8] via-[#FADADD]/45 to-[#FFF5F8] border-t border-[#F2B6CE] py-3 text-center text-xs text-rose-700/80">
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
