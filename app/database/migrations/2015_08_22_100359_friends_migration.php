<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FriendsMigration extends Migration
{
    /**
     * Migrate Up
     */
    public function up()
    {
        if(!Schema::hasTable('users_friends')){
            Schema::create('users_friends', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('friend_id');
                $table->timestamps();
                $table->unique(['user_id', 'friend_id']);
                $table->index('user_id');
                $table->index('friend_id');
            });
        }
    }
    
    /**
     * Migrate Down
     */
    public function down()
    {
        if(Schema::hasTable('users_friends')){
            Schema::drop('users_friends');
        }
    }
}
