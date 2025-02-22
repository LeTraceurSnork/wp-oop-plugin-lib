<?php
/**
 * Class Felix_Arntz\WP_OOP_Plugin_Lib\Meta\Meta_Repository
 *
 * @since 0.1.0
 * @package wp-oop-plugin-lib
 */

namespace Felix_Arntz\WP_OOP_Plugin_Lib\Meta;

use Felix_Arntz\WP_OOP_Plugin_Lib\Meta\Contracts\Entity_Key_Value_Repository;
use Felix_Arntz\WP_OOP_Plugin_Lib\Meta\Contracts\With_Single;

/**
 * Class for a repository of WordPress metadata.
 *
 * @since 0.1.0
 */
class Meta_Repository implements Entity_Key_Value_Repository, With_Single {

	/**
	 * Object type.
	 *
	 * @since 0.1.0
	 * @var string
	 */
	protected $object_type;

	/**
	 * Single config as $key => $single pairs.
	 *
	 * @since 0.1.0
	 * @var array<string, bool>
	 */
	private $single_config = array();

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $object_type Object type.
	 */
	public function __construct( string $object_type ) {
		$this->object_type = $object_type;
	}

	/**
	 * Checks whether a value for the given entity and meta key exists in the database.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $entity_id Entity ID.
	 * @param string $key       Meta key.
	 * @return bool True if a value for the meta key exists, false otherwise.
	 */
	public function exists( int $entity_id, string $key ): bool {
		return metadata_exists( $this->object_type, $entity_id, $key );
	}

	/**
	 * Gets the value for a given entity and meta key from the database.
	 *
	 * Always returns a single value.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $entity_id Entity ID.
	 * @param string $key       Meta key.
	 * @param mixed  $default   Optional. Value to return if no value exists for the meta key. Default null.
	 * @return mixed Value for the meta key, or the default if no value exists.
	 */
	public function get( int $entity_id, string $key, $default = null ) {
		if ( ! metadata_exists( $this->object_type, $entity_id, $key ) ) {
			// If not single, ensure the default is within an array.
			if ( ! $this->get_single( $key ) ) {
				if ( null !== $default ) {
					return array( $default );
				}
				return array();
			}
			return $default;
		}

		return get_metadata( $this->object_type, $entity_id, $key, $this->get_single( $key ) );
	}

	/**
	 * Updates the value for a given entity and meta key in the database.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $entity_id Entity ID.
	 * @param string $key       Meta key.
	 * @param mixed  $value     New value to set for the meta key.
	 * @return bool True on success, false on failure.
	 */
	public function update( int $entity_id, string $key, $value ): bool {
		/*
		 * If multiple values, delete the original ones first and then add the new ones individually, but only if the
		 * passed value is an indexed (not associative) array.
		 * There is only one caveat with this, but that is an edge-case: If the individual values of a multi-value meta
		 * key are themselves indexed arrays, this can lead to unexpected behavior with this implementation. A
		 * workaround would be to wrap them in another array before passing them to this method.
		 */
		if ( ! $this->get_single( $key ) && wp_is_numeric_array( $value ) ) {
			delete_metadata( $this->object_type, $entity_id, $key );
			foreach ( $value as $single_value ) {
				add_metadata( $this->object_type, $entity_id, $key, $single_value );
			}
			return true;
		}

		return (bool) update_metadata( $this->object_type, $entity_id, $key, $value );
	}

	/**
	 * Deletes the data for a given entity and meta key from the database.
	 *
	 * @since 0.1.0
	 *
	 * @param int    $entity_id Entity ID.
	 * @param string $key       Meta key.
	 * @return bool True on success, false on failure.
	 */
	public function delete( int $entity_id, string $key ): bool {
		return (bool) delete_metadata( $this->object_type, $entity_id, $key );
	}

	/**
	 * Deletes all data for the given entity from the database.
	 *
	 * @since 0.1.0
	 *
	 * @param int $entity_id Entity ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_all( int $entity_id ): bool {
		global $wpdb;

		$table_name = $this->object_type . 'meta';
		$table_col  = $this->object_type . '_id';

		$meta_ids = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT meta_id FROM {$wpdb->$table_name} WHERE $table_col = %d ",
				$entity_id
			)
		);
		foreach ( $meta_ids as $mid ) {
			delete_metadata_by_mid( $this->object_type, $mid );
		}

		return true;
	}

	/**
	 * Updates the metadata caches for the given entity IDs.
	 *
	 * @since 0.1.0
	 *
	 * @param int[] $entity_ids Entity IDs.
	 * @return bool True on success, or false on failure.
	 */
	public function prime_caches( array $entity_ids ): bool {
		return (bool) update_meta_cache( $this->object_type, $entity_ids );
	}

	/**
	 * Gets the 'single' config for a given key in the repository.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key Item key.
	 * @return bool Whether or not the item has a single value.
	 */
	public function get_single( string $key ): bool {
		// The default value is true.
		return $this->single_config[ $key ] ?? true;
	}

	/**
	 * Sets the 'single' config for a given key in the repository.
	 *
	 * @since 0.1.0
	 *
	 * @param string $key    Item key.
	 * @param bool   $single Item 'single' config.
	 */
	public function set_single( string $key, bool $single ): void {
		$this->single_config[ $key ] = $single;
	}
}
