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
        Schema::create('examiner_pair_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examiner_pair_id')->comment('ID cap giam khao');
            $table->unsignedBigInteger('examiner_id')->comment('ID giam khao');
            $table->unsignedTinyInteger('examiner_role')->default(1)->comment('Vai tro giam khao');
            $table->unsignedTinyInteger('no_assigned_test')->default(0)->comment('Số bài sẽ gán');
            $table->unsignedTinyInteger('no_done_test')->default(0)->comment('Số bài đã chấm');
            $table->date('start_at')->nullable()->comment('ngay bat dau');
            $table->date('finish_at')->nullable()->comment('ngay ket thuc');
            $table->boolean('is_assigned')->default(0)->comment('Đã gán bài');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examiner_pair_details');
    }
};
