<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_goal_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('short_code')->nullable();
            $table->integer('project_id')->nullable();
            $table->integer('pm_id')->nullable();
            $table->string('event_details')->nullable();
            $table->dateTime('event_date')->nullable();
            $table->integer('pm_response')->nullable();
            $table->integer('admin_resolve')->nullable();
            $table->text('pm_reason')->nullable();
            $table->integer('admin_rating')->nullable();
            $table->text('admin_suggest')->nullable();
            $table->text('admin_review')->nullable();
            $table->integer('event_status')->nullable();
            $table->integer('client_id')->nullable();
            $table->string('project_category')->nullable();
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
        Schema::dropIfExists('project_goal_statuses');
    }
};
