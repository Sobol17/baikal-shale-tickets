<?php

use App\Models\User;

it('allows only admin users to open filament panel', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->actingAs($user)->get('/admin')->assertForbidden();
});
