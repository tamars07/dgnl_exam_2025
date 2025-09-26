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
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->unique()->comment('mã rubric');
            $table->string('name',255)->nullable();
            $table->string('desc',255)->nullable();
            $table->unsignedBigInteger('subject_id');
            $table->tinyInteger('matrix_location')->default(0)->comment('Vị trí xuất hiện trong ma trận');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('rubrics', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubrics', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('rubrics');
    }
};
