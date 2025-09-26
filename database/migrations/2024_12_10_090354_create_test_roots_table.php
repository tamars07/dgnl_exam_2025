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
        Schema::create('test_roots', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->text('content')->nullable();
            $table->unsignedInteger('duration')->default(0)->comment('Thời gian làm bài');
            $table->boolean('is_used')->default(false)->comment('đề thi gốc đã sử dụng?');
            
            $table->unsignedBigInteger('test_group_id');
            $table->foreign('test_group_id')->references('id')->on('test_groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_id');
            $table->foreign('test_id')->references('id')->on('tests')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_form_id');
            $table->foreign('test_form_id')->references('id')->on('test_forms')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
            
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('test_roots', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_roots', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_roots');
    }
};
