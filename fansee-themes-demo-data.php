<?php
/*
Plugin Name: Fansee Themes Demo Data
Plugin URI: https://wordpress.org/plugins/fansee-themes-demo-data/
Description: Get demo data to import your content, widgets and theme settings with one click.
Version: 1.0
Author: fanseethemes
Author URI: http://www.fanseethemes.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: ftdi
*/

// Block direct access to the main plugin file.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class FTDI_Plugin{

	public function __construct(){
		add_filter( 'pt-ocdi/import_files', array( $this, 'import_files' ) );
		add_action( 'pt-ocdi/after_import', array( $this, 'after_import_setup' ) );
	}

	public function import_files(){
		$contents = array();
		$theme = wp_get_theme();
		$site_url = "https://fanseethemes.com/demo-contents/{$theme->stylesheet}/";

		$demo_json = $site_url . 'demo.json';
		$handle = @fopen($demo_json, 'r');
		if(!$handle){
		    return $contents;
		}

		$demos = file_get_contents( $demo_json );

		if( !$demos )
			return $contents;

		$demos = json_decode( $demos, true ); 
		if( $demos && isset( $demos[ 'demos' ] ) && is_array( $demos[ 'demos' ] ) ){
			foreach( $demos[ 'demos' ] as $demo ){

				$path = $site_url;
				$path .= strtolower( str_replace( ' ', '-', $demo[ "name" ] ) ) . '/';
				$contents[] = array(
					'import_file_name' => $demo[ "name" ],
					'categories'       => $demo[ 'categories' ],
					'import_file_url'            => $path . 'wordpress.xml',
					'import_widget_file_url'     => $path . 'widget.wie',
					'import_customizer_file_url' => $path . 'customizer.dat',
					'import_preview_image_url'   => $path . 'screenshot.png',
					'import_notice'              => $demo[ 'notice' ],
				);
			}
		}

		return $contents;
	}

	function after_import_setup() {

	    // Assign menus to their locations.
	    $locations = array( 'primary' => 'primary', 'social-menu-footer' => 'social' );
	    $new = get_theme_mod( 'nav_menu_locations' );
	    foreach( $locations as $loc => $name ){

		    $menu = get_term_by( 'name', $name, 'nav_menu' );
		    if( $menu ){
		    	$new[ $loc ] = $menu->term_id;
		    }
	    }

	    set_theme_mod( 'nav_menu_locations', $new );

	    // Assign front page and posts page (blog page).
	    $front_page_id = get_page_by_title( 'Front Page' );
	    $blog_page_id  = get_page_by_title( 'Blog' );

	    update_option( 'show_on_front', 'page' );
	    if( $front_page_id ){
	    	update_option( 'page_on_front', $front_page_id->ID );
	    }

	    if( $blog_page_id ){
	    	update_option( 'page_for_posts', $blog_page_id->ID );
	    }

	}
	
}

new FTDI_Plugin();

/**
* Disable branding of One Click Demo Import
* @link https://wordpress.org/plugins/one-click-demo-import/
* @since FTDI 1.0
* @return Bool
*/
function ftdi_ocdi_branding(){
	return true;
}
add_filter( 'pt-ocdi/disable_pt_branding', 'ftdi_ocdi_branding' );
