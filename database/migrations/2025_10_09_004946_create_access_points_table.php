<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained()->cascadeOnDelete();
            $table->string('name'); //nama ssid
            $table->string('mac_address')->nullable();
            $table->unsignedSmallInteger('x_position');
            $table->unsignedSmallInteger('y_position');
            $table->integer('signal_strenght')->nullable();
            $table->enum('status', ['active','offline','maintenance'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_points');
    }
};
