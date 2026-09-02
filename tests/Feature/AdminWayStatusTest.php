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

    public function test_admin_can_delete_a_way(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-delete',
            'role' => User::ROLE_ADMIN,
        ]);

        $shop = User::factory()->create([
            'username' => 'shop-delete',
            'role' => User::ROLE_SHOP,
        ]);

        $biker = Biker::create(['name' => 'Delete Rider']);

        $way = Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Bob',
            'address' => 'House 1',
            'phone_number' => '0987654321',
            'amount' => 250,
            'delivery_fees' => 15,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->delete("/admin/ways/{$way->id}")
            ->assertRedirect('/admin/history');

        $this->assertDatabaseMissing('ways', [
            'id' => $way->id,
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

    public function test_admin_history_filters_amount_range_even_when_min_is_higher_than_max(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-range',
            'role' => User::ROLE_ADMIN,
        ]);

        $shop = User::factory()->create([
            'username' => 'shop-range',
            'role' => User::ROLE_SHOP,
        ]);

        $biker = Biker::create(['name' => 'Range Rider']);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Low Amount',
            'address' => 'Range Street 1',
            'phone_number' => '0900000001',
            'amount' => 80,
            'delivery_fees' => 10,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Mid Amount',
            'address' => 'Range Street 2',
            'phone_number' => '0900000002',
            'amount' => 120,
            'delivery_fees' => 10,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'High Amount',
            'address' => 'Range Street 3',
            'phone_number' => '0900000003',
            'amount' => 180,
            'delivery_fees' => 10,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/history?min_amount=120&max_amount=80');

        $response->assertOk()
            ->assertSeeText('Mid Amount')
            ->assertDontSeeText('Low Amount')
            ->assertDontSeeText('High Amount');
    }

    public function test_admin_history_date_filter_uses_delivery_date_when_available(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-date-filter',
            'role' => User::ROLE_ADMIN,
        ]);

        $shop = User::factory()->create([
            'username' => 'shop-date-filter',
            'role' => User::ROLE_SHOP,
        ]);

        $biker = Biker::create(['name' => 'Delivery Date Rider']);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Delivered on 20th',
            'address' => 'Time Street 20',
            'phone_number' => '0900000020',
            'amount' => 200,
            'delivery_fees' => 20,
            'date' => '2026-08-10',
            'assigned_at' => '2026-08-20 09:00:00',
            'status' => 'delivered',
        ]);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Created on 20th',
            'address' => 'Time Street 21',
            'phone_number' => '0900000021',
            'amount' => 300,
            'delivery_fees' => 30,
            'date' => '2026-08-20',
            'assigned_at' => null,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/history?date=2026-08-20');

        $response->assertOk()
            ->assertSeeText('Delivered on 20th')
            ->assertDontSeeText('Created on 20th');
    }

    public function test_admin_can_export_history_as_pdf(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin-pdf',
            'role' => User::ROLE_ADMIN,
        ]);

        $shop = User::factory()->create([
            'username' => 'shop-pdf',
            'role' => User::ROLE_SHOP,
        ]);

        $biker = Biker::create(['name' => 'PDF Rider']);

        Way::create([
            'shop_id' => $shop->id,
            'biker_id' => $biker->id,
            'recipient_name' => 'Charlie',
            'address' => 'PDF Street 9',
            'phone_number' => '0999999999',
            'amount' => 155.50,
            'delivery_fees' => 12.50,
            'date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/history/export/pdf');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringContainsString('%PDF', $response->getContent());
    }

    public function test_oversized_upload_dimensions_are_rejected_before_decoding(): void
    {
        $method = new \ReflectionMethod(\App\Http\Controllers\WayController::class, 'ensureImageDimensionsAreSafe');
        $method->setAccessible(true);

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $this->expectExceptionMessage('Image dimensions exceed the maximum supported size.');

        $method->invoke(new \App\Http\Controllers\WayController(), 5000, 3000);
    }
}
