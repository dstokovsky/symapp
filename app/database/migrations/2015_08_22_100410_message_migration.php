<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MessageMigration extends Migration
{
    /**
     * Migrate Up
     */
    public function up()
    {
        if(!Schema::hasTable('message')){
            Schema::create('message', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->increments('id');
                $table->integer('author_id');
                $table->integer('recipient_id');
                $table->string('text', 200)->default('');
                $table->timestamps();
                $table->index(['author_id', 'recipient_id']);
                $table->index('author_id');
                $table->index('recipient_id');
            });
        }
    }
    
    /**
     * Migrate Down
     */
    public function down()        
    {
        if(Schema::hasTable('message')){
            Schema::drop('message');
        }
    }
}
