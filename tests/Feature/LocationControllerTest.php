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

class LocationControllerTest extends TestCase
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

        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $this->location1inventories = $this->parts->map(function ($part) use ($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[1])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

    }

    function test_location_index()
    {
        $response = $this->actingAs($this->user)->get('/inventories/locations');

        $locations = $this->user->locations()->whereHas('inventories', function ($query) {
            $query->where('inventory_draft_id', null);
        })->get();

        $inventoryDraft = InventoryDraft::factory()->for($this->user)->for($this->locations[0])->create();

        $this->parts->map(function ($part) use ($inventoryDraft) {
            return Inventory::factory()
                ->for($part)
                ->for($this->locations[0])
                ->for($this->user)
                ->for($inventoryDraft)
                ->create();
        });

        $response->assertInertia(fn(Assert $page) => $page
            ->component('Inventories/Index')
            ->has('locations.data', $locations->count(), fn(Assert $page) => $page
                ->has('id')
                ->has('name')
                ->has('size')
            )
        );
    }

    function test_location_store()
    {
        $randomName = fake()->unique()->name();

        $response = $this
            ->actingAs($this->user)
            ->post("/locations", [
                'name' => $randomName,
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('locations', [
            'name' => $randomName,
        ]);
    }
}
