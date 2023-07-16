<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

class Sincro
{

    public function register() {

        add_action('llki_daily_sync_event', array($this, 'llki_run_daily_sync'));

    }

    public function llki_run_daily_sync() {

		$active_leagues_type = get_option('active_leagues_type');
        if($active_leagues_type===2){
            $this->llki_sync_by_name();
        }else{
            $this->llki_sync_by_id();
        }
        
    }

    public function llki_sync_by_name(){
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

			//get all leagues
			$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

			if (isset($leagues->leagues)) {

				$profilesToRegister = array();

				$profilesNumber = $add_with_consent == 1 ? 100 : 1000;
				
				foreach ($leagues->leagues as $keyl => $league) {
					
					foreach ($active_leagues as $key => $active) {
						
						if (stripos( $league->name,$active) !== false) {
							
							$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $league->id);

							foreach ($teamsByLeague->teams as $keyt => $team) {
								
								foreach ($team->players as $keyp => $player) {

									$profile = Helper::getKlaviyoProfiles($klaviyo_api_key, $player->email);

									if (count($profile->data) == 0) {
										//add new
										$arguments = [
											'email'			=>	$player->email,
											'phone'			=>	$player->phone,
											'first_name'	=>	$player->first_name,
											'last_name'		=>	$player->last_name,
											'league_name'	=>	$league->name,
											'team_name'		=>	$team->team_name,
											'is_captain'	=>	$player->captain,
											//'team_status'	=>	'Active'
										];
										//$newP = Helper::registerKlaviyoProfiles($klaviyo_api_key, $arguments);							
									} else {
										//update
										$arguments = [
											'email'			=>	$player->email,
											'phone'			=>	$player->phone,
											'first_name'	=>	$player->first_name,
											'last_name'		=>	$player->last_name,
											'league_name'	=>	$league->name,
											'team_name'		=>	$team->team_name,
											'is_captain'	=>	$player->captain,
											//'team_status'	=>	'Active',
											'profile_id'	=>	$profile->data[0]->id
										];
										//$updtP = Helper::updateKlaviyoProfile($klaviyo_api_key, $arguments);
									}
									
									if (count($profilesToRegister) == $profilesNumber) {
										if ($add_with_consent == 1) {
											//$subL=Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
										} else {
											//$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
										}										
										var_dump(count($profilesToRegister));
										$profilesToRegister = array();										
									} else {
										if ($add_with_consent == 1) {
											$prof = array(
												'channels' => array(
													'email' => array('MARKETING'),
													'sms' => array('MARKETING')
												),
												'email' => $arguments['email'],
												'phone_number' => $arguments['phone'],
												'profile_id' => $profile->data[0]->id
											);
										} else {
											$prof = array(
												'type' => 'profile',
												'id' => $profile->data[0]->id
											);
										}
										$profilesToRegister[] = $prof;
									}
								}
							}
							break;
						}					
					}										
				}
				if(count($profilesToRegister)>0 && count($profilesToRegister) < $profilesNumber){

					if ($add_with_consent == 1) {
						//$subL=Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
					} else {
						//$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
					}					
					var_dump(count($profilesToRegister));
				}
			} else {
				Logs::register(json_encode($leagues));
			}
		} catch (\Throwable $th) {
			Logs::register(json_encode($th->getMessage()));
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

			$profilesToRegister = array();

			$profilesNumber = $add_with_consent == 1 ? 100 : 1000;

			foreach ($activeLeagues as $key => $active) {

				$league = Helper::get_LeagueLabLeaguesById($site, $league_lab_api_key, $active)->leagues[0];

				$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $active);

				foreach ($teamsByLeague->teams as $keyt => $team) {

					foreach ($team->players as $keyp => $player) {

						$profile = Helper::getKlaviyoProfiles($klaviyo_api_key, $player->email);

						if (count($profile->data) == 0) {
							//add new
							$arguments = [
								'email'			=>	$player->email,
								'phone'			=>	$player->phone,
								'first_name'	=>	$player->first_name,
								'last_name'		=>	$player->last_name,
								'league_name'	=>	$league->name,
								'team_name'		=>	$team->team_name,
								'is_captain'	=>	$player->captain,
								//'team_status'	=>	'Active'
							];
							//$newP = Helper::registerKlaviyoProfiles($klaviyo_api_key, $arguments);							
						} else {
							//update
							$arguments = [
								'email'			=>	$player->email,
								'phone'			=>	$player->phone,
								'first_name'	=>	$player->first_name,
								'last_name'		=>	$player->last_name,
								'league_name'	=>	$league->name,
								'team_name'		=>	$team->team_name,
								'is_captain'	=>	$player->captain,
								//'team_status'	=>	'Active',
								'profile_id'	=>	$profile->data[0]->id
							];
							//$updtP = Helper::updateKlaviyoProfile($klaviyo_api_key, $arguments);
						}

						if (count($profilesToRegister) == $profilesNumber) {
							if ($add_with_consent == 1) {
								//$subL=Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
							} else {
								//$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
							}
							var_dump(count($profilesToRegister));
							$profilesToRegister = array();
						} else {
							if ($add_with_consent == 1) {
								$prof = array(
									'channels' => array(
										'email' => array('MARKETING'),
										'sms' => array('MARKETING')
									),
									'email' => $arguments['email'],
									'phone_number' => $arguments['phone'],
									'profile_id' => $profile->data[0]->id
								);
							} else {
								$prof = array(
									'type' => 'profile',
									'id' => $profile->data[0]->id
								);
							}
							$profilesToRegister[] = $prof;
						}
					}
				}
			}

			if (count($profilesToRegister) > 0 && count($profilesToRegister) < $profilesNumber) {

				if ($add_with_consent == 1) {
					//$subL=Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
				} else {
					//$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
				}
				var_dump(count($profilesToRegister));
			}
		} catch (\Throwable $th) {
			Logs::register(json_encode($th->getMessage()));
		}
	}
    
}
