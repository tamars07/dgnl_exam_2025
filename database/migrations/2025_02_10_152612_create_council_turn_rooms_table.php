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
        Schema::create('council_turn_rooms', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false)->comment('1: da khoi tao phong thi, 2: chua khoi tao phong thi');
            $table->string('monitor_code',50)->nullable();
            $table->foreign('monitor_code')->references('code')->on('monitors')->onUpdate('cascade')->onDelete('cascade');
            $table->string('council_turn_code',50);
            $table->foreign('council_turn_code')->references('code')->on('council_turns')->onUpdate('cascade')->onDelete('cascade');
            $table->string('room_code',50);
            $table->foreign('room_code')->references('code')->on('rooms')->onUpdate('cascade')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_turn_rooms');
    }
};
