<?php

class Blacklist extends Eloquent
{
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'blacklist';
    
    public function user()
    {
        return $this->belongsTo('User');
    }
    
    public function banned()
    {
        return $this->belongsTo('User', 'banned_user_id', 'id');
    }
}
