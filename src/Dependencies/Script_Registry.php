<?php
/**
 * Class Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Script_Registry
 *
 * @since 0.1.0
 * @package wp-oop-plugin-lib
 */

namespace Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies;

use _WP_Dependency;
use Felix_Arntz\WP_OOP_Plugin_Lib\Dependencies\Contracts\With_Inline_Code;

/**
 * Class for a registry of scripts.
 *
 * @since 0.1.0
 */
class Script_Registry extends Abstract_Dependency_Registry implements With_Inline_Code {

	/**
	 * Registers a script with the given handle and arguments.
	 *
	 * @since 0.1.0
	 *
	 * @param string               $key  Script handle.
	 * @param array<string, mixed> $args {
	 *     Script registration arguments.
	 *
	 *     @type string|false      $src       Full URL of the script, or false if it is an alias or it is used purely
	 *                                        for inline scripts. Default false.
	 *     @type array             $deps      An array of registered script handles this script depends on. Default
	 *                                        empty array.
	 *     @type string|false|null $ver       String specifying script version number, if it has one, which is added
	 *                                        to the URL as a query string for cache busting purposes. If set to false,
	 *                                        the current WordPress version number is automatically added. If set to
	 *                                        null, no version is added. Default false.
	 *     @type string            $strategy  The strategy for how to load the script. If provided, may be either
	 *                                        'defer' or 'async'. Default empty string (none).
	 *     @type bool              $in_footer Whether to print the script in the footer. Default false.
	 *     @type string            $manifest  Full path of a PHP file which returns arguments for the script, such
	 *                                        as the '*.asset.php' files generated by the '@wordpress/scripts' package.
	 *                                        If provided, the returned arguments will be used to register the script.
	 *                                        Default empty string (none).
	 * }
	 * @return bool True on success, false on failure.
	 */
	public function register( string $key, array $args ): bool {
		global $wp_version;

		$args = $this->parse_args( $args );

		$last_param = $args['in_footer'];
		if ( $args['strategy'] && version_compare( $wp_version, '6.3', '>=' ) ) {
			$last_param = array(
				'in_footer' => $args['in_footer'],
				'strategy'  => $args['strategy'],
			);
		}

		return wp_register_script(
			$key,
			$args['src'],
			$args['deps'],
			$args['ver'],
			$last_param
		);
	}

	/**
	 * Checks whether a script with the given handle is registered.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Script handle.
	 * @return bool True if the script is registered, false otherwise.
	 */
	public function is_registered( string $key ): bool {
		return wp_script_is( $key, 'registered' );
	}

	/**
	 * Gets the registered script for the given handle from the registry.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Script handle.
	 * @return _WP_Dependency|null The registered script definition, or `null` if not registered.
	 */
	public function get_registered( string $key ) {
		$wp_scripts = wp_scripts();

		return $wp_scripts->registered[ $key ] ?? null;
	}

	/**
	 * Gets all scripts from the registry.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, _WP_Dependency> Associative array of handles and their script definitions, or empty array
	 *                                       if nothing is registered.
	 */
	public function get_all_registered(): array {
		return wp_scripts()->registered;
	}

	/**
	 * Enqueues the script with the given handle.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Script handle.
	 */
	public function enqueue( string $key ): void {
		wp_enqueue_script( $key );
	}

	/**
	 * Dequeues the script with the given handle.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Script handle.
	 */
	public function dequeue( string $key ): void {
		wp_dequeue_script( $key );
	}

	/**
	 * Checks whether the script with the given handle is enqueued.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Script handle.
	 * @return bool True if the script is enqueued, false otherwise.
	 */
	public function is_enqueued( string $key ): bool {
		return wp_script_is( $key, 'enqueued' );
	}

	/**
	 * Adds inline code to the script with the given handle.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key  Script handle.
	 * @param string $code JavaScript code to inline after the script output.
	 * @return bool True on success, false on failure.
	 */
	public function add_inline_code( string $key, string $code ): bool {
		return wp_add_inline_script( $key, $code, 'after' );
	}

	/**
	 * Returns defaults to parse script arguments with.
	 *
	 * The keys 'src' and 'deps' do not need to be included as they are universal defaults for any dependency type.
	 *
	 * @since 0.1.0
	 *
	 * @return array<string, mixed> Script registration defaults.
	 */
	protected function get_additional_args_defaults(): array {
		return array(
			'ver'       => false,
			'strategy'  => '',
			'in_footer' => false,
		);
	}
}
