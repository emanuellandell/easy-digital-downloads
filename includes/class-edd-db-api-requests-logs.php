<?php
/**
 * API Requests Logs DB class.
 *
 * This class is for interacting with the API request logs table.
 *
 * @package     EDD
 * @subpackage  Classes/Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_DB_API_Request_Logs Class.
 *
 * @since 3.0
 */
class EDD_DB_API_Request_Logs extends EDD_DB {

	/**
	 * The name of the cache group.
	 *
	 * @since  3.0
	 * @access public
	 * @var    string
	 */
	public $cache_group = 'api_request_logs';

	/**
	 * Initialise object variables and register table.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'edd_api_request_logs';
		$this->primary_key = 'id';
		$this->version     = '1.0';

		if ( ! $this->table_exists( $this->table_name ) ) {
			$this->create_table();
		}
	}

	/**
	 * Retrieve table columns and data types.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return array Array of table columns and data types.
	 */
	public function get_columns() {
		return array(
			'id'           => '%d',
			'user_id'      => '%d',
			'key'          => '%s',
			'version'      => '%s',
			'request'      => '%s',
			'ip'           => '%s',
			'time'         => '%f',
			'date_created' => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return array Array of the default values for each column in the table.
	 */
	public function get_column_defaults() {
		return array(
			'id'           => 0,
			'user_id'      => 0,
			'key'          => '',
			'version'      => '',
			'request'      => '',
			'ip'           => '',
			'time'         => '',
			'date_created' => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Insert a new API request log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::insert()
	 *
	 * @param array  $data {
	 *      API request log attributes.
	 * }
	 * @param string $type Data type to insert (forced to 'api_request_log'.
	 *
	 * @return int ID of the inserted log.
	 */
	public function insert( $data, $type = 'api_request_log' ) {
		// Forced to ensure the correct actions run.
		$type = 'api_request_log';

		$result = parent::insert( $data, $type );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Update an API request log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @see EDD_DB::update()
	 *
	 * @param int   $row_id API request log ID.
	 * @param array $data {
	 *      API request log attributes.
	 * }
	 * @param mixed string|array $where Where clause to filter update.
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		$result = parent::update( $row_id, $data, $where );

		if ( $result ) {
			$this->set_last_changed();
		}

		return $result;
	}

	/**
	 * Delete a log.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param int $row_id ID of the API request log to delete.
	 *
	 * @return bool True if deletion was successful, false otherwise.
	 */
	public function delete( $row_id = 0 ) {
		if ( empty( $row_id ) ) {
			return false;
		}
		$result = parent::delete( $row_id );
		if ( $result ) {
			$this->set_last_changed();
		}
		return $result;
	}

	/**
	 * Retrieve log from the database
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @param array $args {
	 *      Query arguments.
	 * }
	 *
	 * @return array $logs Array of `EDD_Log` objects.
	 */
	public function get_logs( $args = array() ) {
		global $wpdb;
	}

	/**
	 * Parse `WHERE` clause for the SQL query.
	 *
	 * @access private
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 * }
	 *
	 * @return string `WHERE` clause for the SQL query.
	 */
	private function parse_where( $args ) {
		$where = '';

		return $where;
	}

	/**
	 * Count the total number of API request logs in the database.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $args {
	 *      Arguments for the `WHERE` clause.
	 * }
	 *
	 * @return int $count Number of API request logs in the database.
	 */
	public function count( $args = array() ) {
		global $wpdb;
		$where = $this->parse_where( $args );
		$sql   = "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};";
		$count = $wpdb->get_var( $sql );
		return absint( $count );
	}

	/**
	 * Sets the last_changed cache key for API request logs.
	 *
	 * @since 3.0
	 * @access public
	 */
	public function set_last_changed() {
		wp_cache_set( 'last_changed', microtime(), $this->cache_group );
	}

	/**
	 * Retrieves the value of the last_changed cache key for API request logs.
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return string Value of the last_changed cache key for API request logs.
	 */
	public function get_last_changed() {
		if ( function_exists( 'wp_cache_get_last_changed' ) ) {
			return wp_cache_get_last_changed( $this->cache_group );
		}
		$last_changed = wp_cache_get( 'last_changed', $this->cache_group );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, $this->cache_group );
		}
		return $last_changed;
	}

	/**
	 * Create the table.
	 *
	 * @access public
	 * @since 3.0
	 */
	public function create_table() {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "
		CREATE TABLE {$this->table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			key VARCHAR(32) NOT NULL DEFAULT 'public',
			version VARCHAR(30) NOT NULL,
			request LONGTEXT NOT NULL,
			ip VARCHAR(100) NOT NULL,
			time VARCHAR(60) NOT NULL,
			date_created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			KEY user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;
		";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}