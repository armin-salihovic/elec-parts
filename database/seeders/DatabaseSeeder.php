<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\PartCategory;
use App\Models\PartSource;
use App\Models\PedalType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@armin.ba',
            'password' => Hash::make("123")
        ]);

        $pedalTypes = [
            'Distortion',
            'Delay',
            'Modulation',
            'Other'
        ];

        $partCategories = [
            'Resistors',
            'Capacitors',
            'Transistors',
            'LED',
            'Diodes',
            'Enclosures',
            'Knobs',
        ];

        $partSources = [
            'Tayda',
            'Mouser',
            'DigiKey',
            'Farnell',
            'Other',
        ];

        foreach ($pedalTypes as $name) {
            PedalType::create([
                'name' => $name
            ]);
        }

        foreach ($partCategories as $name) {
            PartCategory::create([
                'name' => $name
            ]);
        }
        foreach ($partSources as $name) {
            PartSource::create([
                'name' => $name
            ]);
        }


    }
}
