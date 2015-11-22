<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserMigration extends Migration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        if(!Schema::hasTable('user')){
            Schema::create('user', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->string('email', 100)->unique();
                $table->string('password', 200);
                $table->enum('account', ['email', 'facebook', 'linkedin']);
                $table->string('first_name', 50)->default('');
                $table->string('second_name', 50)->default('');
                $table->primary('id');
                $table->timestamps();
            });
        }
    }
    
    /**
     * Migrate Down.
     */
    public function down()
    {
        if(Schema::hasTable('user')){
            Schema::drop('user');
        }
    }
}
