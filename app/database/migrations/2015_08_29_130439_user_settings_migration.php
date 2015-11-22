<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSettingsMigration extends Migration
{
    /**
     * Migrate Up
     */
    public function up()
    {
        if(!Schema::hasTable('user_settings')){
            Schema::create('user_settings', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id');
                $table->string('name');
                $table->string('value')->default('');
                $table->timestamps();
                $table->index(['user_id', 'name']);
                $table->index('user_id');
                $table->primary('id');
            });
        }
    }
    
    /**
     * Migrate Down
     */
    public function down()
    {
        if(Schema::hasTable('user_settings')){
            Schema::drop('user_settings');
        }
    }
}
