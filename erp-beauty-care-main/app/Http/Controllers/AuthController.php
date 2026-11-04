<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AuditService;

class AuthController extends Controller
{
    /**
     * Show Odoo 17 branded Login Screen
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('s01.home');
        }

        $prefillEmail = $request->query('email', '');
        $role = $request->query('role');
        if (!$prefillEmail && $role) {
            $user = User::where('role', $role)->first();
            if ($user) {
                $prefillEmail = $user->email;
            }
        }

        return view('auth.login', compact('prefillEmail'));
    }

    /**
     * Handle Login Submission (Authenticate with Email and Password)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $inputEmail = trim(strtolower($request->input('email')));
        $inputPassword = trim($request->input('password'));
        $cleanPassword = str_replace(' ', '', $inputPassword);
        $remember = $request->boolean('remember');

        // Allow both @gmail.com and @arunika.com aliases
        $emailCandidates = array_unique([
            $inputEmail,
            str_replace('@arunika.com', '@gmail.com', $inputEmail),
            str_replace('@gmail.com', '@arunika.com', $inputEmail),
        ]);

        // Support exact password and password without spaces (e.g. 'finance 123' -> 'finance123')
        $passwordCandidates = array_unique([
            $inputPassword,
            $cleanPassword,
        ]);

        foreach ($emailCandidates as $email) {
            foreach ($passwordCandidates as $password) {
                if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
                    $request->session()->regenerate();
                    $user = Auth::user();
                    $request->session()->put('active_role', $user->role);

                    AuditService::log('Login Success', 'Auth', (string) $user->id, "User {$user->name} logged in with role {$user->role}", $user->name, $user->role);

                    return redirect()->intended(route('s01.home'))->with('success', "Selamat datang kembali, {$user->name}! Anda masuk sebagai peran {$user->role}.");
                }
            }
        }

        AuditService::log('Login Failed', 'Auth', $request->email, "Failed login attempt for {$request->email}", 'Guest', 'None');

        return redirect()->back()
            ->withInput($request->only('email'))
            ->with('error', 'Kombinasi Email atau Password salah! Setiap profil memerlukan kunci sandi yang valid untuk mengakses sistem.');
    }

    /**
     * Handle User Logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditService::log('User Logout', 'Auth', (string) $user->id, "User {$user->name} logged out", $user->name, $user->role);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem ERP NusaGlow.');
    }
}

