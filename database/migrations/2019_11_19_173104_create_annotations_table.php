<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAnnotationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pdf_id')->index();
            $table->integer('page')->index();
            $table->text('properties');
            $table->text('ranges')->nullable();
            $table->text('shapes')->nullable();
            $table->text('quote')->nullable();
            $table->integer('created_by')->index();
            $table->integer('updated_by')->index()->nullable();
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
        Schema::drop('annotations');
    }
}