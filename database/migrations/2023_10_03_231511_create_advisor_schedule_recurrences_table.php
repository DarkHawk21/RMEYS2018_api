<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisorScheduleRecurrencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisor_schedule_recurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advisor_schedule_id');
            $table->text('exdate')
                ->nullable();
            $table->string('recurrence_type')
                ->nullable();
            $table->string('duration')
                ->default('01:00')
                ->nullable();
            $table->string('freq')
                ->nullable();
            $table->dateTime('dtstart')
                ->nullable();
            $table->integer('interval')
                ->nullable();
            $table->dateTime('until')
                ->nullable();
            $table->string('byweekday')
                ->nullable();
            $table->timestamps();

            $table->foreign('advisor_schedule_id')
                ->references('id')
                ->on('advisor_schedules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisor_schedule_recurrences');
    }
}
