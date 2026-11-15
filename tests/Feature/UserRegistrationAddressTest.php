<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRegistrationAddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_address_step_shows_address_type_dropdown(): void
    {
        $this->withSession(['register_step' => 3])
            ->get(route('register'))
            ->assertOk()
            ->assertSee('name="address_type"', false)
            ->assertSee('Select Address Type')
            ->assertSee('Other')
            ->assertSee('name="address"', false);
    }

    public function test_registration_saves_address_type_with_address_details(): void
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
            'address_type' => 'Block & Lot',
            'address' => 'Block 2 Lot 5, Sample Street',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'renter@example.com',
            'address' => 'Block & Lot: Block 2 Lot 5, Sample Street',
        ]);
    }
}
