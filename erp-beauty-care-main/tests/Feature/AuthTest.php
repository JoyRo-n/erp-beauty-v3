<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\BaselineSeeder;
use Illuminate\Support\Facades\Auth;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new BaselineSeeder();
        $seeder->run();
    }

    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Portal Masuk Karyawan');
        $response->assertSee('Email Karyawan');
        $response->assertSee('Kata Sandi (Password)');
    }

    public function test_login_with_valid_credentials_warehouse(): void
    {
        $response = $this->post('/login', [
            'email' => 'warehouse@gmail.com',
            'password' => 'warehouse123',
        ]);

        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
        $this->assertEquals('Warehouse', Auth::user()->role);
    }

    public function test_login_with_valid_credentials_finance(): void
    {
        $response = $this->post('/login', [
            'email' => 'finance@gmail.com',
            'password' => 'finance123',
        ]);

        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
        $this->assertEquals('Finance', Auth::user()->role);
    }

    public function test_login_with_invalid_password_fails(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'warehouse@gmail.com',
            'password' => 'wrong_password_123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::where('email', 'warehouse@gmail.com')->first();
        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }

    public function test_authenticated_user_accessing_login_redirects_to_home(): void
    {
        $user = User::where('email', 'warehouse@gmail.com')->first();
        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/');
    }
}

