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
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('code',30);
            $table->string('name',100);
            $table->text('desc')->nullable();

            $table->unsignedBigInteger('grade_id');
            $table->foreign('grade_id')->references('id')->on('grades')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('competency_id')->nullable();
            $table->foreign('competency_id')->references('id')->on('competencies')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('taxonomy_id');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onUpdate('cascade')->onDelete('cascade');

            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('topics', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('topics');
    }
};
