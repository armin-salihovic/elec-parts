<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Location;
use App\Models\Part;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAddBySkuTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Location $location;
    private Source $source;
    private Part $part;
    private $quantity;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->location = Location::factory()->for($this->user)->create();

        $this->source = Source::factory()->create();

        $this->category = Category::factory()->for($this->user)->for($this->source)->create();

        $this->part = Part::factory()
            ->for($this->user)
            ->for($this->category)
            ->for($this->source)
            ->create();

        $this->quantity = 10;
    }

    public function test_add_by_sku()
    {
        $this->assertPostAddBySku();

        $this->assertInventoryEntry($this->quantity, null);
    }

    public function test_add_by_sku_draft()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->location)->create();

        $this->assertPostAddBySku($inventoryDraft->id);

        $this->assertInventoryEntry($this->quantity, $inventoryDraft->id);
    }

    public function test_add_by_sku_existing()
    {
        $this->createInventoryEntry(null);

        $this->assertPostAddBySku();

        $this->assertInventoryEntry($this->quantity * 2, null);
    }

    public function test_invalid_sku_param()
    {
        $response = $this->actingAs($this->user)->post("sku-part", [
            'sku' => fake()->unique()->randomNumber(8),
            'location' => $this->location->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
    }

    public function test_invalid_location()
    {
        $response = $this->actingAs($this->user)->post("sku-part", [
            'sku' => $this->part->sku,
            'location' => fake()->unique()->randomNumber(8),
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
    }

    public function test_invalid_inventory_draft()
    {
        $fakeId = fake()->unique()->randomNumber(8);
        $response = $this->actingAs($this->user)->post("sku-part?draft={$fakeId}", [
            'sku' => $this->part->sku,
            'location' => $this->location->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
    }

    function assertPostAddBySku($draftId = null)
    {
        $endpoint = $draftId ? "sku-part?draft={$draftId}" : 'sku-part';

        $response = $this->actingAs($this->user)->post($endpoint, [
            'sku' => $this->part->sku,
            'location' => $this->location->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);
    }

    function createInventoryEntry($draftId)
    {
        return Inventory::create([
            'inventoryable_id' => $this->part->id,
            'inventoryable_type' => $this->part::class,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'inventory_draft_id' => $draftId,
            'quantity' => $this->quantity,
        ]);
    }

    function assertInventoryEntry($quantity, $draftId)
    {
        $this->assertDatabaseHas('inventories', [
            'inventoryable_id' => $this->part->id,
            'inventoryable_type' => $this->part::class,
            'location_id' => $this->location->id,
            'user_id' => $this->user->id,
            'inventory_draft_id' => $draftId,
            'quantity' => $quantity,
        ]);
    }
}
