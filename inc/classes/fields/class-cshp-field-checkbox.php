<?php
/**
 * CSHP_Field_Checkbox class file.
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

/**
 * CSHP_Field_Checkbox Class.
 */
class CSHP_Field_Checkbox extends CSHP_Field {
	/**
	 * Outputting our field.
	 */
	public function output() {
		// set the value.
		$value = ( ! empty( get_option( $this->args->id ) ) ) ? get_option( $this->args->id ) : $this->args->default_value;
		// echo the checkbox field.
		echo '<input name="' . esc_attr( $this->args->id ) . '" id="' . esc_attr( $this->args->id ) . '" type="checkbox" ' . checked( 'on', $value, false ) . ' class="" />';
		// echo the description.
		// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo '<p>' . $this->args->description . '</p>';
	}
}
