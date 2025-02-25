<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhook', function (Blueprint $table) {
            $table->id();
            $table->timestamps();


            // salsify payload fields

            $table->char('status', 24)
                ->nullable()
                ->comment('salsify status');

            $table->char('started_at', 25)
                ->nullable()
                ->comment('salsify export started at');

            $table->char('ended_at', 25)
                ->nullable()
                ->comment('salsify export ended at');

            $table->mediumText('product_feed_export_url')
                ->nullable()
                ->comment('remote uri of json file');

            $table->mediumText('digital_asset_file_export_url')
                ->nullable()
                ->comment('remote uri of digital asset payload');

            $table->char('product_export_stats', 255)
                ->nullable()
                ->comment('salsify stats?');

            $table->char('export_status', 24)
                ->nullable()
                ->comment('salsify export status');

            $table->char('delivery_status', 255)
                ->nullable();

            $table->char('external_processing_status', 255)
                ->nullable();

            $table->char('notification_status', 100)
                ->nullable();


            // our custom fields

            $table->text('request_body')
                ->nullable()
                ->comment('original json request body');

            $table->text('downloaded_payload_filename')
                ->nullable()
                ->comment('uri to downloaded file');

            $table->unsignedTinyInteger('prepared_status')
                ->default(1)
                ->comment('unprepared,prepared,failed,complete');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhook');
    }
}
