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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->unique()->comment('mã địa điểm tổ chức thi');
            $table->string('name',255);
            $table->text('address')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('organizations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('organizations');
    }
};
