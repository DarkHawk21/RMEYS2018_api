<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('identifier')
                ->unique();
            $table->string('name');
            $table->string('last_name');
            $table->string('mother_last_name');
            $table->string('cellphone');
            $table->integer('social_service')
                ->default(0);
            $table->unsignedBigInteger('language_id');
            $table->timestamps();

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
        Schema::dropIfExists('user_details');
    }
}
