<?php

use Illuminate\Http\Response;

class FriendController extends \BaseController
{

	/**
	 * Adds a friend to the given user.
	 *
     * @param int $user_id
     * @param int $friend_id
	 * @return Response
	 */
	public function add($user_id, $friend_id)
	{
        if(empty($user_id)){
            return new Response('User id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($friend_id)){
            return new Response('Friend id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $user_id === (int) $friend_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        if(Friend::whereRaw('user_id=? AND friend_id=?', [(int) $user_id, (int) $friend_id])->count() > 0){
            return new Response('Such record already exists', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        $friend = User::find((int) $friend_id);
        if(empty($friend)){
            return new Response('Friend user not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $model = new Friend();
        $model->user_id = $user->id;
        $model->friend_id = $friend->id;
        $model->save();
        
        if($model->id){
            return new Response('Added', 201, ['Content-Type' => 'application/json']);
        }

        return new Response('Failed adding friend', 409, ['Content-Type' => 'application/json']);
	}

	/**
	 * Returns all users which are followed by current user.
	 *
     * @param int $id
	 * @return Response
	 */
	public function follows($id)
	{
        if(empty($id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
		$user = User::find((int) $id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $db = DB::table('users_friends');
        $db->join('user', 'users_friends.friend_id', '=', 'user.id')
            ->select('user.id', 'user.email', 'user.first_name', 'user.second_name')
            ->where('users_friends.user_id', '=', $user->id);
        if(Input::has('before')){
            $db->where('users_friends.friend_id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('users_friends.friend_id', '>', (int) Input::get('after'));
        }
        $user_followers = $db->take(10)->orderBy('users_friends.friend_id', 'asc')->get();
        
        return $user_followers;
	}

	/**
	 * Returns all followers of current user.
	 *
	 * @return Response
	 */
	public function followers($id)
	{
        if(empty($id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
		$user = User::find((int) $id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $db = DB::table('users_friends');
        $db->join('user', 'users_friends.user_id', '=', 'user.id')
            ->select('user.id', 'user.email', 'user.first_name', 'user.second_name')
            ->where('users_friends.friend_id', '=', $user->id);
        if(Input::has('before')){
            $db->where('users_friends.user_id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('users_friends.user_id', '>', (int) Input::get('after'));
        }
        $users_followed = $db->take(10)->orderBy('users_friends.user_id', 'asc')->get();
        
        return $users_followed;
	}

	/**
	 * Removes a friend for the given user.
	 *
	 * @param int $user_id
     * @param int $friend_id
	 * @return Response
	 */
	public function delete($user_id, $friend_id)
	{
        if(empty($user_id)){
            return new Response('User id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($friend_id)){
            return new Response('Friend id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $user_id === (int) $friend_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        $friend = User::find((int) $friend_id);
        if(empty($friend)){
            return new Response('Friend user not found', 404, ['Content-Type' => 'application/json']);
        }
        $affected_rows = Friend::where('user_id', '=', $user->id)
            ->where('friend_id', '=', $friend->id)->delete();
        if($affected_rows > 0){
            return new Response('Deleted', 200, ['Content-Type' => 'application/json']);
        }
        return new Response('Nothing to delete', 409, ['Content-Type' => 'application/json']);
	}
}
