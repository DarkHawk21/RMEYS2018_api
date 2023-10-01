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
            $table->string('color', 255)
                ->nullable();
            $table->string('textColor', 255)
                ->nullable();
            $table->dateTime('start')
                ->nullable();
            $table->dateTime('end')
                ->nullable();
            $table->string('duracion', 50)
                ->nullable();
            $table->integer('cupomin')
                ->nullable();
            $table->integer('cupomax')
                ->nullable();
            $table->integer('cupoactual')
                ->nullable();
            $table->string('asesor', 100)
                ->nullable();
            $table->string('idioma', 50)
                ->nullable();
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
