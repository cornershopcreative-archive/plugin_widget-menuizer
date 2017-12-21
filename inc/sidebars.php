<?php
/**
 * Sidebars functions, hooks and filters
 *
 * @package Widgetmenuizer
 * @author  Cornershop Creative <info@cornershopcreative.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link    http://www.cornershopcreative.com/
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Add user created sidebar.
 * triggered by add sidebar button under the add a new sidebar section on the widgets area.
 */
function cshp_wm_add_widget_area() {
	// Check Nonce or die.
	if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) && ! isset( $_POST['cshp_wm_widget_area_name'] ) ) {
		die( -1 );
	}
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// get widget area name
	$widget_area_name = sanitize_text_field( $_POST['cshp_wm_widget_area_name'] );
	// create the slug part
	$slug = sanitize_title_with_dashes( $widget_area_name, '', 'save' );
	// Check If Sidebar already exists.
	if ( isset( $cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ] ) ) {
		echo wp_json_encode( array(
			'status'  => 'error',
			'message' => sprintf( '<strong>%1$s</strong> widget area already exists. Please use a different name.',
				esc_html( $widget_area_name )
			),
		));
		die( -1 );
	}
	// get widget area description
	$widget_area_desc = isset( $_POST['cshp_wm_widget_area_desc'] ) ? sanitize_text_field( $_POST['cshp_wm_widget_area_desc'] ) : '';
	// setup sidebar in array.
	$cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ]['name'] = $widget_area_name;
	$cshp_sidebars['areas'][ 'cshp-wm-widget-area-' . $slug ]['description'] = $widget_area_desc;
	// update the sidebar wp option.
	update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true );
	// echo message back on AJAX.
	echo wp_json_encode( array(
		'status'  => 'success',
		'message' => sprintf( '<strong>%1$s</strong> widget area has been created. You can create more areas, once you finish update the page to see all the areas.',
			esc_html( $widget_area_name )
		),
	));
	// die.
	die();
}
add_action( 'wp_ajax_cshp_wm_add_widget_area', 'cshp_wm_add_widget_area' );

/**
 * Remove user created sidebar.
 * triggered by each of the sidebars button.
 */
function cshp_wm_remove_widget_area() {
	// Check Nonce or die.
	if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) && ! isset( $_POST['cshp_wm_widget_area_name'] ) ) {
		die( -1 );
	}
	// save the widget area id.
	$widget_area_name = $_POST['cshp_wm_widget_area_name'];
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// unset the sidebar from the array
	unset( $cshp_sidebars['areas'][ $widget_area_name ] );
	// update the sidebars wp option
	if ( update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true ) ) {
		echo wp_json_encode( array(
			'status'  => 'success',
			'sidebarId' => $widget_area_name,
		));
		die();
	}
	// die.
	die();
}
add_action( 'wp_ajax_cshp_wm_remove_widget_area', 'cshp_wm_remove_widget_area' );

/**
 * Register All User created sidebars
 */
function cshp_wm_widgets_init() {
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// check that there are sidebars to register.
	if ( $cshp_sidebars['areas'] ) :
		// loop through sidebars.
		foreach ( $cshp_sidebars['areas'] as $id => $sidebar ) :
			// register each sidebar.
			register_sidebar( array(
				'name'          => stripslashes( sanitize_text_field( $sidebar['name'] ) ),
				'id'            => sanitize_text_field( $id ),
				'description'   => stripslashes( sanitize_text_field( $sidebar['description'] ) ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			) );
		endforeach;
	endif;
}
add_action( 'widgets_init', 'cshp_wm_widgets_init' );
