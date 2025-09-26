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
        Schema::create('rubric_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('code',50)->unique()->comment('mã rubric');
            $table->string('name',255)->nullable();
            $table->string('desc',255)->nullable();
            $table->unsignedBigInteger('rubric_id');
            $table->foreign('rubric_id')->references('id')->on('rubrics')->onUpdate('cascade')->onDelete('cascade');
            $table->float('min_score')->default(0)->comment('mức điểm thấp nhất');
            $table->float('max_score')->default(0)->comment('mức điểm cao nhất');
            $table->set('scores',['0','0.25','0.5','0.75','1','1.25','1.5','1.75','2','2.25','2.5','2.75','3','3.25','3.5','3.75','4','4.25','4.5','4.75','5','5.25','5.5','5.75','6','6.25','6.5','6.75','7','7.25','7.5','7.75','8','8.25','8.5','8.75','9','9.25','9.5','9.75','10'])->nullable()->comment('bước nhảy điểm');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('rubric_criterias', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rubric_criterias', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('rubric_criterias');
    }
};
