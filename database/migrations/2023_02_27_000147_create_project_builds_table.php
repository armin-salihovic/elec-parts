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
        Schema::create('project_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->integer('quantity');
            $table->boolean('completed')->default(false);
            $table->smallInteger('selection_priority')->default(0);
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
        Schema::dropIfExists('project_builds');
    }
};
