<?php
/**
 * Class Felix_Arntz\WP_OOP_Plugin_Lib\Validation\Date_Validation_Rule
 *
 * @since n.e.x.t
 * @package wp-oop-plugin-lib
 */

namespace Felix_Arntz\WP_OOP_Plugin_Lib\Validation;

use Felix_Arntz\WP_OOP_Plugin_Lib\Validation\Contracts\Validation_Rule;
use Felix_Arntz\WP_OOP_Plugin_Lib\Validation\Exception\Validation_Exception;

/**
 * Class for a validation rule that ensures values are valid date strings.
 *
 * @since n.e.x.t
 */
class Date_Validation_Rule implements Validation_Rule {

	/**
	 * Validates the given value.
	 *
	 * Validation will be strict and throw an exception for any unmet requirements.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed $value Value to validate.
	 *
	 * @throws Validation_Exception Thrown when validation fails.
	 */
	public function validate( $value ): void {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $value ) ) {
			throw Validation_Exception::create(
				'invalid_datetime',
				sprintf(
					/* translators: %s: value */
					esc_html__( '%s is not a valid date string.', 'wp-oop-plugin-lib' ),
					esc_html( (string) $value )
				)
			);
		}
	}

	/**
	 * Sanitizes the given value.
	 *
	 * This should be called before storing the value in the persistency layer (e.g. the database).
	 * If the value does not satisfy validation requirements, it will be sanitized to a value that does, e.g. a default.
	 *
	 * @since n.e.x.t
	 *
	 * @param mixed $value Value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value ) {
		try {
			$this->validate( $value );
		} catch ( Validation_Exception $e ) {
			return '';
		}

		return $value;
	}
}
