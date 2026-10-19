<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_creates_and_authenticates_user(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Ana Lectora',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Ana Lectora',
            'email' => 'ana@example.com',
        ]);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Otra Ana',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
