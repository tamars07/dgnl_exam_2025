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
        Schema::create('test_form_parts', function (Blueprint $table) {
            $table->id();
            $table->string('desc')->nullable()->comment('mô tả phần thi');
            $table->unsignedInteger('order')->nullable()->comment('thứ tự trong đề thi');
            $table->unsignedInteger('no_questions')->nullable()->comment('số lượng câu hỏi');
            $table->set('list_questions',['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50'])->nullable()->comment('danh sách câu hỏi');

            $table->unsignedBigInteger('test_form_id');
            $table->foreign('test_form_id')->references('id')->on('test_forms')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('test_part_id');
            $table->foreign('test_part_id')->references('id')->on('test_parts')->onUpdate('cascade')->onDelete('cascade');
            // $table->unsignedBigInteger('question_type_id')->nullable();
            // $table->foreign('question_type_id')->references('id')->on('question_types')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
        Schema::table('test_form_parts', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_form_parts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('test_form_parts');
    }
};
