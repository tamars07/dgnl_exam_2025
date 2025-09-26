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
        Schema::create('test_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('part_title')->nullable()->comment('tiêu đề phần thi');
            $table->text('desc')->nullable();
            $table->enum('caltype',['auto','auto_context','auto_fill','reader'])->comment('cách chấm thi: auto, auto_context, auto_fill, reader');
            $table->boolean('is_shuffled')->default(0)->comment('phần thi có hoán đổi vị trí câu hỏi');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
        Schema::table('test_parts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_parts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_parts');
    }
};
