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
        Schema::create('pedals', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId("pedal_type_id")->constrained();
            $table->string("serial_number");
            $table->string("owner");
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
        Schema::dropIfExists('pedals');
    }
};
