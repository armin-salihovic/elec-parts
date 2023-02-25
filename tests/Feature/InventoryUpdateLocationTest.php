<?php


use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Location;
use App\Models\Part;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryUpdateLocationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private $locations;

    private $parts;

    private Inventory $inventory1;

    private Inventory $inventory2;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->locations = Location::factory()->count(5)->for($this->user)->create();

        $source = Source::factory()->create();

        $category = Category::factory()->for($this->user)->for($source)->create();

        $this->parts = Part::factory()
            ->count(5)
            ->for($this->user)
            ->for($category)
            ->for($source)
            ->create();

        $this->inventory1 = Inventory::factory()
            ->for($this->parts[0])
            ->for($this->locations[0])
            ->for($this->user)
            ->create();

        $this->inventory2 = Inventory::factory()
            ->for($this->parts[0])
            ->for($this->locations[1])
            ->for($this->user)
            ->create();

    }

    function test_update_location()
    {
        $response = $this->actingAs($this->user)->put("inventories/{$this->inventory1->id}/location", [
            'location' => $this->locations[3]->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->inventory1->id,
            'part_id' => $this->inventory1->part_id,
            'location_id' => $this->locations[3]->id,
            'user_id' => $this->user->id,
        ]);
    }

    function test_update_location_existing_transfer()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $inventory3 = Inventory::factory()
            ->for($this->parts[0])
            ->for($this->locations[1])
            ->for($this->user)
            ->for($inventoryDraft)
            ->create();

        $response = $this->actingAs($this->user)->put("inventories/{$this->inventory1->id}/location", [
            'location' => $this->locations[1]->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->inventory2->id,
            'part_id' => $this->inventory1->part_id,
            'location_id' => $this->inventory2->location_id,
            'user_id' => $this->user->id,
            'quantity' => $this->inventory1->quantity + $this->inventory2->quantity,
        ]);

        $this->assertDatabaseMissing('inventories', [
            'id' => $this->inventory1->id,
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $inventory3->id,
            'part_id' => $inventory3->part_id,
            'location_id' => $inventory3->location_id,
            'user_id' => $this->user->id,
            'inventory_draft_id' => $inventory3->inventory_draft_id,
            'quantity' => $inventory3->quantity,
        ]);
    }

    function test_update_location_same()
    {
        $response = $this->actingAs($this->user)->put("inventories/{$this->inventory1->id}/location", [
            'location' => $this->inventory1->location_id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->inventory1->id,
            'part_id' => $this->inventory1->part_id,
            'location_id' => $this->inventory1->location_id,
            'user_id' => $this->user->id,
            'quantity' => $this->inventory1->quantity,
        ]);
    }

}
