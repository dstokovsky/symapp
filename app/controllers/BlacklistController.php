<?php

use Illuminate\Http\Response;

class BlacklistController extends \BaseController
{

    /**
     * Adds user with banned user id into user's id blacklist.
     * 
     * @param int $user_id
     * @param int $banned_user_id
     * @return Response
     */
	public function add($user_id, $banned_user_id)
    {
        if(empty($user_id)){
            return new Response('User id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($banned_user_id)){
            return new Response('Banned user id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if((int) $user_id === (int) $banned_user_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        if(Blacklist::whereRaw('user_id=? AND banned_user_id=?', [(int) $user_id, (int) $banned_user_id])->count() > 0){
            return new Response('Such record already exists', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        $banned_user = User::find((int) $banned_user_id);
        if(empty($banned_user)){
            return new Response('Banned user not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $blacklist = new Blacklist();
        $blacklist->user_id = $user->id;
        $blacklist->banned_user_id = $banned_user->id;
        $blacklist->save();
        
        if($blacklist->id){
            return new Response('Added', 201, ['Content-Type' => 'application/json']);
        }

        return new Response('Failed updating blacklist', 409, ['Content-Type' => 'application/json']);
    }

	/**
	 * Get the user's blacklist by its id.
	 *
	 * @param  int  $user_id
	 * @return Response
	 */
	public function show($user_id)
	{
        if(empty($user_id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $db = DB::table('blacklist');
        $db->join('user', 'blacklist.banned_user_id', '=', 'user.id')
            ->select('user.id', 'user.email', 'user.first_name', 'user.second_name')
            ->where('blacklist.user_id', '=', $user->id);
        if(Input::has('before')){
            $db->where('banned_user_id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('banned_user_id', '>', (int) Input::get('after'));
        }
        $users = $db->take(10)->orderBy('banned_user_id', 'asc')->get();
        
		return $users;
	}

	/**
	 * Removes user with banned user id from user's id blacklist.
	 *
	 * @param  int  $user_id
     * @param  int  $banned_user_id
	 * @return Response
	 */
	public function delete($user_id, $banned_user_id)
	{
        if(empty($user_id)){
            return new Response('User id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($banned_user_id)){
            return new Response('Banned user id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $user_id === (int) $banned_user_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        $banned_user = User::find((int) $banned_user_id);
        if(empty($banned_user)){
            return new Response('Banned user not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $affected_rows = Blacklist::where('user_id', '=', $user->id)
            ->where('banned_user_id', '=', $banned_user->id)->delete();
        
        if($affected_rows > 0){
            return new Response('Deleted', 200, ['Content-Type' => 'application/json']);
        }
        return new Response('Nothing to delete', 409, ['Content-Type' => 'application/json']);
	}
}
