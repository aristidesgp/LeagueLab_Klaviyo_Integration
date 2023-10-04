<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

use LLKI\Inc\Util\Helper;
use LLKI\Inc\Base\Logs;

class Settings
{
	public function register()
	{
		add_action('admin_menu', array($this, 'add_configuration_menus'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('init', array($this, 'llki_sync'));
	}

	// Add configuration menus
	public function add_configuration_menus()
	{
		add_submenu_page(
			'tools.php',                                 // Slug del menú padre
			'League Lab - Klaviyo Configuration',        // Título de la página
			'League Lab - Klaviyo Configuration',        // Título del menú
			'manage_options',                             // Capacidad requerida para acceder a la página
			'league-lab-klaviyo',                         // Slug de la página
			array($this, 'display_league_lab_klaviyo_configuration') // Función para mostrar el contenido de la página
		);

		add_submenu_page(
			'league-lab-klaviyo',                      // Parent menu slug
			'League Lab Configuration', // Page title
			'League Lab', // Menu title
			'manage_options',                  // Required capability to access the page
			'league-lab',              // Page slug
			array($this, 'display_league_lab_configuration') // Use $this to access the function within the class
		);

		add_submenu_page(
			'league-lab-klaviyo',                      // Parent menu slug
			'Klaviyo Configuration', // Page title
			'Klaviyo', // Menu title
			'manage_options',                  // Required capability to access the page
			'klaviyo',              // Page slug
			array($this, 'display_klaviyo_configuration') // Use $this to access the function within the class
		);
	}

	public function display_league_lab_klaviyo_configuration()
	{
?>
		<div class="wrap">
			<h1 class="nav-tab-wrapper">
				<a href="?page=league-lab-klaviyo" class="nav-tab <?php echo (empty($_GET['tab']) || $_GET['tab'] === 'league-lab') ? 'nav-tab-active' : ''; ?>">League Lab Configuration</a>
				<a href="?page=league-lab-klaviyo&tab=klaviyo" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'klaviyo') ? 'nav-tab-active' : ''; ?>">Klaviyo Configuration</a>
				<a href="?page=league-lab-klaviyo&tab=sincro" class="nav-tab <?php echo (isset($_GET['tab']) && $_GET['tab'] === 'sincro') ? 'nav-tab-active' : ''; ?>">Sync</a>
			</h1>

			<?php
			if (empty($_GET['tab']) || $_GET['tab'] === 'league-lab') {
				$this->display_league_lab_configuration();
			} else if ($_GET['tab'] === 'klaviyo') {
				$this->display_klaviyo_configuration();
			} else if ($_GET['tab'] === 'sincro') {
				$this->display_sync();
			}
			?>
		</div>
	<?php
	}


	// Display the League Lab configuration page content
	public function display_league_lab_configuration()
	{
	?>
		<div class="wrap">
			<h1>League Lab Configuration</h1>
			<form method="post" action="options.php" id="llki_ll_form">
				<?php
				settings_fields('league_lab_section');
				do_settings_sections('league-lab');

				submit_button();
				?>
			</form>
		</div>
	<?php
	}

	// Display the Klaviyo configuration page content
	public function display_klaviyo_configuration()
	{
	?>
		<div class="wrap">
			<h1>Klaviyo Configuration</h1>
			<form method="post" action="options.php">
				<?php
				settings_fields('klaviyo_section');
				do_settings_sections('klaviyo');

				submit_button();
				?>
			</form>
		</div>
<?php
	}

	// Display the Klaviyo configuration page content
	public function display_sync()
	{
		?>
			<div class="wrap">
				<h1>Synchronization</h1>
				<div id="sync-progress"></div>
				<div id="spinner" style="display: none;">
					<img id="sync-spinner" class="spinnerr" src="https://i.gifer.com/ZZ5H.gif" alt="Loading..."  />
				</div>
				<button id="manual-sync-button" class="button cbutton" onclick="syncro()">
					Manual Sync
				</button>
			</div>
		<?php
	}

	// Register settings for League Lab and Klaviyo
	public function register_settings()
	{
		// Register settings fields for League Lab
		add_settings_section(
			'league_lab_section',                  // Section ID
			'League Lab Data',                      // Section title
			null,                                  // Function to display a section description (optional)
			'league-lab'                            // Page slug where the section will be shown
		);

		add_settings_field(
			'league_lab_api_key',                   // Field ID
			'League Lab API Key',                   // Field label
			array($this, 'display_league_lab_api_key_field'), // Use $this to access the function within the class
			'league-lab',                            // Page slug where the field will be shown
			'league_lab_section'                     // ID of the section to which the field belongs
		);

		add_settings_field(
			'league_lab_site',                      // Field ID
			'Site',                                 // Field label
			array($this, 'display_league_lab_site_field'), // Function to display the field
			'league-lab',                           // Page slug where the field will be shown
			'league_lab_section'                    // ID of the section to which the field belongs
		);

		add_settings_field(
			'league_lab_sincro_number',                      // Field ID
			'Number to Sincro',                                 // Field label
			array($this, 'display_league_lab_sincro_number'), // Function to display the field
			'league-lab',                           // Page slug where the field will be shown
			'league_lab_section',                  // ID of the section to which the field belongs
			array('description' => 'Number of Leagues to sincro by hourly cron.')
		);


		add_settings_field(
			'league_lab_active_leagues',              // Field ID
			'Active Leagues',                         // Field label
			array($this, 'display_league_lab_active_leagues_field'), // Function to display the field
			'league-lab',                             // Page slug where the field will be shown
			'league_lab_section',                      // ID of the section to which the field belongs
			array('description' => 'Enter each league name on a separate line.') // Description for the field
		);

		/* add_settings_field(
			'll_active_leagues',              // Field ID
			'Leagues',                         // Field label
			array($this, 'display_active_leagues'), // Function to display the field
			'league-lab',                             // Page slug where the field will be shown
			'league_lab_section',                      // ID of the section to which the field belongs
			array('description' => 'Please select active leagues.') // Description for the field
		); */
		register_setting('league_lab_section', 'll_active_leagues');
		
		register_setting('league_lab_section', 'league_lab_active_leagues');
		
		register_setting('league_lab_section', 'active_leagues_type');

		register_setting('league_lab_section', 'league_lab_site');

		register_setting('league_lab_section', 'league_lab_sincro_number');

		register_setting('league_lab_section', 'league_lab_api_key');


		// Register settings fields for Klaviyo
		add_settings_section(
			'klaviyo_section',                  // Section ID
			'Klaviyo Data',                      // Section title
			null,                               // Function to display a section description (optional)
			'klaviyo'                            // Page slug where the section will be shown
		);

		add_settings_field(
			'klaviyo_api_key',                   // Field ID
			'Klaviyo API Key',                   // Field label
			array($this, 'display_klaviyo_api_key_field'), // Use $this to access the function within the class
			'klaviyo',                            // Page slug where the field will be shown
			'klaviyo_section'                     // ID of the section to which the field belongs
		);

		add_settings_field(
			'klaviyo_list_id',                   // Field ID
			'Klaviyo List ID',                   // Field label
			array($this, 'display_klaviyo_list_id_field'), // Use $this to access the function within the class
			'klaviyo',                            // Page slug where the field will be shown
			'klaviyo_section'                     // ID of the section to which the field belongs
		);

		add_settings_field(
			'klaviyo_add_with_consent',                    // ID del campo
			'Add customers with consent',          // Etiqueta del campo
			array($this, 'display_klaviyo_add_with_consent_field'), // Función para mostrar el campo
			'klaviyo',                                     // Slug de la página donde se mostrará el campo
			'klaviyo_section'                              // ID de la sección a la que pertenece el campo
		);

		register_setting('klaviyo_section', 'klaviyo_add_with_consent');


		register_setting('klaviyo_section', 'klaviyo_api_key');

		register_setting('klaviyo_section', 'klaviyo_list_id');
	}
	// Display the active leagues section
	public function display_active_leagues($args)
	{
		// Get all leagues (assuming they are stored in an array called $leagues)		
		$league_lab_api_key = get_option('league_lab_api_key');
		$site = get_option('league_lab_site');
		$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

		$activeLeagues = get_option('ll_active_leagues'); // Example: Array of active league IDs

		echo '<div style="max-height: 500px; overflow-y: scroll;">'; // CSS styles for the scroll

		foreach ($leagues->leagues as $league) {
			$leagueId = $league->id;
			$leagueStatus=$league->divisions[0]->team_status;
			$leagueName = $league->name;

			echo '<label>';
			echo '<input type="checkbox" name="ll_active_leagues[]" value="' . esc_attr($leagueId) . '"';
			if (is_array($activeLeagues) && in_array($leagueId, $activeLeagues)) {
				echo ' checked';
			}
			echo '> ' . esc_html($leagueName) . ' => ' . esc_html($leagueId). ' => ' . esc_html($leagueStatus);
			echo '</label><br>';
		}

		echo '</div>';
		if (isset($args['description'])) {
			echo '<p class="description" style="margin-top:15px;">' . esc_html($args['description']) . '</p>';
		}
	}

	// Función para guardar el array de ligas activas
	public function save_active_leagues($input)
	{
		// Sanitizar y validar el valor del campo de ligas activas
		$activeLeagues = isset($input['active_leagues']) ? $input['active_leagues'] : array();
		$activeLeagues = array_map('intval', $activeLeagues); // Convertir los valores a enteros

		// Actualizar el valor de la opción 'league_lab_active_leagues' en la base de datos
		update_option('league_lab_active_leagues', $activeLeagues);
	}

	// Display the League Lab API Key field
	public function display_league_lab_api_key_field()
	{
		$api_key = get_option('league_lab_api_key');
		echo '<input type="text" name="league_lab_api_key" value="' . esc_attr($api_key) . '" />';
	}

	public function display_league_lab_site_field()
	{
		$site = get_option('league_lab_site');
		echo '<input type="text" name="league_lab_site" value="' . esc_attr($site) . '" />';
	}

	public function display_league_lab_sincro_number($args)
	{
		$sincro_num = get_option('league_lab_sincro_number');
		$json_array = get_option('active_leagues_list');
		echo '<div><p style="margin-botton:5px;">'.esc_html($json_array).'</p>';
		echo '<input type="number" name="league_lab_sincro_number" value="' . esc_attr($sincro_num) . '" />';
		if (isset($args['description'])) {
			echo '<p class="description" style="margin-top:15px;">' . esc_html($args['description']) . '</p>';
		}
		echo '</div>';
	}
	

	public function display_league_lab_active_leagues_field($args)
	{
		$active_leagues = get_option('league_lab_active_leagues');
		$active_leagues = !empty($active_leagues) ? explode("\n", $active_leagues) : array();

		$active_leagues_type = get_option('active_leagues_type');

		echo '<label for="active_leagues_type">' . esc_html__('Active Leagues Type:', LLKI_PLUGIN_URL) . '</label><br>';
		echo '<select name="active_leagues_type" id="active_leagues_type" style="margin-top:10px;">';
		echo '<option value="1" ' . selected($active_leagues_type, '1', false) . '>' . esc_html__('Select from List', LLKI_PLUGIN_URL) . '</option>';
		echo '<option value="2" ' . selected($active_leagues_type, '2', false) . '>' . esc_html__('Enter Manually', LLKI_PLUGIN_URL) . '</option>';
		echo '</select>';

		echo '<br><br>';

		$divManualLeague=$active_leagues_type==='2'?'<div id="manual_active_leagues">':'<div id="manual_active_leagues" style="display: none;">';
		echo $divManualLeague;
		echo '<textarea name="league_lab_active_leagues" rows="5" cols="50">' . esc_textarea(implode("\n", $active_leagues)) . '</textarea>';
		echo '<p class="description">Enter each league name on a separate line.</p>';
		echo '</div>';
	
		// Display the select field using the $leagues array
		$league_lab_api_key = get_option('league_lab_api_key');
		$site = get_option('league_lab_site');
		$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

		$activeLeagues = get_option('ll_active_leagues'); // Example: Array of active league IDs

		$divListLeague=$active_leagues_type==='1'?'<div id="list_active_leagues">':'<div id="list_active_leagues" style="display: none;">';
		echo $divListLeague;
		echo '<div style="max-height: 500px; overflow-y: scroll;">'; // CSS styles for the scroll

		foreach ($leagues->leagues as $league) {
			$leagueId = $league->id;
			$leagueStatus='';
			foreach ($league->divisions as $key => $division) {
				$leagueStatus.=' /<strong> DIVISION:</strong> '.$division->name.' <strong>TEAM STATUS:</strong>'.$division->team_status;				
			}
			$leagueName = $league->name;

			echo '<label>';
			echo '<input type="checkbox" name="ll_active_leagues[]" value="' . esc_attr($leagueId) . '"';
			if (is_array($activeLeagues) && in_array($leagueId, $activeLeagues)) {
				echo ' checked';
			}
			echo '> ' . esc_html($leagueName) . ' => ' . esc_html($leagueId). ' => ' . $leagueStatus. ' => ' . $leagueStatus;
			echo '</label><br>';		

						
		}
		echo '</div>';
		echo '<p class="description" style="margin-top:15px;">Please select active leagues.</p>';
		echo '</div>';
		echo '<input type="hidden" id="active_l_t_h" name="active_leagues_type" value="' . esc_attr($active_leagues_type) . '">';
		
		
	}


	// Display the Klaviyo API Key field
	public function display_klaviyo_api_key_field()
	{
		$api_key = get_option('klaviyo_api_key');
		echo '<input type="text" name="klaviyo_api_key" value="' . esc_attr($api_key) . '" />';
	}

	public function display_klaviyo_list_id_field()
	{
		$list_id = get_option('klaviyo_list_id');
		echo '<input type="text" name="klaviyo_list_id" value="' . esc_attr($list_id) . '" />';
	}

	// Function to display the checkbox field
	public function display_klaviyo_add_with_consent_field()
	{
		$add_with_consent = get_option('klaviyo_add_with_consent');
		echo '<input type="checkbox" id="klaviyo_add_with_consent" name="klaviyo_add_with_consent" value="1" ' . checked($add_with_consent, 1, false) . ' />';
	}

	public function llki_sync() {

		$active_leagues_type = get_option('active_leagues_type');
        if($active_leagues_type==='2'){
            $this->llki_SincroByLeagueName();
        }else{
            $this->llki_SincroByLeagueId();
        }
        
    }

	public function llki_SincroByLeagueName()
	{
		try {
			return;
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


			//get all leagues
			$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);

			if (isset($leagues->leagues)) {

				$profilesToRegister = array();

				$profilesNumber = $add_with_consent == 1 ? 100 : 1000;

				foreach ($leagues->leagues as $keyl => $league) {

					foreach ($active_leagues as $key => $active) {

						if (stripos($league->name, $active) !== false) {

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
										var_dump(count($profilesToRegister).'<br>');
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
				if (count($profilesToRegister) > 0 && count($profilesToRegister) < $profilesNumber) {

					if ($add_with_consent == 1) {
						//$subL=Helper::subscribeProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
					} else {
						//$subl = Helper::addProfilesToKlaviyoList($klaviyo_api_key, $klaviyo_list_id, $profilesToRegister);
					}
					var_dump(count($profilesToRegister).'<br>');
				}
			} else {
				Logs::register(json_encode($leagues));
			}
		} catch (\Throwable $th) {
			Logs::register(json_encode($th->getMessage()));
		}
	}

	public function llki_SincroByLeagueId()
	{
		try {
			return;		
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
			$sum=0;
			foreach ($activeLeagues as $key => $active) {

				$league = Helper::get_LeagueLabLeaguesById($site, $league_lab_api_key, $active)->leagues[0];

				$teamsByLeague = Helper::get_LeagueLabTeamsByLeagues($site, $league_lab_api_key, $active);

				foreach ($teamsByLeague->teams as $keyt => $team) {

					var_dump(count($team->players).'<br>');
					$sum=$sum+count($team->players);
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
