<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedStore();
    }

    public function test_customer_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Nusrat Jahan',
            'email' => 'nusrat@example.com',
            'phone' => '01812-345678',
            'password' => 'Leather2026',
            'password_confirmation' => 'Leather2026',
        ])->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'nusrat@example.com')->sole();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertSame('01812345678', $user->phone);
    }

    public function test_registration_validates_input(): void
    {
        $this->post('/register', ['name' => 'X', 'email' => 'bad', 'phone' => '123', 'password' => 'short', 'password_confirmation' => 'nope'])
            ->assertSessionHasErrors(['email', 'phone', 'password']);
        $this->assertGuest();
    }

    public function test_customer_can_login_and_logout(): void
    {
        $user = $this->customer(['password' => 'Password123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/account')->assertOk()->assertSee('Hello');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_inactive_customer_cannot_login(): void
    {
        $user = $this->customer(['password' => 'Password123', 'is_active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_account_pages_require_login(): void
    {
        foreach (['/account', '/account/orders', '/account/addresses', '/account/nfc', '/account/warranty', '/account/profile'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_customer_account_pages_load(): void
    {
        $this->actingAs($this->customer());

        foreach (['/account', '/account/orders', '/account/addresses', '/account/addresses/create', '/account/nfc', '/account/warranty', '/account/profile'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_customer_can_save_address(): void
    {
        $user = $this->customer();

        $this->actingAs($user)->post('/account/addresses', [
            'label' => 'Office', 'name' => 'Ashik', 'phone' => '01712345678', 'address' => 'Level 5, Gulshan Ave', 'city' => 'Dhaka',
        ])->assertRedirect(route('account.addresses.index'));

        $this->assertTrue($user->addresses()->sole()->is_default);
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $this->actingAs($this->customer())->get('/admin')->assertForbidden();
    }

    public function test_staff_can_access_admin_panel(): void
    {
        $this->actingAs($this->staff('manager'))->get('/admin')->assertOk();
    }
}
