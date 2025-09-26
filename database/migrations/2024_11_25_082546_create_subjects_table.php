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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code',30)->comment('TO-LI-HO-SI-VA-N1');
            $table->string('short_code',30)->nullable()->comment('T-L-H-S-V-A');
            $table->tinyInteger('code_number')->comment('code dạng số');
            $table->string('name',100);
            $table->text('desc')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
        Schema::table('subjects', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::dropIfExists('subjects');
    }
};
