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
		add_action('init', array($this, 'llki_Sincro'));
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
			</h1>

			<?php
			if (empty($_GET['tab']) || $_GET['tab'] === 'league-lab') {
				$this->display_league_lab_configuration();
			} else if ($_GET['tab'] === 'klaviyo') {
				$this->display_klaviyo_configuration();
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
			<form method="post" action="options.php">
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
			'league_lab_active_leagues',              // Field ID
			'Active Leagues',                         // Field label
			array($this, 'display_league_lab_active_leagues_field'), // Function to display the field
			'league-lab',                             // Page slug where the field will be shown
			'league_lab_section',                      // ID of the section to which the field belongs
			array('description' => 'Enter each league name on a separate line.') // Description for the field
		);

		/* add_settings_field(
			'll_active_leagues',              // Field ID
			'Active Leagues',                         // Field label
			array($this, 'display_active_leagues'), // Function to display the field
			'league-lab',                             // Page slug where the field will be shown
			'league_lab_section',                      // ID of the section to which the field belongs
			//array('description' => 'Enter each league name on a separate line.') // Description for the field
		);
		register_setting('league_lab_section', 'll_active_leagues'); */

		register_setting('league_lab_section', 'league_lab_active_leagues');

		register_setting('league_lab_section', 'league_lab_site');

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
	public function display_active_leagues()
	{
		/* // Get all leagues (assuming they are stored in an array called $leagues)		
		$league_lab_api_key = get_option('league_lab_api_key');
		$site = get_option('league_lab_site');
		$leagues = Helper::get_LeagueLabLeagues($site, $league_lab_api_key);	

		$activeLeagues = get_option('ll_active_leagues'); // Example: Array of active league IDs

		echo '<div style="max-height: 500px; overflow-y: scroll;">'; // CSS styles for the scroll

		foreach ($leagues->leagues as $league) {
			$leagueId = $league->id;
			$leagueName = $league->name;

			echo '<label>';
			echo '<input type="checkbox" name="ll_active_leagues[]" value="' . esc_attr($leagueId) . '"';
			if (is_array($activeLeagues) && in_array($leagueId, $activeLeagues)) {
				echo ' checked';
			}
			echo '> ' . esc_html($leagueName);
			echo '</label><br>';
		}

		echo '</div>'; */
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

	public function display_league_lab_active_leagues_field($args)
	{
		$active_leagues = get_option('league_lab_active_leagues');
		$active_leagues = !empty($active_leagues) ? explode("\n", $active_leagues) : array();

		echo '<textarea name="league_lab_active_leagues" rows="5" cols="50">' . esc_textarea(implode("\n", $active_leagues)) . '</textarea>';

		if (isset($args['description'])) {
			echo '<p class="description">' . esc_html($args['description']) . '</p>';
		}
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

	public function llki_Sincro()
	{
		try {
			
			return;			
		} catch (\Throwable $th) {
			Logs::register(json_encode($th->getMessage()));
		}
	}
}
