<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLengthColumnToTopicVideosTable extends Migration
{
    public function up(): void
    {
        Schema::table('topic_videos', function (Blueprint $table) {
            $table->bigInteger('length')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('topic_videos', function (Blueprint $table) {
            $table->dropColumn('length');
        });
    }
}
