<?php

namespace Hametuha\Sharee\Models;

use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Pattern\Model;

/**
 * Revenue meta
 *
 * @package sharee
 * @property RevenueModel $revenues
 */
class RevenueMetaModel extends Model {

	protected $version = '0.8.0';

	protected $name = 'revenue_meta';

	protected $priority = 11;

	protected $default_placeholder = [
		'meta_id'    => '%d',
		'key'        => '%s',
		'revenue_id' => '%d',
		'value'      => '%s',
		'created'    => '%s',
		'updated'    => '%s',
	];

	protected $models = [
		'revenues' => RevenueModel::class,
	];


	/**
	 * Get table query
	 *
	 * @param string $prev_version
	 * @return string
	 */
	public function get_tables_schema( $prev_version ) {
		return <<<SQL
			CREATE TABLE `{$this->table}` (
				`meta_id`      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`key`          VARCHAR(192) NOT NULL,
				`revenue_id`   BIGINT UNSIGNED NOT NULL,
				`value`        LONGTEXT NOT NULL,
				`created`      DATETIME NOT NULL,
				`updated`      DATETIME NOT NULL,
				FOREIGN KEY( `revenue_id` ) references `{$this->revenues->table}`(`revenue_id`) ON DELETE CASCADE,
				INDEX key_date( `key`, `revenue_id` )
			) ENGINE {$this->engine} DEFAULT CHARSET={$this->charset}
SQL;
	}

	/**
	 * Add meta value.
	 *
	 * @param string $key
	 * @param int    $revenue_id
	 * @param $value
	 * @return int|\WP_Error
	 */
	public function add_meta( $key, $revenue_id, $value ) {
		if ( is_array( $value ) ) {
			$value = serialize( $value );
		}
		return $this->insert(
			[
				'key'        => $key,
				'revenue_id' => $revenue_id,
				'value'      => $value,
			]
		);
	}

	/**
	 * Register revenue meta.
	 *
	 * @param int   $revenue_id
	 * @param array $records
	 * @return false|int
	 */
	public function add_multiple_meta( $revenue_id, $records ) {
		return $this->bulk_insert(
			array_map(
				function( $record ) use ( $revenue_id ) {
					list( $key, $value ) = $record;
					if ( is_array( $value ) ) {
						$value = serialize( $value );
					}
					return [
						'revenue_id' => $revenue_id,
						'key'        => $key,
						'value'      => $value,
					];
				},
				$records
			)
		);
	}

	/**
	 * Get meta values
	 *
	 * @param int    $revenue_id
	 * @param string $key
	 * @param bool   $multiple Default false
	 * @return array|\stdClass
	 */
	public function get_meta( $revenue_id, $key, $multiple = false ) {
		$query = <<<SQL
			SELECT * FROM {$this->table}
			WHERE `key` = %s
              AND revenue_id = %d
			ORDER BY created DESC
SQL;
		if ( $multiple ) {
			return $this->get_results( $query, $key, $revenue_id );
		} else {
			return $this->get_row( $query, $key, $revenue_id );
		}
	}

	/**
	 * Get all logs
	 *
	 * @param int $revene_id
	 * @return array
	 */
	public function get_logs( $revene_id ) {
		return $this->get_meta( $revene_id, 'log', true );
	}

}
