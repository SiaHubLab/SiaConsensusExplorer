<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableBlockMetrics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('block_metrics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('height');
            $table->string('difficulty');
            $table->string('estimatedhashrate');
            $table->timestamp('timestamp');
            $table->integer('transactions');
            $table->integer('new_file_contracts');
            $table->integer('revisioned_file_contracts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('block_metrics');
    }
}
