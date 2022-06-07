<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('qlinks')) {
            Schema::dropIfExists('qlinks');
        }

        Schema::create('qlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('lock')->nullable();
            $table->string('key')->nullable();
            $table->string('usage_count')->nullable();
            $table->string('visitor_id')->nullable();  // unique from the customer ie. email or user login id
            $table->text('access_link');  // full link shared with customer_id above
            $table->string('queue_id')->nullable();
            $table->string('visitor_identity_key')->nullable(); // Visitor Identity Key from the IOWR excel info; key in API payload
            $table->string('token_identifier');
            $table->timeTz('redirect_time_utc')->nullable();  // from redirectDetails['redirectTimeUtc']
            $table->string('queue_number')->nullable();
            $table->string('event_id')->nullable(); // iowr event id
            $table->string('rlwr_event_id')->nullable();    // request a link waiting room event id
            $table->string('rlwr_queue_id')->nullable();    // request link waiting room queue id
            $table->boolean('rlwr_queue_id_used')->nullable();    // request link waiting room queue id has been used
            $table->timestamps();

            $table->unique('token_identifier');
            $table->index('rlwr_queue_id');
            $table->index('rlwr_event_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qlinks');
    }
};
