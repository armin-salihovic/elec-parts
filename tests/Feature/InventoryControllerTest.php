<?php


use App\Models\Category;
use App\Models\Inventory;
use App\Models\InventoryDraft;
use App\Models\Location;
use App\Models\Part;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InventoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private $locations;

    private $parts;

    private Category $category;

    private Source $source;

    private $location0inventories;
    private $location1inventories;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->locations = Location::factory()->count(5)->for($this->user)->create();

        $this->source = Source::factory()->create();

        $this->category = Category::factory()->for($this->user)->for($this->source)->create();

        $this->parts = Part::factory()
            ->count(5)
            ->for($this->user)
            ->for($this->category)
            ->for($this->source)
            ->create();

        $this->location0inventories = $this->parts->map(function ($part) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[0])
                ->for($this->user)
                ->create();
        });

        $this->location1inventories = $this->parts->map(function ($part) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[1])
                ->for($this->user)
                ->create();
        });
    }

    function test_inventory_index()
    {
        $response = $this->actingAs($this->user)->get('/inventories');

        $response->assertInertia(fn (Assert $page) => $page
                ->component('Inventories/Index')
                ->has('data.data', $this->user->inventories->count() > 10 ? 10 : $this->user->inventories->count())
                ->has('locations', $this->user->locations->count())
                ->has('sources', Source::all()->count())
            );
    }

    function test_inventory_create()
    {
        $response = $this
            ->actingAs($this->user)
            ->get("/inventories/create");

        $response->assertInertia(fn (Assert $page) => $page
                ->component('Locations/Create')
                ->has('locations', $this->user->locations->count())
            );
    }

    // tests if non-existing parts in the location are saved correctly
    function test_inventory_store_new()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $location3inventories = $this->parts->map(function ($part) use($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[3])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

        $response = $this
            ->actingAs($this->user)
            ->post("/inventories/create/{$inventoryDraft->id}");

        $this->assertDatabaseMissing('inventory_drafts', [
            'id' => $inventoryDraft->id,
        ]);

        foreach ($location3inventories as $inventory) {
            $this->assertDatabaseHas('inventories', [
                'id' => $inventory->id,
                'inventory_draft_id' => null,
            ]);
        }

        $response->assertStatus(302);
    }

    // tests if existing parts in the location are saved correctly
    function test_inventory_store_existing()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $location0inventories = $this->parts->map(function ($part) use($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[0])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

        $response = $this
            ->actingAs($this->user)
            ->post("/inventories/create/{$inventoryDraft->id}");

        $this->assertDatabaseMissing('inventory_drafts', [
            'id' => $inventoryDraft->id,
        ]);

        foreach ($location0inventories as $inventory) {
            $this->assertDatabaseMissing('inventories', [
                'id' => $inventory->id,
            ]);
        }

        foreach ($this->location0inventories as $inventory) {
            foreach ($location0inventories as $inventory2) {
                if($inventory->part_id === $inventory2->part_id) {
                    $this->assertDatabaseHas('inventories', [
                        'id' => $inventory->id,
                        'quantity' => $inventory->quantity + $inventory2->quantity,
                    ]);
                    break;
                }
            }
        }

        $response->assertStatus(302);
    }

    // tests if both existing and new parts in the location are saved correctly
    function test_inventory_store_mixed()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $location0inventoriesPart1 = $this->parts->map(function ($part) use($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[0])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

        $parts2 = Part::factory()
            ->count(5)
            ->for($this->user)
            ->for($this->category)
            ->for($this->source)
            ->create();

        $location0inventoriesPart2 = $parts2->map(function ($part) use($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[0])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

        $response = $this
            ->actingAs($this->user)
            ->post("/inventories/create/{$inventoryDraft->id}");

        $this->assertDatabaseMissing('inventory_drafts', [
            'id' => $inventoryDraft->id,
        ]);

        // existing

        foreach ($location0inventoriesPart1 as $inventory) {
            $this->assertDatabaseMissing('inventories', [
                'id' => $inventory->id,
            ]);
        }

        foreach ($this->location0inventories as $inventory) {
            foreach ($location0inventoriesPart1 as $inventory2) {
                if($inventory->part_id === $inventory2->part_id) {
                    $this->assertDatabaseHas('inventories', [
                        'id' => $inventory->id,
                        'quantity' => $inventory->quantity + $inventory2->quantity,
                    ]);
                    break;
                }
            }
        }

        // new

        foreach ($location0inventoriesPart2 as $inventory) {
            $this->assertDatabaseHas('inventories', [
                'id' => $inventory->id,
                'inventory_draft_id' => null,
                'quantity' => $inventory->quantity,
            ]);
        }

        $response->assertStatus(302);
    }

    function test_inventory_quantity_update()
    {
        $response = $this->actingAs($this->user)->put("inventories/{$this->location0inventories[0]->id}", [
            'quantity' => -1,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[0]->id,
            'quantity' => -1,
        ]);
    }

    function test_inventory_delete()
    {
        $response = $this->actingAs($this->user)->delete("inventories/{$this->location0inventories[0]->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('inventories', [
            'id' => $this->location0inventories[0]->id,
        ]);
    }

}
