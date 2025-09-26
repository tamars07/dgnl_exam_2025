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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->text('desc')->nullable();
            // $table->unsignedInteger('period')->comment('số ca thi');
            $table->unsignedInteger('test_root_numbers')->comment('số lượng đề gốc');
            $table->unsignedInteger('test_mix_numbers')->comment('số lượng đề hoán vị');

            $table->unsignedBigInteger('test_group_id');
            $table->foreign('test_group_id')->references('id')->on('test_groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_form_id')->nullable();
            $table->foreign('test_form_id')->references('id')->on('test_forms')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');

            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('tests', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('tests');
    }
};
