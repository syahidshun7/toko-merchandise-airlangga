<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guest_can_open_login_but_must_authenticate_to_open_dashboard(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Masuk admin');
        $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
        $this->assertGuest();
    }

    public function test_admin_can_sign_in_and_the_session_id_changes(): void
    {
        $password = Str::random(24);
        $admin = User::factory()->create(['password' => $password]);

        $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
        $oldSessionId = session()->getId();

        $this->post(route('admin.login.store'), [
            'email' => strtoupper($admin->email),
            'password' => $password,
        ])->assertRedirectToRoute('admin.dashboard');

        $this->assertAuthenticatedAs($admin);
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->get(route('admin.dashboard'))->assertOk()->assertSee($admin->name);
        $this->get(route('admin.login'))->assertRedirectToRoute('admin.dashboard');
    }

    public function test_wrong_credentials_do_not_authenticate_or_flash_the_password(): void
    {
        $admin = User::factory()->create();

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => Str::random(24),
        ])->assertRedirectToRoute('admin.login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.'])
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_login_validates_input(): void
    {
        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => ['unexpected'],
        ])->assertSessionHasErrors(['email', 'password']);

        $this->get(route('admin.login'))->assertOk();
        $this->assertGuest();
    }

    public function test_failed_attempts_temporarily_block_even_valid_credentials(): void
    {
        $password = Str::random(24);
        $admin = User::factory()->create(['password' => $password]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from(route('admin.login'))->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => Str::random(24),
            ])->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $this->from(route('admin.login'))->post(route('admin.login.store'), [
            'email' => strtoupper($admin->email),
            'password' => $password,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->travel(61)->seconds();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => $password,
        ])->assertRedirectToRoute('admin.dashboard');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_logout_invalidates_session_and_rotates_csrf_token(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)->withSession(['admin-session-marker' => true]);
        $this->get(route('admin.dashboard'))->assertOk();
        $oldSessionId = session()->getId();
        $oldToken = session()->token();

        $this->post(route('admin.logout'))
            ->assertRedirectToRoute('admin.login')
            ->assertSessionMissing('admin-session-marker');

        $this->assertGuest();
        $this->assertNotSame($oldSessionId, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->get(route('admin.dashboard'))->assertRedirectToRoute('admin.login');
    }

    public function test_public_registration_and_get_logout_are_unavailable(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
        $this->get('/admin/register')->assertNotFound();
        $this->post('/admin/register')->assertNotFound();
        $this->get('/admin/logout')->assertMethodNotAllowed();
    }
}
