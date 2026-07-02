<?php

use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('allows only admin users to open filament panel', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($user)->get('/admin')->assertForbidden();
});

it('allows admin users to change their password from profile page', function (): void {
    $admin = User::factory()->create([
        'is_admin' => true,
        'password' => 'old-password',
    ]);

    $this->actingAs($admin)
        ->get(route('filament.admin.auth.profile'))
        ->assertOk();

    Livewire::actingAs($admin)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => 'new-password',
            'passwordConfirmation' => 'new-password',
            'currentPassword' => 'old-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('new-password', $admin->refresh()->password))->toBeTrue();
});
