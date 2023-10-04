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
            $table->string('exdate')
                ->nullable();
            $table->string('freq')
                ->nullable();
            $table->dateTime('dtstart')
                ->nullable();
            $table->integer('interval')
                ->nullable();
            $table->integer('count')
                ->nullable();
            $table->dateTime('until')
                ->nullable();
            $table->string('bysetpos')
                ->nullable();
            $table->string('bymonth')
                ->nullable();
            $table->string('bymonthday')
                ->nullable();
            $table->string('byyearday')
                ->nullable();
            $table->string('byweekno')
                ->nullable();
            $table->string('byweekday')
                ->nullable();
            $table->string('byhour')
                ->nullable();
            $table->string('byminute')
                ->nullable();
            $table->string('bysecond')
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
