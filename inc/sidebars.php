<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*
 * Add user created sidebar.
 * triggered by add sidebar button under the add a new sidebar section on the widgets area.
 */
add_action( 'wp_ajax_cshp_wm_add_widget_area', 'cshp_wm_add_widget_area' );
function cshp_wm_add_widget_area(){
	//Check Nonce or die.
	if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) ) die(-1);
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// get widget area name
	$widget_area_name = sanitize_text_field( $_POST['cshp_wm_widget_area_name'] );
	// create the slug part
	$slug = sanitize_title_with_dashes($widget_area_name, '', 'save');
	// setup sidebar in array.
	$cshp_sidebars['areas']['cshp-wm-widget-area-' . $slug] = $widget_area_name;
	//update the sidebar wp option.
	update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true );
	//echo message back on AJAX.
	printf( '<strong>%1$s</strong> widget area has been created. You can create more areas, once you finish update the page to see all the areas.',
		esc_html( $_POST['cshp_wm_widget_area_name'] )
	);
	// die.
	die();
}

/*
 * Remove user created sidebar.
 * triggered by each of the sidebars button.
 */
add_action( 'wp_ajax_cshp_wm_remove_widget_area', 'cshp_wm_remove_widget_area' );
function cshp_wm_remove_widget_area(){
	//Check Nonce or die.
	if ( ! wp_verify_nonce( $_POST['cshp_wm_sidebars_nonce'], 'cshp_wm_sidebars_nonce' ) ) die(-1);
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// unset the sidebar from the array
	unset( $cshp_sidebars['areas'][$_POST['cshp_wm_widget_area_name']] );
	// update the sidebars wp option
	update_option( 'cshp_wm_widget_areas', $cshp_sidebars, true );
	//die.
	die( $_POST['cshp_wm_widget_area_name'] );
}

/*
 * Register All User created sidebars
 */
add_action( 'widgets_init', 'cshp_wm_widgets_init' );
function cshp_wm_widgets_init() {
	// get sidebars wp option.
	$cshp_sidebars = get_option( 'cshp_wm_widget_areas' );
	// check that there are sidebars to register.
	if ( $cshp_sidebars['areas'] ) :
		//loop through sidebars.
		foreach ( $cshp_sidebars['areas'] as $id => $name ):
			// register each sidebar.
			register_sidebar( array(
				'name' => sanitize_text_field( $name ),
				'id' => sanitize_text_field( $id ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<h4 class="widget-title">',
				'after_title' => '</h4>',
			) );
		endforeach;
	endif;
}