<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('objects', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('published')->default(1);
            $table->date('date')->nullable()->default(null);
            $table->string('name');
            $table->string('h1');
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->text('announce')->nullable()->default(null);
            $table->string('city')->nullable()->default(null);
            $table->string('square')->nullable()->default(null);
            $table->text('text')->nullable()->default(null);
            $table->string('image')->nullable()->default(null);
            $table->string('alias')->nullable()->default(null);
            $table->string('title')->nullable()->default(null);
            $table->string('keywords')->nullable()->default(null);
            $table->string('description')->nullable()->default(null);
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
        Schema::dropIfExists('objects');
    }
}
