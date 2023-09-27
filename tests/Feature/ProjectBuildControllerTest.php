<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\Part;
use App\Models\Project;
use App\Models\ProjectBuild;
use App\Models\ProjectBuildPart;
use App\Models\ProjectPart;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Collection;

class ProjectBuildControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Collection $locations;

    private Collection $parts;

    private Category $category;

    private Source $source;

    private Collection $location0inventories;

    private Collection $location1inventories;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->setUpLocations();

        $this->source = Source::create(['name' => 'Local']);

        $this->category = Category::create([
            'name' => 'Resistors',
            'source_id' => $this->source->id,
            'user_id' => $this->user->id,
        ]);

        $this->setUpParts();

        $this->setUpInventories();
    }

    public function test_project_build()
    {
        list($project, $projectBuild, $projectParts, $projectBuildParts) = $this->buildProject();

        // assertions
        $this->assertDatabaseMissing('project_builds', [
            'id' => $projectBuild->id,
            'completed' => 0,
        ]);

        $this->assertDatabaseMissing('project_build_parts', [
            'used' => 0,
        ]);

        $this->assertDatabaseHas('project_build_parts', [
            'id' => $projectBuildParts[0]->id,
            'quantity' => $projectParts[0]->quantity * $projectBuild->quantity
        ]);

        $this->assertDatabaseHas('project_build_parts', [
            'id' => $projectBuildParts[1]->id,
            'quantity' => $projectParts[1]->quantity * $projectBuild->quantity
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[0]->id,
            'quantity' => $this->location0inventories[0]->quantity - $projectBuild->quantity
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[1]->id,
            'quantity' => $this->location0inventories[1]->quantity - $projectBuild->quantity
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[2]->id,
            'quantity' => 0
        ]);
    }

    public function test_project_build_undo_build()
    {
        list($project, $projectBuild, $projectParts, $projectBuildParts) = $this->buildProject();

        // request
        $this
            ->actingAs($this->user)
            ->post("project-builds/" . $projectBuild->id . '/undo');

        $this->assertDatabaseMissing('project_builds', [
            'id' => $projectBuild->id,
            'completed' => 1,
        ]);

        $this->assertDatabaseHas('project_build_parts', [
            'id' => $projectBuildParts[0]->id,
            'used' => 0,
            'quantity' => 0
        ]);

        $this->assertDatabaseHas('project_build_parts', [
            'id' => $projectBuildParts[1]->id,
            'used' => 0,
            'quantity' => 0
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[0]->id,
            'quantity' => $this->location0inventories[0]->quantity
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[1]->id,
            'quantity' => $this->location0inventories[1]->quantity
        ]);

        $this->assertDatabaseHas('inventories', [
            'id' => $this->location0inventories[2]->id,
            'quantity' => $this->location0inventories[2]->quantity
        ]);

    }

//    public function test_project_build_update_build_selection_priority()
//    {
//
//    }

    public function test_project_build_destroy()
    {
        list($project, $projectBuild, $projectParts, $projectBuildParts) = $this->buildProject();

        // request
        $this
            ->actingAs($this->user)
            ->delete("project-builds/" . $projectBuild->id);

        $this->assertDatabaseMissing('project_builds', [
            'id' => $projectBuild->id,
        ]);
    }

    private function buildProject(): array
    {
        // prepare data
        $project = Project::create([
            'name' => "Test Project",
            'user_id' => $this->user->id,
        ]);

        $projectBuild = ProjectBuild::create([
            'quantity' => 3,
            'project_id' => $project->id,
        ]);

        $projectParts = collect();

        $projectParts->push(ProjectPart::create([
            'quantity' => 1,
            'part_name' => '10k res',
            'description' => fake()->name(),
            'designators' => fake()->name(),
            'project_id' => $project->id,
        ]));

        $projectParts->push(ProjectPart::create([
            'quantity' => 1,
            'part_name' => 'LED Green',
            'description' => fake()->name(),
            'designators' => fake()->name(),
            'project_id' => $project->id,
        ]));

        $projectParts->push(ProjectPart::create([
            'quantity' => $this->location0inventories[2]->quantity,
            'part_name' => 'LED Blue',
            'description' => fake()->name(),
            'designators' => fake()->name(),
            'project_id' => $project->id,
        ]));

        $projectBuildParts = collect();

        $projectBuildParts->push(ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $projectParts[0]->id,
            'inventory_id' => $this->location0inventories[0]->id,
            'used' => 0,
            'quantity' => 0,
        ]));

        $projectBuildParts->push(ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $projectParts[1]->id,
            'inventory_id' => $this->location0inventories[1]->id,
            'used' => 0,
            'quantity' => 0,
        ]));

        $projectBuildParts->push(ProjectBuildPart::create([
            'project_build_id' => $projectBuild->id,
            'project_part_id' => $projectParts[2]->id,
            'inventory_id' => $this->location0inventories[2]->id,
            'used' => 0,
            'quantity' => 0,
        ]));

        // request
        $this
            ->actingAs($this->user)
            ->post("project-builds/" . $projectBuild->id);

        return array($project, $projectBuild, $projectParts, $projectBuildParts);
    }

    private function setUpLocations()
    {
        $this->locations = collect();

        $this->locations->push(Location::create([
            'user_id' => $this->user->id,
            'name' => "BAG 1",
        ]));

        $this->locations->push(Location::create([
            'user_id' => $this->user->id,
            'name' => "BAG 2",
        ]));

        $this->locations->push(Location::create([
            'user_id' => $this->user->id,
            'name' => "BAG 3",
        ]));
    }

    private function setUpParts()
    {
        $this->parts = collect();

        $this->parts->push(Part::create([
            'name' => '10k resistor 1/4w',
            'category_id' => $this->category->id,
            'source_id' => $this->source->id,
            'user_id' => $this->user->id,
        ]));

        $this->parts->push(Part::create([
            'name' => 'LED Green',
            'category_id' => $this->category->id,
            'source_id' => $this->source->id,
            'user_id' => $this->user->id,
        ]));

        $this->parts->push(Part::create([
            'name' => 'LED Blue',
            'category_id' => $this->category->id,
            'source_id' => $this->source->id,
            'user_id' => $this->user->id,
        ]));
    }

    private function setUpInventories()
    {
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
}
