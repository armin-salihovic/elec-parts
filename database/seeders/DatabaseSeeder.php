<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Category;
use App\Models\DistributorPart;
use App\Models\Source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{

    public static function addSourceToCategories(&$categories, $source_id)
    {
        foreach ($categories as &$value) {
            if (array_key_exists("children", $value)) {
                self::addSourceToCategories($value["children"], $source_id);
            }
            $value["source_id"] = 1;
        }
    }

    public static function findCategoryIDinTree($nodes, $category, $depth = 0, &$index = null)
    {
        foreach ($nodes as $node) {
            if ($node->name == $category[$depth]) {
                if (count($category) == $depth + 1) {
                    $index = $node->id;
                }
                self::findCategoryIDinTree($node->children, $category, $depth + 1, $index);
            }
        }

        if ($index != null) {
            return $index;
        }

        throw new \Exception("ERROR with finding the node");
    }

    public static function getCategoryID($category)
    {
        $partCategory = Category::where('name', $category[count($category) - 1])->get();
        if ($partCategory->count() == 1) {
            return $partCategory[0]->id;
        }

        $nodes = Category::whereIsRoot()->get();
        return DatabaseSeeder::findCategoryIDinTree($nodes, $category);
    }

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

        $json_string = Storage::disk('local')->get('categories.json');

        if ($json_string === null) {
            dd("ERROR: JSON is not read.");
        }

        $json = json_decode($json_string, true);
        if ($json === null) {
            dd("ERROR: Json decoding has failed.");
        }

        $partSources = [
            'Tayda',
            'Mouser',
            'DigiKey',
            'Farnell',
            'Local',
        ];

        foreach ($partSources as $name) {
            Source::create([
                'name' => $name
            ]);
        }

        DatabaseSeeder::addSourceToCategories($json, 1);

        foreach ($json as $value) {
            Category::create($value);
        }

        $json_string = Storage::disk('local')->get('products_cleaned.json');

        $json = json_decode($json_string, true);

        foreach ($json as $value) {
            DistributorPart::create([
                'name' => $value['name'],
                'sku' => $value['sku'],
                'price' => $value['price'],
                'url' => $value['url'],
                'category_id' => DatabaseSeeder::getCategoryID($value['category']),
                'source_id' => 1
            ]);
        }


    }
}
