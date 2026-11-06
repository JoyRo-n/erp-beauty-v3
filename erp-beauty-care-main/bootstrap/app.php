<?php

use App\Http\Middleware\PreventBackHistoryCache;
use App\Services\RoleAccessService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('web', PreventBackHistoryCache::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Expired/stale CSRF token (e.g. from an old tab): keep authenticated users in their session
        // and send guests to a fresh login page instead of showing "419 Page Expired".
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if ($e->getStatusCode() !== 419 && !($e->getPrevious() instanceof TokenMismatchException)) {
                return null;
            }

            if (Auth::check()) {
                $user = Auth::user();
                if ($request->is('switch-role') && $request->filled('role') && $request->input('role') !== $user->role) {
                    if (RoleAccessService::isSuperRole($user->role)) {
                        $request->session()->put('origin_super_user_id', $user->id);
                    }
                    return redirect()->route('login', ['role' => $request->input('role')])
                        ->with('info', 'Silakan masukkan kata sandi akun tujuan, atau tekan Kembali untuk tetap di akun Anda.');
                }

                return redirect()->route('s01.home')
                    ->with('info', 'Halaman diperbarui secara otomatis. Sesi akun ' . $user->role . ' Anda tetap aktif.');
            }

            $params = [];
            if ($request->is('switch-role') && $request->filled('role')) {
                $params['role'] = $request->input('role');
            }

            return redirect()->route('login', $params)
                ->with('info', 'Sesi halaman sudah kedaluwarsa. Silakan masuk kembali dengan akun yang dipilih.');
        });
    })->create();
