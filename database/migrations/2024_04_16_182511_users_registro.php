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
        Schema::create('registro', function(Blueprint $table)
        {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('enemy_id')->unsigned()->nullable();
            $table->unsignedBigInteger('ganador')->nullable();
            $table->foreign('ganador')->references('id')->on('users');
            $table->foreign('enemy_id')->references('id')->on('users');
            $table->integer('estado')->default(0)->nullable();
            $table->unsignedTinyInteger('turno')->nullable();
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
        Schema::dropIfExists('registro');
    }
};
