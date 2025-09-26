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
        Schema::create('test_mixes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('content')->nullable();
            $table->boolean('is_used')->default(false)->comment('đề thi đã sử dụng?');
            $table->unsignedInteger('duration')->default(0)->comment('Thời gian làm bài');
            $table->string('council_code',50)->nullable();
            $table->string('council_turn_code',50)->nullable();
            $table->unsignedBigInteger('used_time')->default(0)->comment('thời gian sử dụng');

            $table->unsignedBigInteger('test_group_id')->nullable();
            $table->foreign('test_group_id')->references('id')->on('test_groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_id')->nullable();
            $table->foreign('test_id')->references('id')->on('tests')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_root_id')->nullable();
            // $table->foreign('test_root_id')->references('id')->on('test_roots')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_form_id')->nullable();
            $table->foreign('test_form_id')->references('id')->on('test_forms')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');

            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('test_mixes', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_mixes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_mixes');
    }
};
