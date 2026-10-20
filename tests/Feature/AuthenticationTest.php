<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_register_pages_are_available_to_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="email"', false);
        $this->get('/register')->assertOk()->assertSee('name="password_confirmation"', false);
    }

    public function test_registration_creates_a_user_with_a_hashed_password_and_user_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Honey Customer',
            'email' => 'customer@example.test',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
            'role' => 'admin',
        ]);

        $user = User::where('email', 'customer@example.test')->firstOrFail();

        $response->assertRedirect('/');
        $this->assertSame('user', $user->role);
        $this->assertTrue(Hash::check('strong-password', $user->password));
        $this->assertNotSame('strong-password', $user->password);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_duplicate_email_and_invalid_input(): void
    {
        User::factory()->create(['email' => 'existing@example.test']);

        $this->from('/register')->post('/register', [
            'name' => '',
            'email' => 'existing@example.test',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertRedirect('/register')
            ->assertSessionHasErrors(['name', 'email', 'password', 'password_confirmation']);
    }

    public function test_confirmation_mismatch_has_a_confirmation_field_error(): void
    {
        $this->from('/register')->followingRedirects()->post('/register', [
            'name' => 'Honey Customer',
            'email' => 'customer@example.test',
            'password' => 'strong-password',
            'password_confirmation' => 'different-password',
        ])->assertOk()
            ->assertSee('Konfirmasi kata sandi tidak cocok.')
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_valid_user_login_regenerates_the_session_and_redirects_to_customer_home(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('strong-password'),
        ]);

        $this->get('/login');
        $sessionId = session()->getId();

        $this->post('/login', [
            'email' => 'customer@example.test',
            'password' => 'strong-password',
        ])->assertRedirect('/');

        $this->assertNotSame($sessionId, session()->getId());
        $this->assertAuthenticatedAs($user);
    }

    public function test_valid_admin_login_redirects_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.test',
            'password' => Hash::make('strong-password'),
            'role' => 'admin',
        ]);

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'strong-password',
        ])->assertRedirect(route('admin.home'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_login_is_rejected_with_a_safe_error(): void
    {
        User::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('strong-password'),
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'customer@example.test',
            'password' => 'incorrect-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Email atau password tidak sesuai.',
            ]);

        $this->from('/login')->post('/login', [
            'email' => 'unknown@example.test',
            'password' => 'incorrect-password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Email atau password tidak sesuai.',
            ]);

        $this->assertGuest();
    }

    public function test_repeated_failed_logins_for_the_same_email_and_ip_are_throttled(): void
    {
        $credentials = [
            'email' => 'customer@example.test',
            'password' => 'incorrect-password',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->from('/login')
                ->post('/login', $credentials)
                ->assertSessionHasErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }

        $this->from('/login')
            ->post('/login', $credentials)
            ->assertSessionHasErrors([
                'email' => 'Terlalu banyak percobaan masuk. Silakan coba kembali beberapa saat lagi.',
            ]);

        $this->assertGuest();
    }

    public function test_failed_logins_from_one_ip_are_throttled_across_different_emails(): void
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->from('/login')
                ->post('/login', [
                    'email' => "customer{$attempt}@example.test",
                    'password' => 'incorrect-password',
                ])
                ->assertSessionHasErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }

        $this->from('/login')
            ->post('/login', [
                'email' => 'another-customer@example.test',
                'password' => 'incorrect-password',
            ])
            ->assertSessionHasErrors([
                'email' => 'Terlalu banyak percobaan masuk. Silakan coba kembali beberapa saat lagi.',
            ]);

        $this->assertGuest();
    }

    public function test_login_throttle_expires_after_one_minute(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('strong-password'),
        ]);
        $credentials = [
            'email' => 'customer@example.test',
            'password' => 'incorrect-password',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', $credentials)
                ->assertSessionHasErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }

        $this->travel(61)->seconds();

        $this->post('/login', [
            'email' => 'customer@example.test',
            'password' => 'strong-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_successful_login_clears_attempts_for_its_email_and_ip_pair(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.test',
            'password' => Hash::make('strong-password'),
        ]);
        $invalidCredentials = [
            'email' => 'customer@example.test',
            'password' => 'incorrect-password',
        ];

        for ($attempt = 0; $attempt < 4; $attempt++) {
            $this->post('/login', $invalidCredentials)
                ->assertSessionHasErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }

        $this->post('/login', [
            'email' => 'customer@example.test',
            'password' => 'strong-password',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', $invalidCredentials)
                ->assertSessionHasErrors([
                    'email' => 'Email atau password tidak sesuai.',
                ]);
        }
    }

    public function test_successful_login_does_not_clear_failed_attempts_for_its_ip(): void
    {
        $user = User::factory()->create([
            'email' => 'successful-customer@example.test',
            'password' => Hash::make('strong-password'),
        ]);

        for ($attempt = 0; $attempt < 29; $attempt++) {
            $this->post('/login', [
                'email' => "customer{$attempt}@example.test",
                'password' => 'incorrect-password',
            ])->assertSessionHasErrors([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        $this->post('/login', [
            'email' => 'successful-customer@example.test',
            'password' => 'strong-password',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');

        $this->post('/login', [
            'email' => 'thirtieth-customer@example.test',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors([
            'email' => 'Email atau password tidak sesuai.',
        ]);

        $this->post('/login', [
            'email' => 'thirty-first-customer@example.test',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors([
            'email' => 'Terlalu banyak percobaan masuk. Silakan coba kembali beberapa saat lagi.',
        ]);
    }

    public function test_logout_ends_the_authenticated_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $sessionId = session()->getId();
        $csrfToken = csrf_token();

        $this->post('/logout')->assertRedirect('/');

        $this->assertGuest();
        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($csrfToken, csrf_token());
    }

    public function test_guests_and_regular_users_cannot_access_admin_endpoint(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_can_access_the_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard Admin')
            ->assertSee('Total produk aktif')
            ->assertSee('Total penjualan aktual');
    }

    public function test_authenticated_user_is_redirected_away_from_login_and_register(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/login')->assertRedirect('/');
        $this->get('/register')->assertRedirect('/');
    }
}
