<?php
/**
 * Class Felix_Arntz\WP_OOP_Plugin_Lib\Privacy\Personal_Data_Exporter_Hook_Registrar
 *
 * @since 0.1.0
 * @package wp-oop-plugin-lib
 */

namespace Felix_Arntz\WP_OOP_Plugin_Lib\Privacy;

use Felix_Arntz\WP_OOP_Plugin_Lib\General\Array_Registry;
use Felix_Arntz\WP_OOP_Plugin_Lib\General\Contracts\Hook_Registrar;

/**
 * Class that adds the relevant hook to register personal data exporters.
 *
 * @since 0.1.0
 */
class Personal_Data_Exporter_Hook_Registrar implements Hook_Registrar {

	/**
	 * Adds a callback that registers the exporters to the relevant hook.
	 *
	 * The callback receives a registry instance as the sole parameter, allowing to call the
	 * {@see Array_Registry::register()} method.
	 *
	 * @since 0.1.0
	 *
	 * @param callable $register_callback Callback to register the exporters.
	 */
	public function add_register_callback( callable $register_callback ): void {
		add_filter(
			'wp_privacy_personal_data_exporters',
			function ( $exporters ) use ( $register_callback ) {
				$exporter_registry = new Array_Registry( $exporters );
				$register_callback( $exporter_registry );
				return $exporter_registry->to_array();
			}
		);
	}
}
