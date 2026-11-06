<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\BaselineSeeder;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BackNavigationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new BaselineSeeder())->run();
    }

    public function test_pages_are_not_cached_so_back_button_refetches_them(): void
    {
        $response = $this->actingAs(User::where('role', 'Admin')->first())->get('/');
        $response->assertStatus(200);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));

        $login = $this->get('/login');
        $this->assertStringContainsString('no-store', $login->headers->get('Cache-Control'));
    }

    public function test_expired_csrf_token_redirects_guest_to_login_instead_of_419(): void
    {
        Route::post('/_test-expired', fn () => throw new TokenMismatchException('CSRF token mismatch.'))
            ->middleware('web');

        $response = $this->post('/_test-expired');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
    }

    public function test_expired_csrf_token_keeps_authenticated_admin_logged_in(): void
    {
        Route::post('/_test-expired-auth', fn () => throw new TokenMismatchException('CSRF token mismatch.'))
            ->middleware('web');

        $admin = User::where('role', 'Admin')->firstOrFail();
        $response = $this->actingAs($admin)->post('/_test-expired-auth');
        $response->assertRedirect(route('s01.home'));
        $this->assertEquals('Admin', Auth::user()->role);
    }

    public function test_admin_and_owner_matrix_inspect_does_not_terminate_session_and_can_return(): void
    {
        foreach (['Admin', 'Owner'] as $superRole) {
            $superUser = User::where('role', $superRole)->firstOrFail();

            // 1. Admin/Owner clicks "Inspeksi Akses Sales" in Main Admin Matrix (inspect=1) -> session NOT terminated
            $inspectResponse = $this->actingAs($superUser)
                ->from('/')
                ->post('/switch-role', ['role' => 'Sales', 'inspect' => 1, '_browser_flow' => 1]);

            $inspectResponse->assertRedirect(route('login', ['role' => 'Sales', 'inspect' => 1]));
            $this->assertTrue(Auth::check());
            $this->assertEquals($superRole, Auth::user()->role);

            // 2. Login screen keeps Admin/Owner session alive and shows Kembali button
            $loginPage = $this->get('/login?role=Sales&inspect=1');
            $loginPage->assertStatus(200);
            $loginPage->assertSee('sales@gmail.com');
            $loginPage->assertSee("Kembali ke {$superRole}");

            // 3. Pressing Back or Kembali returns directly to Admin/Owner without logout
            $backToHome = $this->get('/');
            $backToHome->assertStatus(200);
            $backToHome->assertSee('inspectStaffRole(', false);
            $this->assertEquals($superRole, Auth::user()->role);

            // 4. Completing staff inspection login preserves return-to-super button
            $this->post('/login', [
                'email' => 'sales@gmail.com',
                'password' => 'sales123',
                'target_role' => 'Sales',
                'inspect' => 1,
            ])->assertRedirect(route('s01.home'));

            $this->assertEquals('Sales', Auth::user()->role);
            $this->get('/')->assertStatus(200)->assertSee("Kembali ke {$superRole}");

            $this->post('/return-super')->assertRedirect(route('s01.home'));
            $this->assertEquals($superRole, Auth::user()->role);
        }
    }

    public function test_top_right_sidebar_switch_terminates_previous_session_for_staff_and_super_roles(): void
    {
        foreach (['Finance', 'Admin', 'Owner'] as $role) {
            $user = User::where('role', $role)->firstOrFail();

            // User chooses another account from the top-right sidebar (no inspect flag) and confirms "Log Out"
            $switchResponse = $this->actingAs($user)
                ->from('/')
                ->post('/switch-role', ['role' => 'Sales', '_browser_flow' => 1]);

            $switchResponse->assertRedirect(route('login', ['role' => 'Sales']));
            $this->assertGuest();

            // Login screen loads cleanly with Sales email pre-filled and NO active previous session banner
            $loginPage = $this->get('/login?role=Sales');
            $loginPage->assertStatus(200);
            $loginPage->assertSee('sales@gmail.com');
            $loginPage->assertDontSee('Mode Inspeksi:');
            $loginPage->assertDontSee('Kembali ke');
            $this->assertGuest();
        }
    }

    public function test_account_switch_logout_confirmation_modal_rendered(): void
    {
        $admin = User::where('role', 'Admin')->firstOrFail();
        $res = $this->actingAs($admin)->get('/');
        $res->assertStatus(200);
        $res->assertSee('id="logout-confirm-modal"', false);
        $res->assertSee('Are you sure you want to log out?');
        $res->assertSee('Cancel');
        $res->assertSee('Log Out');
    }
}
