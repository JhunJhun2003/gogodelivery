<?php

namespace Tests\Feature;

use App\Models\Biker;
use App\Models\User;
use App\Models\Way;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWayStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_mark_a_way_as_onway(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin1',
            'role' => User::ROLE_ADMIN,
        ]);

        $shop = User::factory()->create([
            'username' => 'shop1',
            'role' => User::ROLE_SHOP,
        ]);

        $biker = Biker::create(['name' => 'Ko Ko']);

        $way = Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Alice',
            'address' => 'Somewhere',
            'phone_number' => '123456',
            'amount' => 100,
            'delivery_fees' => 10,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->postJson("/admin/ways/{$way->id}/status", ['status' => 'onway'])
            ->assertOk()
            ->assertJsonPath('status', 'onway');

        $this->assertDatabaseHas('ways', [
            'id' => $way->id,
            'status' => 'onway',
        ]);
    }
}
