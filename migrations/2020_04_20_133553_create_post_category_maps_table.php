<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostCategoryMapsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_category_maps', function (Blueprint $table) {
            $table->integer('post_id');
            $table->integer('post_category_id');
            // Đánh index cho tên bảng, ngôn ngữ, và và chuỗi ngẫu nhiên
            $table->index([ 'post_id' ]);
            $table->index([ 'post_category_id' ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('post_category_maps');
    }
}
