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
        Schema::table('wa_traffic', function (Blueprint $table) {
            $table->unsignedBigInteger('guardian_web_id')->nullable()->after('article_show_id');
            $table->foreign('guardian_web_id')->references('id')->on('guardian_webs')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wa_traffic', function (Blueprint $table) {
            $table->dropForeign(['guardian_web_id']);
            $table->dropColumn('guardian_web_id');
        });
    }
};
