<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('codTaller', 50)
                ->nullable();
            $table->string('title', 255)
                ->nullable();
            $table->dateTime('start')
                ->nullable();
            $table->dateTime('end')
                ->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('language_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->foreign('language_id')
                ->references('id')
                ->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workshops');
    }
}
