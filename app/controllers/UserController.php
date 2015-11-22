<?php

use Illuminate\Http\Response;

class UserController extends \BaseController
{

	/**
	 * Creates the new user.
	 *
	 * @return Response
	 */
	public function create()
	{
        $key = Input::get('key', '');
        if(!password_verify(Config::get('app.key'), $key)){
            return new Response('Forbidden', 403, ['Content-Type' => 'application/json']);
        }
		$user = json_decode(Input::get('user', ''));
        if(empty($user)){
            return new Response('No data specified for new user or it was an invalid JSON', 409, ['Content-Type' => 'application/json']);
        }
        if(!filter_var($user->email, FILTER_VALIDATE_EMAIL)){
            return new Response('Invalid email specified', 409, ['Content-Type' => 'application/json']);
        }
        if(!in_array($user->account, ['email', 'facebook', 'linkedin'])){
            return new Response('Invalid account type specified', 409, ['Content-Type' => 'application/json']);
        }
        if(strtolower($user->account) === 'email' && (!isset($user->password) || empty($user->password))){
            return new Response('For account type email password should be specified', 409, ['Content-Type' => 'application/json']);
        }
        if(User::where('email', '=', $user->email)->count() > 0){
            return new Response('The user with such email already exists', 409, ['Content-Type' => 'application/json']);
        }
        //Creating user table record
        $profile = new User();
        $profile->email = strtolower(trim($user->email));
        $profile->password = password_hash($user->password, PASSWORD_DEFAULT);
        $profile->account = $user->account;
        $profile->first_name = isset($user->first_name) ? $user->first_name : '';
        $profile->second_name = isset($user->second_name) ? $user->second_name : '';
        $profile->save();
        if(!$profile->id){
            return new Response('Failed creating new user', 409, ['Content-Type' => 'application/json']);
        }
        $user->id = $profile->id;
        //Creating additional optional user's settings
        foreach ($user as $setting_name => $setting_value){
            if(is_array($setting_value)){
                foreach ($setting_value as $values){
                    if(is_object($values)){
                        foreach ($values as $sub_value_key => $sub_value){
                            if(is_array($sub_value) || is_object($sub_value)){
                                continue;
                            }
                            $setting = new Setting();
                            $setting->user_id = $profile->id;
                            $setting->name = implode(":", [$setting_name, $sub_value_key]);
                            $setting->value = strip_tags(trim($sub_value));
                            $setting->save();
                            if(!$setting->id){
                                $profile->delete();
                                return new Response('Failed creating new user settings', 409, ['Content-Type' => 'application/json']);
                            }
                        }
                    }
                    else
                    {
                        $setting = new Setting();
                        $setting->user_id = $profile->id;
                        $setting->name = $setting_name;
                        $setting->value = strip_tags(trim($values));
                        $setting->save();
                        if(!$setting->id){
                            $profile->delete();
                            return new Response('Failed creating new user settings', 409, ['Content-Type' => 'application/json']);
                        }
                    }
                }
            }
        }
        
        $client_id = md5(Config::get('app.key') . $profile->id . $profile->created_at);
        $client_secret = md5(Config::get('app.key') . $profile->email . $profile->created_at);
        DB::table('oauth_clients')->insert(
            ['id' => $client_id, 'secret' => $client_secret, 'name' => implode('-', [$profile->id, $profile->email]),
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),]
        );
        $user->client_id = $client_id;
        $user->client_secret = $client_secret;
        
        return (array)$user;
	}

	/**
	 * Get the user profile by its id.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if(empty($id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
		$user = User::find((int) $id);
        if(empty($user)){
            return new Response('User not found', 404, ['Content-Type' => 'application/json']);
        }
        $user_settings = $user->settings()->get()->toArray();
        $profile = $user->toArray();
        foreach ($user_settings as $setting){
            if(stristr($setting['name'], ":"))
            {
                $path = explode(":", $setting['name']);
                $key = $path[0];
                $sub_key = $path[1];
                if(!isset($profile[$key]))
                {
                    $counter = 0;
                    $profile[$key] = [];
                }

                if(isset($profile[$key][$counter][$sub_key]))
                {
                    $counter++;
                }
                $profile[$key][$counter][$sub_key] = $setting['value'];
            }
            elseif(!empty ($setting['name']))
            {
                $profile[$setting['name']][] = $setting['value'];
            }
        }
        
        return $profile;
	}

	/**
	 * Updates the profile of given user.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		return ['edit', $id];
	}

	/**
	 * Removes the user by given id.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function delete($id)
	{
		if(empty($id)){
            return new Response('No user id specified', 409, ['Content-Type' => 'application/json']);
        }
        $user = User::find((int) $id);
        $affected_rows = $user->settings()->delete();
        $affected_rows += $user->delete();
        if($affected_rows > 0){
            return new Response('Deleted', 200, ['Content-Type' => 'application/json']);
        }
        return new Response('Nothing to delete', 409, ['Content-Type' => 'application/json']);
	}
}
