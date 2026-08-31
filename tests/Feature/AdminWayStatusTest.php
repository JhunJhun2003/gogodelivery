<?php

namespace Tests\Feature;

use App\Models\Biker;
use App\Models\User;
use App\Models\Way;
use Database\Seeders\DatabaseSeeder;
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

    public function test_admin_can_create_a_user_with_phone_number(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-create',
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Biker One',
                'username' => 'biker-one',
                'password' => 'secret123',
                'role' => User::ROLE_BIKER,
                'biker_id' => null,
                'phone_number' => '0912345678',
            ])
            ->assertRedirect('/admin/users');

        $this->assertDatabaseHas('users', [
            'username' => 'biker-one',
            'phone_number' => '0912345678',
            'role' => User::ROLE_BIKER,
            'email' => null,
        ]);
    }

    public function test_seeded_biker_user_is_linked_to_a_biker_record(): void
    {
        $this->seed(DatabaseSeeder::class);

        $bikerUser = User::query()->where('username', 'biker1')->firstOrFail();

        $this->assertNotNull($bikerUser->biker_id);
        $this->assertNotNull($bikerUser->biker);
        $this->assertSame('Biker Rider', $bikerUser->biker->name);
    }
}
