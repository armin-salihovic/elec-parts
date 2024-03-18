<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProjectPartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quantity' => $this->faker->randomNumber(),
            'part_name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'designators' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'project_id' => Project::factory(),
        ];
    }
}
