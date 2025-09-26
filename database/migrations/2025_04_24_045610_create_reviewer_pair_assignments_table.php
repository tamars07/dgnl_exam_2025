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
        Schema::create('reviewer_pair_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('examinee_test_code',50)->nullable()->comment('mã phách - khởi tạo khi bắt đầu chấm thi');
            $table->unsignedBigInteger('reviewer_pair_id')->comment('cap giam khao');
            $table->date('start_at')->nullable()->comment('ngay bat dau');
            $table->date('finish_at')->nullable()->comment('ngay ket thuc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_pair_assignments');
    }
};
