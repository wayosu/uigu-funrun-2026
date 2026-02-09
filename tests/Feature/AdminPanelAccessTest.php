<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_admin_can_access_resources()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/events')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/race-categories')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/registrations')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/payments')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/payment-settings')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/notification-settings')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/checkin-settings')
            ->assertStatus(200);

        $this->actingAs($user)
            ->get('/admin/participants')
            ->assertStatus(200);
    }
}
