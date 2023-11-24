<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('object_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('object_id');
            $table->foreign('object_id')
                ->references('id')->on('objects')
                ->onDelete('cascade');
            $table->string('image')->nullable();
            $table->string('name')->nullable()->default(null);
            $table->unsignedTinyInteger('order')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('object_images');
    }
}
