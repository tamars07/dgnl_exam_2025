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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->unique()->comment('mã phòng thi');
            $table->string('name',50)->comment('tên phòng thi');
            $table->string('desc',255)->nullable();
            $table->unsignedInteger('no_slots')->default(0);
            $table->boolean('status')->default(true);
            $table->string('organization_code',50);
            $table->foreign('organization_code')->references('code')->on('organizations')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('rooms');
    }
};
