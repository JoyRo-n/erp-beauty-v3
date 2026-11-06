<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\AuditService;
use App\Services\RoleAccessService;

class AuthController extends Controller
{
    /**
     * Show Odoo 17 branded Login Screen.
     * Keeps Admin/Owner session intact ONLY when reached via S-01 Matrix inspection (?inspect=1).
     */
    public function showLoginForm(Request $request)
    {
        $targetRole = $request->query('role');
        $isInspect = $request->boolean('inspect');
        $authUser = Auth::user();

        if ($authUser) {
            if (!$isInspect || !RoleAccessService::isSuperRole($authUser->role) || !$targetRole || $targetRole === $authUser->role) {
                return redirect()->route('s01.home');
            }
        }

        $currentUser = ($isInspect && $authUser && RoleAccessService::isSuperRole($authUser->role)) ? $authUser : null;

        $prefillEmail = $request->query('email', '');
        if (!$prefillEmail && $targetRole) {
            $user = User::where('role', $targetRole)->first();
            if ($user) {
                $prefillEmail = $user->email;
            }
        }

        $returnUrl = $request->session()->get('switch_return_url', route('s01.home'));

        return view('auth.login', compact('prefillEmail', 'targetRole', 'currentUser', 'returnUrl', 'isInspect'));
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

        $isInspect = $request->boolean('inspect');
        $previousSuperId = null;
        if ($isInspect) {
            $previousSuperId = $request->session()->get('origin_super_user_id');
            if (Auth::check() && RoleAccessService::isSuperRole(Auth::user()->role)) {
                $previousSuperId = Auth::id();
            }
        }

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
                    $request->session()->forget('switch_return_url');

                    if ($isInspect && $previousSuperId && !RoleAccessService::isSuperRole($user->role)) {
                        $request->session()->put('origin_super_user_id', $previousSuperId);
                    } else {
                        $request->session()->forget('origin_super_user_id');
                    }

                    AuditService::log('Login Success', 'Auth', (string) $user->id, "User {$user->name} logged in with role {$user->role}", $user->name, $user->role);

                    $redirect = $request->filled('target_role')
                        ? redirect()->route('s01.home')
                        : redirect()->intended(route('s01.home'));

                    return $redirect->with('success', "Selamat datang kembali, {$user->name}! Anda masuk sebagai peran {$user->role}.");
                }
            }
        }

        AuditService::log('Login Failed', 'Auth', $request->email, "Failed login attempt for {$request->email}", Auth::check() ? Auth::user()->name : 'Guest', Auth::check() ? Auth::user()->role : 'None');

        $targetRole = $request->input('target_role');
        $backRedirect = ($isInspect && Auth::check() && $targetRole)
            ? redirect()->route('login', ['role' => $targetRole, 'inspect' => 1])
            : redirect()->back();

        return $backRedirect
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
