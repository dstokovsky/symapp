<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BlackListMigration extends Migration
{
    /**
     * Migrate Up
     */
    public function up()
    {
        if(!Schema::hasTable('blacklist')){
            Schema::create('blacklist', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('banned_user_id');
                $table->timestamps();
                $table->index(['user_id', 'banned_user_id']);
                $table->index('user_id');
                $table->index('banned_user_id');
            });
        }
    }
    
    /**
     * Migrate Down
     */
    public function down()
    {
        if(Schema::hasTable('blacklist')){
            Schema::drop('blacklist');
        }
    }
}
