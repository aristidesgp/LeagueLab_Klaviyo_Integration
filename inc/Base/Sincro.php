<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

use LLKI\Inc\Base\Logs;
use LLKI\Inc\Util\Helper;

class Sincro
{

	public function register()
	{

		add_action('llki_daily_sync_event', array($this, 'llki_run_daily_sync'));

		//add_action('llki_ten_minutes_event', array($this, 'ten_min'));

		add_action('llki_one_hour_event', array($this, 'llki_schedule_hour'));
	}

	public function ten_min()
	{
		Logs::register("Ten minutes Cron");
	}
	public function llki_schedule_hour()
	{
		$json_array = get_option('active_leagues_list');
		$sincro_num = get_option('league_lab_sincro_number');
		$active_leagues = json_decode($json_array, true);
		

		if (is_array($active_leagues) && $sincro_num > 0) {
			
			for ($i = 0; $i < $sincro_num; $i++) {
				$leagueId = array_shift($active_leagues);
				if ($this->llki_hour_sync($leagueId)) {					
					update_option('active_leagues_list', json_encode($active_leagues));
				}else {					
					array_push($active_leagues, $leagueId);
					update_option('active_leagues_list', json_encode($active_leagues));
				}
			}
		}

	}

	public function llki_run_daily_sync()
	{
		$league_lab_api_key = get_option('league_lab_api_key');
		$site = get_option('league_lab_site');
		$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);
		$active_leagues = array();
		if (isset($leagues->leagues)) {
			foreach ($leagues->leagues as $key => $league) {
				if ($league->status == 'In Progress') {
					$active_leagues[] = $league->id;
				}
			}
		}
		update_option('active_leagues_list', json_encode($active_leagues));


		/* $active_leagues_type = get_option('active_leagues_type');
		if ($active_leagues_type === '2') {
			return $this->llki_sync_by_name();
		} else {
			return $this->llki_sync_by_id();
		} */
	}

	public function llki_hour_sync($leagueId)
	{
		try {
			//League Lab vars
			$league_lab_api_key = get_option('league_lab_api_key');
			$site = get_option('league_lab_site');		

			//Klaviyo vars
			$klaviyo_api_key = get_option('klaviyo_api_key');
			$klaviyo_list_id = get_option('klaviyo_list_id');
			$add_with_consent = get_option('klaviyo_add_with_consent');

			$league = Helper::get_LeagueLabLeaguesById($site, $league_lab_api_key, $leagueId)->leagues[0];

			$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $leagueId);

			foreach ($teamsByLeague->teams as $keyt => $team) {				

				foreach ($team->players as $keyp => $player) {					

					$profile = Helper::getKlaviyoProfiles($klaviyo_api_key, $player->email);
					$profileId = 0;

					if (count($profile->data) == 0) {
						//add new
						$arguments = [
							'email'			=>	$player->email,
							'phone'			=>	$player->phone,
							'first_name'	=>	$player->first_name,
							'last_name'		=>	$player->last_name,
							'league_name'	=>	[$league->name],
							'team_name'		=>	[$team->team_name],
							'is_captain'	=>	$player->captain,
							'team_status'	=>	[$team->registration_status],
							'player_status'	=>	[$player->player_status],
						];

						$newP = Helper::registerKlaviyoProfiles($klaviyo_api_key, $arguments);
						$profileId = $newP['response']['data']['id'];
					} else {
						$latribute = 'League Name';
						$tatribute = 'Team Name';
						$pstatus = 'Player Status';
						$tstatus = 'Team Status';
						$profileLeagues = isset($profile->data[0]->attributes->properties->$latribute) ? $profile->data[0]->attributes->properties->$latribute : [];
						$profileTeams = isset($profile->data[0]->attributes->properties->$tatribute) ? $profile->data[0]->attributes->properties->$tatribute : [];

						$profilePstatus = isset($profile->data[0]->attributes->properties->$pstatus) ? $profile->data[0]->attributes->properties->$pstatus : [];
						$profileTstatus = isset($profile->data[0]->attributes->properties->$tstatus) ? $profile->data[0]->attributes->properties->$tstatus : [];

						$isLeague = false;
						if (is_array($profileLeagues)) {
							foreach ($profileLeagues as $key => $pleague) {
								if ($pleague == $league->name) {
									$isLeague = true;
									break;
								}
							}
							if (!$isLeague)
								$profileLeagues[] = $league->name;
						} else {
							$profileLeagues = [$league->name];
						}

						$isTeam = false;
						if (is_array($profileTeams)) {
							foreach ($profileTeams as $key => $pTeam) {
								if ($pTeam == $team->team_name) {
									$isTeam = true;
									break;
								}
							}
							if (!$isTeam)
								$profileTeams[] = $team->team_name;
						} else {
							$profileTeams = [$team->team_name];
						}

						$isPlayerStatus = false;
						if (is_array($profilePstatus)) {
							foreach ($profilePstatus as $key => $pTeam) {
								if ($pTeam == $player->player_status) {
									$isPlayerStatus = true;
									break;
								}
							}
							if (!$isPlayerStatus)
								$profilePstatus[] = $player->player_status;
						} else {
							$profilePstatus = [$player->player_status];
						}

						$isTeamStatus = false;
						if (is_array($profileTstatus)) {
							foreach ($profileTstatus as $key => $pTeam) {
								if ($pTeam == $team->registration_status) {
									$isTeamStatus = true;
									break;
								}
							}
							if (!$isTeamStatus)
								$profileTstatus[] = $team->registration_status;
						} else {
							$profileTstatus = [$team->registration_status];
						}

						$phoneNumber = $player->phone;
						if ($phoneNumber == null)
							$phoneNumber = $profile->data[0]->attributes->phone_number;
						else
							$phoneNumber = '+1' . $phoneNumber;


						//update
						$arguments = [
							'email'			=>	$player->email,
							'phone'			=>	$phoneNumber,
							'first_name'	=>	$player->first_name,
							'last_name'		=>	$player->last_name,
							'league_name'	=>	$profileLeagues,
							'team_name'		=>	$profileTeams,
							'is_captain'	=>	$player->captain,
							'player_status'	=>	$profilePstatus,
							'team_status'	=>	$profileTstatus,
							'profile_id'	=>	$profile->data[0]->id
						];
						$updtP = Helper::updateKlaviyoProfile($klaviyo_api_key, $arguments);
						$profileId = $profile->data[0]->id;
					}
					if (!is_null($profileId)) {
						if ($add_with_consent == 1) {
							$prof = [array(
								'channels' => array(
									'email' => array('MARKETING'),
									'sms' => array('MARKETING')
								),
								'email' => $arguments['email'],
								'phone_number' => $arguments['phone'],
								'profile_id' => $profileId
							)];
							$subL = Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
						} else {
							$prof = [array(
								'type' => 'profile',
								'id' => $profileId
							)];
							$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
						}
					}
				}
			}
			return true;
		} catch (\Throwable $th) {
			return false;
			//Logs::register(json_encode($th->getMessage()));
		}
	}

	public function llki_sync_by_name()
	{
		try {
			//League Lab vars
			$league_lab_api_key = get_option('league_lab_api_key');
			$site = get_option('league_lab_site');
			$active_leagues = get_option('league_lab_active_leagues');
			$active_leagues = !empty($active_leagues) ? explode("\n", $active_leagues) : array();
			$active_leagues = array_map('trim', $active_leagues);

			//Klaviyo vars
			$klaviyo_api_key = get_option('klaviyo_api_key');
			$klaviyo_list_id = get_option('klaviyo_list_id');
			$add_with_consent = get_option('klaviyo_add_with_consent');

			/* $league = Helper::get_LeagueLabLeaguesById($site, $league_lab_api_key,60533);
			var_dump($league->leagues[0]->name);
			return; */
			$leaguesNumber = 0;
			$teamsNumber = 0;
			$playersNumber = 0;

			//get all leagues
			$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

			if (isset($leagues->leagues)) {

				foreach ($leagues->leagues as $keyl => $league) {

					foreach ($active_leagues as $key => $active) {

						if (stripos($league->name, $active) !== false) {

							$leaguesNumber = $leaguesNumber + 1;

							$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $league->id);

							foreach ($teamsByLeague->teams as $keyt => $team) {

								$teamsNumber = $teamsNumber + 1;

								foreach ($team->players as $keyp => $player) {

									$playersNumber = $playersNumber + 1;

									$profile = Helper::getKlaviyoProfiles($klaviyo_api_key, $player->email);
									$profileId = 0;

									if (count($profile->data) == 0) {
										//add new
										$arguments = [
											'email'			=>	$player->email,
											'phone'			=>	$player->phone,
											'first_name'	=>	$player->first_name,
											'last_name'		=>	$player->last_name,
											'league_name'	=>	[$league->name],
											'team_name'		=>	[$team->team_name],
											'is_captain'	=>	$player->captain,
											'team_status'	=>	[$team->registration_status],
											'player_status'	=>	[$player->player_status],
										];

										$newP = Helper::registerKlaviyoProfiles($klaviyo_api_key, $arguments);
										$profileId = $newP['response']['data']['id'];
									} else {
										$latribute = 'League Name';
										$tatribute = 'Team Name';
										$pstatus = 'Player Status';
										$tstatus = 'Team Status';
										$profileLeagues = isset($profile->data[0]->attributes->properties->$latribute) ? $profile->data[0]->attributes->properties->$latribute : [];
										$profileTeams = isset($profile->data[0]->attributes->properties->$tatribute) ? $profile->data[0]->attributes->properties->$tatribute : [];

										$profilePstatus = isset($profile->data[0]->attributes->properties->$pstatus) ? $profile->data[0]->attributes->properties->$pstatus : [];
										$profileTstatus = isset($profile->data[0]->attributes->properties->$tstatus) ? $profile->data[0]->attributes->properties->$tstatus : [];

										$isLeague = false;
										if (is_array($profileLeagues)) {
											foreach ($profileLeagues as $key => $pleague) {
												if ($pleague == $league->name) {
													$isLeague = true;
													break;
												}
											}
											if (!$isLeague)
												$profileLeagues[] = $league->name;
										} else {
											$profileLeagues = [$league->name];
										}

										$isTeam = false;
										if (is_array($profileTeams)) {
											foreach ($profileTeams as $key => $pTeam) {
												if ($pTeam == $team->team_name) {
													$isTeam = true;
													break;
												}
											}
											if (!$isTeam)
												$profileTeams[] = $team->team_name;
										} else {
											$profileTeams = [$team->team_name];
										}

										$isPlayerStatus = false;
										if (is_array($profilePstatus)) {
											foreach ($profilePstatus as $key => $pTeam) {
												if ($pTeam == $player->player_status) {
													$isPlayerStatus = true;
													break;
												}
											}
											if (!$isPlayerStatus)
												$profilePstatus[] = $player->player_status;
										} else {
											$profilePstatus = [$player->player_status];
										}

										$isTeamStatus = false;
										if (is_array($profileTstatus)) {
											foreach ($profileTstatus as $key => $pTeam) {
												if ($pTeam == $team->registration_status) {
													$isTeamStatus = true;
													break;
												}
											}
											if (!$isTeamStatus)
												$profileTstatus[] = $team->registration_status;
										} else {
											$profileTstatus = [$team->registration_status];
										}

										$phoneNumber = $player->phone;
										if ($phoneNumber == null)
											$phoneNumber = $profile->data[0]->attributes->phone_number;
										else
											$phoneNumber = '+1' . $phoneNumber;


										//update
										$arguments = [
											'email'			=>	$player->email,
											'phone'			=>	$phoneNumber,
											'first_name'	=>	$player->first_name,
											'last_name'		=>	$player->last_name,
											'league_name'	=>	$profileLeagues,
											'team_name'		=>	$profileTeams,
											'is_captain'	=>	$player->captain,
											'player_status'	=>	$profilePstatus,
											'team_status'	=>	$profileTstatus,
											'profile_id'	=>	$profile->data[0]->id
										];
										$updtP = Helper::updateKlaviyoProfile($klaviyo_api_key, $arguments);
										$profileId = $profile->data[0]->id;
									}
									if (!is_null($profileId)) {
										if ($add_with_consent == 1) {
											$prof = [array(
												'channels' => array(
													'email' => array('MARKETING'),
													'sms' => array('MARKETING')
												),
												'email' => $arguments['email'],
												'phone_number' => $arguments['phone'],
												'profile_id' => $profileId
											)];
											$subL = Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
										} else {
											$prof = [array(
												'type' => 'profile',
												'id' => $profileId
											)];
											$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
										}
									}
								}
							}
							break;
						}
					}
				}
			} else {
				Logs::register(json_encode($leagues));
			}
			return [
				'leagues'   => $leaguesNumber,
				'teams'     => $teamsNumber,
				'players'   => $playersNumber
			];
		} catch (\Throwable $th) {
			//Logs::register(json_encode($th->getMessage()));
		}
	}

	public function llki_sync_by_id()
	{
		try {
			//League Lab vars           
			$league_lab_api_key = get_option('league_lab_api_key');
			$site = get_option('league_lab_site');
			$activeLeagues = get_option('ll_active_leagues');


			//Klaviyo vars
			$klaviyo_api_key = get_option('klaviyo_api_key');
			$klaviyo_list_id = get_option('klaviyo_list_id');
			$add_with_consent = get_option('klaviyo_add_with_consent');

			$leaguesNumber = count($activeLeagues);
			$teamsNumber = 0;
			$playersNumber = 0;

			foreach ($activeLeagues as $key => $active) {
				//Logs::register($active);
				$league = Helper::get_LeagueLabLeaguesById($site, $league_lab_api_key, $active)->leagues[0];

				$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $active);

				foreach ($teamsByLeague->teams as $keyt => $team) {

					$teamsNumber = $teamsNumber + 1;

					foreach ($team->players as $keyp => $player) {

						$playersNumber = $playersNumber + 1;

						$profile = Helper::getKlaviyoProfiles($klaviyo_api_key, $player->email);
						$profileId = 0;

						if (count($profile->data) == 0) {
							//add new
							$arguments = [
								'email'			=>	$player->email,
								'phone'			=>	$player->phone,
								'first_name'	=>	$player->first_name,
								'last_name'		=>	$player->last_name,
								'league_name'	=>	[$league->name],
								'team_name'		=>	[$team->team_name],
								'is_captain'	=>	$player->captain,
								'team_status'	=>	[$player->registration_status],
								'player_status'	=>	[$player->player_status],
							];

							$newP = Helper::registerKlaviyoProfiles($klaviyo_api_key, $arguments);
							$profileId = $newP['response']['data']['id'];
						} else {
							$latribute = 'League Name';
							$tatribute = 'Team Name';
							$pstatus = 'Player Status';
							$tstatus = 'Team Status';
							$profileLeagues = isset($profile->data[0]->attributes->properties->$latribute) ? $profile->data[0]->attributes->properties->$latribute : [];
							$profileTeams = isset($profile->data[0]->attributes->properties->$tatribute) ? $profile->data[0]->attributes->properties->$tatribute : [];

							$profilePstatus = isset($profile->data[0]->attributes->properties->$pstatus) ? $profile->data[0]->attributes->properties->$pstatus : [];
							$profileTstatus = isset($profile->data[0]->attributes->properties->$tstatus) ? $profile->data[0]->attributes->properties->$tstatus : [];

							$isLeague = false;
							if (is_array($profileLeagues)) {
								foreach ($profileLeagues as $key => $pleague) {
									if ($pleague == $league->name) {
										$isLeague = true;
										break;
									}
								}
								if (!$isLeague)
									$profileLeagues[] = $league->name;
							} else {
								$profileLeagues = [$league->name];
							}

							$isTeam = false;
							if (is_array($profileTeams)) {
								foreach ($profileTeams as $key => $pTeam) {
									if ($pTeam == $team->team_name) {
										$isTeam = true;
										break;
									}
								}
								if (!$isTeam)
									$profileTeams[] = $team->team_name;
							} else {
								$profileTeams = [$team->team_name];
							}

							$isPlayerStatus = false;
							if (is_array($profilePstatus)) {
								foreach ($profilePstatus as $key => $pTeam) {
									if ($pTeam == $player->player_status) {
										$isPlayerStatus = true;
										break;
									}
								}
								if (!$isPlayerStatus)
									$profilePstatus[] = $player->player_status;
							} else {
								$profilePstatus = [$player->player_status];
							}

							$isTeamStatus = false;
							if (is_array($profileTstatus)) {
								foreach ($profileTstatus as $key => $pTeam) {
									if ($pTeam == $team->registration_status) {
										$isTeamStatus = true;
										break;
									}
								}
								if (!$isTeamStatus)
									$profileTstatus[] = $team->registration_status;
							} else {
								$profileTstatus = [$team->registration_status];
							}

							$phoneNumber = $player->phone;
							if ($phoneNumber == null)
								$phoneNumber = $profile->data[0]->attributes->phone_number;
							else
								$phoneNumber = '+1' . $phoneNumber;


							//update
							$arguments = [
								'email'			=>	$player->email,
								'phone'			=>	$phoneNumber,
								'first_name'	=>	$player->first_name,
								'last_name'		=>	$player->last_name,
								'league_name'	=>	$profileLeagues,
								'team_name'		=>	$profileTeams,
								'is_captain'	=>	$player->captain,
								'player_status'	=>	$profilePstatus,
								'team_status'	=>	$profileTstatus,
								'profile_id'	=>	$profile->data[0]->id
							];
							$updtP = Helper::updateKlaviyoProfile($klaviyo_api_key, $arguments);
							$profileId = $profile->data[0]->id;
						}
						if (!is_null($profileId)) {
							if ($add_with_consent == 1) {
								$prof = [array(
									'channels' => array(
										'email' => array('MARKETING'),
										'sms' => array('MARKETING')
									),
									'email' => $arguments['email'],
									'phone_number' => $arguments['phone'],
									'profile_id' => $profileId
								)];
								$subL = Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
							} else {
								$prof = [array(
									'type' => 'profile',
									'id' => $profileId
								)];
								$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $prof);
							}
						}
					}
				}
			}

			return [
				'leagues'   => $leaguesNumber,
				'teams'     => $teamsNumber,
				'players'   => $playersNumber
			];
		} catch (\Throwable $th) {
			//Logs::register(json_encode($th->getMessage()));
		}
	}
}
