<?php

namespace LLKI\Inc\Util;

final class Helper
{

	public static function get_LeagueLabLeagues($site, $token)
	{
		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => "https://api.leaguelab.com/v1/sites/" . $site . "/leagues",
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
			CURLOPT_URL => "https://api.leaguelab.com/v1/sites/" . $site . "/leagues/" . $league . "/teams",
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
					'phone_number' => '+1' . $arguments['phone'],
					'first_name' => $arguments['first_name'],
					'last_name' => $arguments['last_name'],
					'properties' => array(
						'League Name' => $arguments['league_name'],
						'Team Name' => $arguments['team_name'],
						'Captain' => $arguments['is_captain'],
						'Team Status' => $arguments['team_status']
					)
				)
			)
		);

		$args = array(
			'headers' => $headers,
			'body' => wp_json_encode($data)
		);

		$response = wp_remote_post($api_url, $args);
		if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 201) {
			return ['code' => 201, 'message' => 'Profile succefull registered in Klaviyo.'];
		} else {
			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Klaviyo Error.';
			return ['code' => 500, 'message' => 'Error registering profile in Klaviyo: ' . $error_message];
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
					'phone_number' => '+1' . $arguments['phone'],
					'first_name' => $arguments['first_name'],
					'last_name' => $arguments['last_name'],
					'properties' => array(
						'League Name' => $arguments['league_name'],
						'Team Name' => $arguments['team_name'],
						'Captain' => $arguments['is_captain'],
						'Team Status' => $arguments['team_status']
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
			return 'Profile updated successfully in Klaviyo.';
		} else {
			// Error: Failed to update profile in Klaviyo
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
			return 'Profiles added successfully in Klaviyo list.';
		} else {
			// Error: Failed to register profiles in Klaviyo list
			$error_message = is_wp_error($response) ? $response->get_error_message() : 'Error in the request to Klaviyo.';
			return 'Error adding profiles in Klaviyo list: ' . $error_message;
		}
	}

}
