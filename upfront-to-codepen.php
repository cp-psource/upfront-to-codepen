<?php
/**
 * Plugin Name:       Upfront zu CodePen
 * Plugin URI:        https://upfront.n3rds.work/upfront-framework/upfront-builder/upfront-zu-codepen-erweiterung/
 * Description:       Erstelle einen neuen Pen, der einen Stilleitfaden für die Farben und Typografieeinstellungen von Upfront-Designs enthält.
 * Version:           1.1.1
 * Author:            DerN3rd
 * Author URI:        https://n3rds.work
 * License:           GPLv2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       upfront-to-codepen
 */
require 'psource/psource-plugin-update/psource-plugin-updater.php';
use Psource\PluginUpdateChecker\v5\PucFactory;
$MyUpdateChecker = PucFactory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=upfront-to-codepen', 
	__FILE__, 
	'upfront-to-codepen' 
);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Upfront_To_CodePen {

	private $settings;

	function __construct() {
		$this->load_menu_page();
		$this->load_upfront_theme_settings();
		$this->load_settings_page_link();
		$this->load_styles_and_scripts();
	}

	function load_menu_page() {
		add_action( 'admin_menu', array( $this, 'register_menu_page' ), 900 );
	}

	function load_upfront_theme_settings() {
		$theme_settings = include get_stylesheet_directory() . '/settings.php';
		$this->settings = $this->strip_slashes( $theme_settings );
	}

	function load_settings_page_link() {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'add_action_links' ) );
	}

	function load_styles_and_scripts() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
	}

	function register_menu_page() {
		add_submenu_page(
			'upfront',
			'UpFront zu CodePen',
			'UpFront zu CodePen',
			'manage_options',
			'upfront_to_codepen',
			array( $this, 'submenu_page' )
		);
	}

	function submenu_page() {
		?>
		<div class="wrap card">
			<h1>UpFront Zu CodePen</h1>
			<form id="codepen_form" action="https://codepen.io/pen/define" method="POST" target="_blank">
				<p>Erstelle einen neuen Pen, der einen Styleguide für Dein <a href="https://upfront.n3rds.work/upfront-themes/" target="_blank">UpFront</a> Child-Theme enthält.</p>
				<p>Dadurch werden alle Themenfarben &amp; Typografische Einstellungen in einen neuen <a href="https://codepen.io/pen" target="_blank">Pen</a> extrahiert. Alle Google-Schriftartenvarianten werden im Abschnitt <strong>Stuff für &lt;head&gt;"</strong> in den Pen-Einstellungen hinzugefügt.</p>
				<p class="submit">
					<input type="hidden" id="codepen_form_data" name="data" value="">
					<input type="submit" id="codepen_submit" class="button button-primary" value="Neuen Pen erstellen">
				</p>
			</form>
		</div>
		<?php
	}

	function add_action_links( $links ) {
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=upfront_to_codepen' ) . '">Einstellungen</a>',
		);

		return array_merge( $links, $plugin_links );
	}

	function enqueue_styles_and_scripts() {
		$theme = wp_get_theme();

		wp_enqueue_script( 'uf2cp-script', plugins_url( '/js/main.js', __FILE__ ), array( 'jquery' ), '1.0', true );
		wp_localize_script( 'uf2cp-script', 'uf2cp_object',
			array(
				'theme_name'     => $theme->get( 'Name' ),
				'theme_version'  => $theme->get( 'Version' ),
				'theme_url'      => get_stylesheet_directory_uri(),
				'theme_settings' => $this->settings,
			)
		);
	}

	function strip_slashes( $input ) {
		if ( is_array( $input ) ) {
			$input = array_map( array( $this, 'strip_slashes' ), $input );
		} elseif ( is_object( $input ) ) {
			$vars = get_object_vars( $input );

			foreach ( $vars as $k => $v ) {
				$input->{$k} = $this->strip_slashes( $v );
			}
		} else {
			$input = stripslashes( $input );
		}

		return $input;
	}

}

$upfront_to_codepen = new Upfront_To_CodePen();
