<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade')->comment('课程');
            $table->string('title')->comment('课程标题');
            $table->string('path', 500)->comment('视频地址');
            $table->unsignedSmallInteger('order')->default(0)->comment('排序');
            $table->unsignedInteger('view_num')->default(0)->comment('观看次数');
            // $table->unsignedInteger('comment_num')->default(0)->comment('评论数');
            // $table->unsignedInteger('favour_count')->default(0)->comment('点赞数');
            // $table->unsignedInteger('favorite_count')->default(0)->comment('收藏数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('videos');
    }
};
