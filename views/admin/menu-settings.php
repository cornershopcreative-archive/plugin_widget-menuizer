<?php
/**
 * Menu Settings Page file.
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

// The menuizer settings page.
/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
$menuizer_settings = new CSHP_Settings_Page( __( 'Widget Menuizer', CSHP_WM_TEXTDOMAIN ), __( 'Widget Menuizer', CSHP_WM_TEXTDOMAIN ), 'manage_options', 'widget_menuizer' );

// Adding the dropdown sections.
/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
$menuizer_settings_dropdown_settings = $menuizer_settings->add_settings_section( 'dropdown_settings', __( 'Dropdown', CSHP_WM_TEXTDOMAIN ) );
	// Adding the dropdown settings hover field.
	$menuizer_settings_dropdown_settings->add_settings_field(
		array(
			'id'    => 'show_on_hover',
			/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
			'label' => __( 'Show On Hover?', CSHP_WM_TEXTDOMAIN ),
			/* phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralDomain */
			'description' => __( 'Select whether you want to show the dropdowns on hover.', CSHP_WM_TEXTDOMAIN ),
			'field_type' => 'checkbox',
		)
	);
