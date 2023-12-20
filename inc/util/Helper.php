<?php

namespace LLKI\Inc\Util;

use LLKI\Inc\Base\Logs;

final class Helper
{

	public static function get_LeagueLabLeagues($site, $token)
	{
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.leaguelab.com/v1/sites/" . $site . "/leagues-info",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Content-Type: application/json",
				"X-Client-Token: " . $token
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {			
			return json_decode($response);
		}
	}

	public static function get_LeagueLabLeaguesById($site, $token, $leagueId)
	{
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.leaguelab.com/v1/sites/" . $site . "/leagues-info/".$leagueId,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Content-Type: application/json",
				"X-Client-Token: " . $token
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {			
			return json_decode($response);
		}
	}

	public static function get_LeagueLabTeamsByLeagues($site, $token, $league)
	{
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.leaguelab.com/v1/sites/" . $site . "/leagues/" . $league . "/teams/show-all/",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => [
				"Content-Type: application/json",
				"X-Client-Token: " . $token
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			return "cURL Error #:" . $err;
		} else {
			return json_decode($response);
		}
	}

	public static function get_LeagueLab_Individuals($site, $token, $league)
	{

		$api_url = 'https://api.leaguelab.com/v1/sites/'.$site.'/leagues/'.$league.'/individuals';
		$headers = array(
			'X-Client-Token' => $token,
		);

		$args = array(
			'headers' => $headers
		);

		$response = wp_remote_get($api_url, $args);

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$body = wp_remote_retrieve_body($response);
			$individuals = json_decode($body);

			return $individuals;
		} else {

			$error_message = is_wp_error($response) ? $response->get_error_message() : 'League Lab Error.';
			return ['code' => 500, 'message' => 'Error to get individuals from League Lab ' . $error_message];
		}
	}
	public static function getKlaviyoProfiles($klaviyo_api_key, $email)
	{

		$api_url = 'https://a.klaviyo.com/api/profiles/?filter=equals(email,"' . urlencode($email) . '")';
		$headers = array(
			'Authorization' => 'Klaviyo-API-Key ' . $klaviyo_api_key,
			'Accept' => 'application/json',
			'revision' => '2023-06-15'
		);

		$args = array(
			'headers' => $headers
		);

		$response = wp_remote_get($api_url, $args);

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			$body = wp_remote_retrieve_body($response);
			$profiles = json_decode($body);

			return $profiles;
		} else {

			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Klaviyo Error.';
			return ['code' => 500, 'message' => 'Error to get profiles from Klaviyo: ' . $error_message];
		}
	}

	public static function registerKlaviyoProfiles($klaviyo_api_key, $arguments)
	{

		$api_url = 'https://a.klaviyo.com/api/profiles';
		$headers = array(
			'Authorization' => 'Klaviyo-API-Key ' . $klaviyo_api_key,
			'Accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-06-15'
		);
		
		$data = array(
			'data' => array(
				'type' => 'profile',
				'attributes' => array(
					'email' => $arguments['email'],
					'phone_number' => '+1'. $arguments['phone'],
					'first_name' => $arguments['first_name'],
					'last_name' => $arguments['last_name'],
					'properties' => array(
						'League Name' => $arguments['league_name'],
						'Team Name' => $arguments['team_name'],
						'Captain' => $arguments['is_captain'],
						'Player Status' => $arguments['player_status'],
						'Team Status' => $arguments['team_status'],
						'Sport'		=>$arguments['sports']
					)
				)
			)
		);

		$args = array(
			'headers' => $headers,
			'body' => wp_json_encode($data)
		);
		Logs::register('Agregando');
		Logs::register($arguments['email']);		
		$response = wp_remote_post($api_url, $args);
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 201) {
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);
			return ['code' => 201, 'message' => 'Profile succefull registered in Klaviyo.', 'response'=> $data];
		} else {
			Logs::register('Error');
			Logs::register($arguments['email']);
			Logs::register(json_encode($response));
			$body = wp_remote_retrieve_body($response);
			$data = json_decode($body, true);			
			return ['code' => 500, 'message' => 'Error registering profile in Klaviyo: ' . $data['errors']];
		}
	}

	public static function updateKlaviyoProfile($klaviyo_api_key, $arguments){		 

		$api_url = 'https://a.klaviyo.com/api/profiles/' . $arguments['profile_id'] . '/';
		$headers = array(
			'Authorization' => 'Klaviyo-API-Key ' . $klaviyo_api_key,
			'Accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-06-15'
		);

		$data = array(
			'data' => array(
				'type' => 'profile',
				'id' => $arguments['profile_id'],
				'attributes' => array(
					'email' => $arguments['email'],
					'phone_number' => $arguments['phone'],
					'first_name' => $arguments['first_name'],
					'last_name' => $arguments['last_name'],
					'properties' => array(
						'League Name' => $arguments['league_name'],
						'Team Name' => $arguments['team_name'],
						'Captain' => $arguments['is_captain'],
						'Player Status' => $arguments['player_status'],
						'Team Status' => $arguments['team_status'],
						'Sport'		=>$arguments['sports']
					)
				)
			)
		);

		$args = array(
			'method' => 'PATCH',
			'headers' => $headers,
			'body' => wp_json_encode($data)
		);

		$response = wp_remote_request($api_url, $args);

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
			// Success: Profile updated in Klaviyo
			Logs::register('Update successfully');
			Logs::register($arguments['email']);
			return 'Profile updated successfully in Klaviyo.';
		} else {
			// Error: Failed to update profile in Klaviyo
			Logs::register('Update Error');
			Logs::register($arguments['email']);
			Logs::register(json_encode($response));
			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Error in the request to Klaviyo.';
			return 'Error updating profile in Klaviyo: ' . $error_message;
		}
	}
	public static function subscribeProfilesToKlaviyoList($klaviyo_api_key, $list_id, $profiles)
	{		

		$api_url = 'https://a.klaviyo.com/api/profile-subscription-bulk-create-jobs/';
		$headers = array(
			'Authorization' => 'Klaviyo-API-Key ' . $klaviyo_api_key,
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-06-15'
		);

		$data = array(
			'data' => array(
				'type' => 'profile-subscription-bulk-create-job',
				'attributes' => array(
					'list_id' => $list_id,
					'custom_source' => 'Marketing Event',
					'subscriptions' => $profiles
				)
			)
		);

		$args = array(
			'headers' => $headers,
			'body' => wp_json_encode($data)
		);

		$response = wp_remote_post($api_url, $args);

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 202) {			
			return 'Profiles suscribed successfully in Klaviyo list.';
		} else {
			// Error: Failed to register profiles in Klaviyo list
			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Error in the request to Klaviyo.';
			return 'Error suscribing profiles in Klaviyo list: ' . $error_message;
		}
	}

	public static function addProfilesToKlaviyoList($klaviyo_api_key, $list_id, $profiles)
	{

		$api_url = 'https://a.klaviyo.com/api/lists/'.$list_id.'/relationships/profiles/';
		$headers = array(
			'Authorization' => 'Klaviyo-API-Key ' . $klaviyo_api_key,
			'accept' => 'application/json',
			'content-type' => 'application/json',
			'revision' => '2023-06-15'
		);
		

		$data = array(
			'data' => $profiles			
		);

		$args = array(
			'headers' => $headers,
			'body' => wp_json_encode($data)
		);

		$response = wp_remote_post($api_url, $args);

		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 204) {
			//Logs::register('Profiles added successfully in Klaviyo list.');
			return 'Profiles added successfully in Klaviyo list.';
		} else {
			// Error: Failed to register profiles in Klaviyo list
			//Logs::register(json_encode($response));
			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Error in the request to Klaviyo.';
			//Logs::register('Error adding profiles in Klaviyo list: ' . $error_message);
			return 'Error adding profiles in Klaviyo list: ' . $error_message;
		}
	}

}
