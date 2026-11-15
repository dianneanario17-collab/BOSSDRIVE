<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_address_step_uses_the_original_address_field(): void
    {
        $response = $this->withSession(['register_step' => 3])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('Block & Lot / Street')
            ->assertSee('name="address"', false);
        $this->assertStringNotContainsString('name="address_type"', $response->getContent());
    }

    public function test_registration_saves_address_without_an_address_type_prefix(): void
    {
        $this->withSession([
            'register_step' => 3,
            'registration' => [
                'name' => 'Test Renter',
                'email' => 'renter@example.com',
                'contact_number' => '09123456789',
                'age' => 25,
                'birth_date' => '2001-01-01',
                'gender' => 'Other',
                'password' => Hash::make('Password123!'),
            ],
        ])->post(route('user.register.address'), [
            'province' => 'Cavite',
            'city' => 'General Mariano Alvarez',
            'barangay' => 'Barangay 4 Poblacion',
            'address' => 'Block 2 Lot 5, Sample Street',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'renter@example.com',
            'address' => 'Block 2 Lot 5, Sample Street',
        ]);
    }
}
