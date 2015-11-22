<?php

use Illuminate\Http\Response;

class MessageController extends \BaseController
{

	/**
	 * Sends a message from author to recipient.
	 *
     * @param int $author_id
     * @param int $recipient_id
	 * @return Response
	 */
	public function post($author_id, $recipient_id)
	{
        if(empty($author_id)){
            return new Response('Author id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($recipient_id)){
            return new Response('Recipient id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $author_id === (int) $recipient_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        $author = User::find((int) $author_id);
        if(empty($author)){
            return new Response('Author user not found', 404, ['Content-Type' => 'application/json']);
        }
        $recipient = User::find((int) $recipient_id);
        if(empty($recipient)){
            return new Response('Recipient user not found', 404, ['Content-Type' => 'application/json']);
        }
        if(Blacklist::whereRaw('user_id=? and banned_user_id=?', [$recipient->id, $author->id])->count() > 0){
            return new Response("User #{$author->id} is in #{$recipient->id} user's blacklist", 
                409, ['Content-Type' => 'application/json']);
        }
        if(!Input::has('text') || Input::get('text', '') === ''){
            return new Response('Message text is missed', 409, ['Content-Type' => 'application/json']);
        }
        
        $text = strip_tags(trim(Input::get('text', '')));
        $model = new Message();
        $model->author_id = $author->id;
        $model->recipient_id = $recipient->id;
        $model->text = $text;
        $model->save();
        
        if($model->id){
            return new Response('Added', 201, ['Content-Type' => 'application/json']);
        }

        return new Response('Failed posting message', 409, ['Content-Type' => 'application/json']);
	}

	/**
	 * Returns all users with which current user had some communications.
	 *
     * @param int $user_id
	 * @return Response
	 */
	public function history($user_id)
	{
		if(empty($user_id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
		$user = User::find((int) $user_id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        
        $db = DB::table('message');
        $db->join('user', 'message.recipient_id', '=', 'user.id')
            ->select('user.id', 'user.email', 'user.first_name', 'user.second_name')
            ->where('message.author_id', '=', $user->id);
        if(Input::has('before')){
            $db->where('message.recipient_id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('message.recipient_id', '>', (int) Input::get('after'));
        }
        $history_author = $db->take(10)->orderBy('message.recipient_id', 'asc');
        
        $db = DB::table('message');
        $db->join('user', 'message.author_id', '=', 'user.id')
            ->select('user.id', 'user.email', 'user.first_name', 'user.second_name')
            ->where('message.recipient_id', '=', $user->id);
        if(Input::has('before')){
            $db->where('message.author_id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('message.author_id', '>', (int) Input::get('after'));
        }
        $history = $db->take(10)->orderBy('message.author_id', 'asc')->union($history_author)->get();
        
        return $history;
	}

	/**
	 * Returns the chat history between author and recipient.
	 *
	 * @return Response
	 */
	public function chat($author_id, $recipient_id)
	{
		if(empty($author_id)){
            return new Response('Author id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($recipient_id)){
            return new Response('Recipient id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $author_id === (int) $recipient_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        $author = User::find((int) $author_id);
        if(empty($author)){
            return new Response('Author user not found', 404, ['Content-Type' => 'application/json']);
        }
        $recipient = User::find((int) $recipient_id);
        if(empty($recipient)){
            return new Response('Recipient user not found', 404, ['Content-Type' => 'application/json']);
        }
        $db = DB::table('message');
        $db->select('id', 'text', 'created_at', 'updated_at')
            ->where(function($query) use ($author, $recipient){
                $query->where('author_id', '=', $author->id)->where('recipient_id', '=', $recipient->id);
            })
            ->orWhere(function($query) use ($author, $recipient){
                $query->where('author_id', '=', $recipient->id)->where('recipient_id', '=', $author->id);
            });
        if(Input::has('before')){
            $db->where('id', '<', (int) Input::get('before'));
        }
        if(Input::has('after')){
            $db->where('id', '>', (int) Input::get('after'));
        }
        $chat = $db->take(10)->orderBy('id', 'asc')->get();
        
        return $chat;
	}

	/**
	 * Removes the communication history between users.
	 *
	 * @param int $author_id
     * @param int $recipient_id
	 * @return Response
	 */
	public function delete($author_id, $recipient_id)
	{
		if(empty($author_id)){
            return new Response('Author id is missed', 409, ['Content-Type' => 'application/json']);
        }
        if(empty($recipient_id)){
            return new Response('Recipient id is missed', 409, ['Content-Type' => 'application/json']);
        }
		if((int) $author_id === (int) $recipient_id){
            return new Response('The same user ids given', 409, ['Content-Type' => 'application/json']);
        }
        
        $db = DB::table('message');
        $affected_rows = $db->select('id', 'text', 'created_at', 'updated_at')
            ->where(function($query) use ($author_id, $recipient_id){
                $query->where('author_id', '=', $author_id)->where('recipient_id', '=', $recipient_id);
            })
            ->orWhere(function($query) use ($author_id, $recipient_id){
                $query->where('author_id', '=', $recipient_id)->where('recipient_id', '=', $author_id);
            })->delete();
        if($affected_rows > 0){
            return new Response('Deleted', 200, ['Content-Type' => 'application/json']);
        }
        return new Response('Nothing to delete', 409, ['Content-Type' => 'application/json']);
	}
}
