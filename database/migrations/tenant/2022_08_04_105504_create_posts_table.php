<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('ID',20);
            $table->bigInteger('post_author')->nullable();
            $table->dateTime('post_date')->useCurrent();
            $table->dateTime('post_date_gmt')->useCurrent();
            $table->longText('post_content')->nullable();
            $table->text('post_title')->nullable();
            $table->text('post_excerpt')->nullable();
            $table->string('post_status',20)->nullable();
            $table->string('comment_status',20)->nullable();
            $table->string('ping_status',20)->nullable();
            $table->string('post_password',255)->nullable();
            $table->string('post_name',200)->nullable();
            $table->text('to_ping')->nullable();
            $table->text('pinged')->nullable();
            $table->dateTime('post_modified')->useCurrent();
            $table->dateTime('post_modified_gmt')->useCurrent();
            $table->longText('post_content_filtered')->nullable();
            $table->bigInteger('post_parent')->nullable();
            $table->string('guid',255)->nullable();
            $table->integer('menu_order')->nullable();
            $table->string('post_type',20)->nullable();
            $table->string('post_mime_type',100)->nullable();
            $table->bigInteger('comment_count')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->string('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
