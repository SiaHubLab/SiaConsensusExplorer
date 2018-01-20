<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class StatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('route')->unique();
            $table->timestamps();
        });

        Schema::create('stats', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('route_id')->unsigned()->index();
            $table->double('execution_time');
            $table->ipAddress('source');
            $table->text('request_data');
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
        Schema::dropIfExists('routes');
        Schema::dropIfExists('stats');
    }
}
