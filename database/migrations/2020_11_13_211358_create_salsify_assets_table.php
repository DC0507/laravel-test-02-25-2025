<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalsifyAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salsify_assets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->char('salsify_id', 40);
            $table->mediumText('source_url');
            $table->mediumText('url');
            $table->char('name', 255);
            $table->char('format', 16);
            $table->char('etag', 40);
            $table->char('resource_type', 40);
            $table->char('filename', 255);
            $table->char('system_id', 255);
            $table->integer('width');
            $table->integer('height');
            $table->integer('bytes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salsify_assets');
    }
}
