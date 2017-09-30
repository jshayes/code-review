<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestedReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requested_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pull_request_id');
            $table->string('reviewer_id');
            $table->dateTime('requested_at');
            $table->timestamps();

            $table->unique(['pull_request_id', 'reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requested_reviews');
    }
}
