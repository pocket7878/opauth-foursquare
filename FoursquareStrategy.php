<?php
/**
 * Foursquare strategy for Opauth
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 Pocket7878 (http://poketo7878.dip.jp)
 * @link         http://opauth.org
 * @package      Opauth.FoursquareStrategy
 * @license      MIT License
 */

class FoursquareStrategy extends OpauthStrategy{
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 * eg. array('app_id', 'app_secret');
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults = array(
		'redirect_uri' => '{complete_url_to_strategy}int_callback',
	);

	/**
	 * Auth request
	 */
	public function request(){
		$url = 'https://foursquare.com/oauth2/authenticate';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'response_type' => 'code',
			'redirect_uri' => $this->strategy['redirect_uri']
		);

		if (!empty($this->strategy['response_type'])) $params['response_type'] = $this->strategy['response_type'];
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after Foursquare's OAuth
	 */
	public function int_callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$url = 'https://foursquare.com/oauth2/access_token';
			$params = array(
				'client_id' =>$this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'grant_type' => 'authorization_code',
				'redirect_uri'=> $this->strategy['redirect_uri'],
				'code' => trim($_GET['code'])
			);
			$response = $this->serverGet($url, $params, null, $headers);
			
			$results = json_decode($response);

			if (!empty($results) && isset($results->access_token)){
				$info = $this->user_info($results->access_token);
				$user = $info->response->user;
				$this->auth = array(
					'provider' => 'Foursquare',
					'uid' => $user->id,
					'info' => array(
						'name' => $user->firstName.$user->lastName,
						'email' => $user->contact->email,
						'first_name' => $user->firstName,
						'last_name'  => $user->lastName,
						'location' => $user->homeCity,
						//Cope with new api results
						'photo' => $user->photo->prefix.'original'.$user->photo->suffix
					),
					'credentials' => array(
						'token' => $results->access_token,
					),
					'raw' => $info
				);
				
				$this->callback();
			}
			else{
				$error = array(
					'provider' => 'Foursquare',
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => $headers
				);

				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'provider' => 'Foursquare',
				'code' => $_GET['error'],
				'message' => $_GET['error_description'],
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Foursquare API for user info
	 *
	 * @param string $access_token 
	 * @return array Parsed JSON results
	 */
	private function user_info($oauth_token){
		$info = $this->serverGet('https://api.foursquare.com/v2/users/self', 
					array('oauth_token' => $oauth_token,'v'=>date("Ymd",time())) , null, $headers);
		if (!empty($info)){
			$res = json_decode($info);
			if($res->meta->code == 200) {
				return $res;
			}
			else {
				$error = array(
					'provider' => 'Foursquare',
					'code' => 'user_info_error',
					'message' => 'Failed when attempting to get user information.',
					'raw' => array(
						'response' => $info,
						'headers' => $headers
					)
				);
				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'provider' => 'Foursquare',
				'code' => 'user_info_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $info,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
		}
}
