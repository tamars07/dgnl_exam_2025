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
        Schema::create('difficults', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->string('desc',200)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('difficults', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('difficults', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('difficults');
    }
};
