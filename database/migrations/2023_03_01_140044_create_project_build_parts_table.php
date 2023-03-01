<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_build_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_build_id')->constrained();
            $table->foreignId('project_part_id')->constrained();
            $table->foreignId('inventory_id')->constrained();
            $table->unique(['project_build_id', 'project_part_id', 'inventory_id'], 'pb_pp_inv_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_build_parts');
    }
};
