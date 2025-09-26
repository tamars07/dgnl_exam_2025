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
        Schema::create('council_turn_test_mixes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_mix_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('council_code',50)->nullable();
            $table->string('council_turn_code',50)->nullable();
            $table->boolean('is_used')->default(false)->comment('đề thi đã sử dụng?');
            $table->unsignedBigInteger('used_time')->default(0)->comment('thời gian sử dụng');
            $table->boolean('is_active_by_chairman')->default(false)->comment('diem truong kich hoat?');
            $table->timestamps();
            //indexing
            $table->index(['subject_id', 'council_code','council_turn_code'],'index_test_mixes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('council_turn_test_mixes');
    }
};
