<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('schedule_event_id');
            $table->date('selected_date');
            $table->time('selected_time_start');
            $table->time('selected_time_end');
            $table->date('real_date_start')
                ->nullable();
            $table->date('real_date_end')
                ->nullable();
            $table->time('real_time_start')
                ->nullable();
            $table->time('real_time_end')
                ->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('schedule_event_id')
                ->references('id')
                ->on('advisor_schedules')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisories');
    }
}
