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
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InventoryDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private $locations;

    private $parts;

    private Category $category;

    private Source $source;

    private $location0inventories;

    private $location1inventories;

    private $inventoryDraft;

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

        $this->inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $this->location1inventories = $this->parts->map(function ($part) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[1])
                ->for($this->user)
                ->for($this->inventoryDraft)
                ->create();
        });

    }

    public function test_inventory_draft_index()
    {
        InventoryDraft::factory()->count(5)->for($this->user)->for($this->locations[0])->create();

        $response = $this->actingAs($this->user)->get('/inventories/drafts');

        $response->assertInertia(fn(Assert $page) => $page
            ->component('Inventories/Index')
            ->has('inventory_drafts.data', $this->user->inventoryDrafts->count(), fn(Assert $page) => $page
                ->has('id')
                ->has('size')
                ->has('created_at')
            )
        );
    }

    function test_inventory_draft_create()
    {
        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $response = $this
            ->actingAs($this->user)
            ->get("/inventories/create/{$inventoryDraft->id}");

        $response->assertInertia(fn(Assert $page) => $page
            ->component('Inventories/Create')
            ->has('parts', $inventoryDraft->inventories->count())
            ->has('locations', $this->user->locations->count())
            ->where('location_id', $inventoryDraft->location_id)
        );
    }

    function test_inventory_draft_store()
    {
        $response = $this
            ->actingAs($this->user)
            ->post("/inventory-drafts/{$this->locations[0]->id}");

        $response->assertStatus(302);
    }

    function test_inventory_draft_destroy()
    {
        $response = $this
            ->actingAs($this->user)
            ->delete("/inventory-drafts/{$this->inventoryDraft->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('inventory_drafts', [
            'id' => $this->inventoryDraft->id,
        ]);
    }
}
