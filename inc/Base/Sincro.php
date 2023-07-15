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
        try {			
			return;
			//League Lab vars
			$league_lab_api_key = get_option('league_lab_api_key');
			$site = get_option('league_lab_site');
			$active_leagues = get_option('league_lab_active_leagues');
			$active_leagues = !empty($active_leagues) ? explode("\n", $active_leagues) : array();

			//Klaviyo vars
			$klaviyo_api_key = get_option('klaviyo_api_key');
			$klaviyo_list_id = get_option('klaviyo_list_id');
			$add_with_consent = get_option('klaviyo_add_with_consent');

			//get all leagues
			$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

			if (isset($leagues->leagues)) {

				$profilesToRegister = array();

				$profilesNumber = $add_with_consent == 1 ? 100 : 1000;

				foreach ($leagues->leagues as $keyl => $league) {

					//solo obtener los equipos de las ligas activas despues de la 1ra sincro.
					foreach ($active_leagues as $key => $active) {
						
						if (strpos($league->name, $active) !== false) {

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

									if (
										count($profilesToRegister) == $profilesNumber ||
										(count($leagues->leagues) - 1 == $keyl && count($teamsByLeague->teams) - 1 == $keyt && count($team->players) - 1 == $keyp)
									) {
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
			} else {
				Logs::register(json_encode($leagues));
			}
		} catch (\Throwable $th) {
			Logs::register(json_encode($th->getMessage()));
		}
    }
    
}
