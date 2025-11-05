<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Osiset\ShopifyApp\Util;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(Util::getShopsTable(), function (Blueprint $table) {
            $table->id(); // Standard 'id' primary key
            $table->string('name'); // This is the shop domain
            $table->string('email')->nullable();
            $table->string('password', 100)->nullable();
            $table->string('shopify_token')->nullable(); // Access Token
            $table->boolean('shopify_private')->default(false);
            $table->boolean('shopify_grandfathered')->default(false);
            $table->string('shopify_namespace')->nullable();
            $table->boolean('shopify_freemium')->default(false);
            $table->integer('plan_id')->unsigned()->nullable();
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at (withTrashed problemi yasadigim icin koydum)

            // Indexler
            $table->unique('name');
            $table->foreign('plan_id')->references('id')->on(Util::getShopifyConfig('table_names.plans', 'plans'));
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists(Util::getShopsTable());
    }
}
