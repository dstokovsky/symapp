<?php

class Setting extends Eloquent
{
    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
    protected $table = 'user_settings';
    
    public function user()
    {
        return $this->belongsTo('User');
    }
}
