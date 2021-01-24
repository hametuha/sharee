<?php
namespace Hametuha\Sharee\Models;


use Hametuha\Pattern\Model;
use Hametuha\Sharee\Models\RevenueMetaModel;

/**
 * Class RevenueModel
 *
 * @package sharee
 * @property array            $label
 * @property array            $status
 * @property array            $status_class
 * @property RevenueMetaModel $revenue_meta
 */
class RevenueModel extends Model {

	protected $version = '0.8.1';

	protected $name = 'revenues';

	protected $default_placeholder = [
		'revenue_id'   => '%d',
		'revenue_type' => '%s',
		'object_id'    => '%d',
		'price'        => '%f',
		'unit'         => '%f',
		'tax'          => '%f',
		'deducting'    => '%f',
		'total'        => '%f',
		'status'       => '%s',
		'description'  => '%s',
		'created'      => '%s',
		'fixed'        => '%s',
		'updated'      => '%s',
		'currency'     => '%s',
	];

	protected $models = [
		'revenue_meta' => RevenueMetaModel::class,
	];

	/**
	 * Default search args for search method.
	 *
	 * @return array
	 */
	public function default_search_args() {
		return [
			'year'      => 0,
			'month'     => 0,
			'page'      => 1,
			'object_id' => 0,
			'per_page'  => 20,
			'status'    => null,
			'start'     => '',
			'end'       => '',
			'type'      => [],
		];
	}

	/**
	 * Get table query
	 *
	 * @param string $prev_version
	 * @return string
	 */
	public function get_tables_schema( $prev_version ) {
		return <<<SQL
			CREATE TABLE `{$this->table}` (
				`revenue_id`   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				`revenue_type` VARCHAR(48) NOT NULL,
				`object_id`    BIGINT UNSIGNED NOT NULL DEFAULT 0,
				`price`        FLOAT SIGNED NOT NULL,
				`unit`         INT UNSIGNED NOT NULL,
				`tax`          FLOAT SIGNED NOT NULL,
				`deducting`    FLOAT SIGNED NOT NULL,
				`total`	       FLOAT SIGNED NOT NULL,
				`status`       TINYINT SIGNED NOT NULL DEFAULT 0,
				`description`  TEXT NOT NULL DEFAULT '',
				`created`      DATETIME NOT NULL,
				`fixed`        DATETIME NOT NULL,
				`updated`      DATETIME NOT NULL,
				`currency`     VARCHAR(45) NOT NULL DEFAULT 'JPY',
				INDEX  type_user( `revenue_type`, `object_id`, `created` ),
				INDEX  by_date( `created`, `status` )
			) ENGINE {$this->engine} DEFAULT CHARSET={$this->charset}
SQL;
	}

	/**
	 * Revenue type which included in post.
	 */
	public function get_types_for_post_type() {
		return apply_filters( 'sharee_revenu_type_for_post', [] );
	}

	/**
	 * Detect if this is post type.
	 *
	 * @param string $type
	 * @return bool
	 */
	public function is_post_revenue( $type ) {
		return in_array( $type, $this->get_types_for_post_type(), true );
	}

	/**
	 * Get label for types
	 *
	 * @return array
	 */
	public function get_labels() {
		return apply_filters( 'sharee_labels', [] );
	}

	/**
	 * Get revenue type to be billed.
	 *
	 * @return array
	 */
	public function type_to_be_billed() {
		$types = array_keys( $this->get_labels() );
		return (array) apply_filters( 'sharee_type_to_be_billed', $types );
	}

	/**
	 * Get labels
	 *
	 * @return array
	 */
	public function get_status() {
		return array_merge(
			[
				0  => _x( 'Pending', 'revenue-status', 'sharee' ),
				1  => _x( 'Paid', 'revenue-status', 'sharee' ),
				-1 => _x( 'Rejected', 'revenue-status', 'sharee' ),
			],
			apply_filters( 'sharee_additional_status', [] )
		);
	}

	/**
	 * Get status label
	 *
	 * @param int $status_index
	 * @return string
	 */
	public function label( $status_index ) {
		$status_key = $this->get_status();
		return isset( $status_key[ $status_index ] ) ? $status_key[ $status_index ] : $status_key[0];
	}

	/**
	 * Get status class
	 *
	 * @return array
	 */
	public function get_status_class() {
		return apply_filters(
			'sharee_status_class',
			[
				0  => 'warning',
				1  => 'success',
				-1 => 'default',
			]
		);
	}

	/**
	 * Get status label
	 *
	 * @param string $status
	 * @param bool   $with_icon
	 *
	 * @return string
	 */
	public function status_label( $status, $with_icon = false ) {
		$classes = $this->get_status_class();
		if ( ! isset( $classes[ $status ] ) ) {
			$status = 0;
		}
		$icon = '';
		if ( $with_icon ) {
			switch ( $status ) {
				case 1:
					$icon = '<span class="dashicons dashicons-yes"></span>';
					break;
				case -1:
					$icon = '<span class="dashicons dashicons-no-alt"></span>';
					break;
				default:
					$icon = '<span class="dashicons dashicons-warning"></span>';
					break;
			}
		}
		return sprintf( '<span class="label label-%s">%s %s</span>', esc_attr( $classes[ $status ] ), $icon, esc_html( $this->status[ $status ] ) );
	}

	/**
	 * Get label
	 *
	 * @param string $type
	 *
	 * @return mixed|string
	 */
	public function type_label( $type ) {
		$labels = $this->get_labels();
		return isset( $labels[ $type ] ) ? $labels[ $type ] : __( 'Undefined', 'sharee' );
	}

	/**
	 * Get month range
	 *
	 * @param int $year
	 * @param int $month
	 * @return array
	 */
	public function get_month_range( $year, $month ) {
		$start = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
		$d     = new \DateTime();
		$d->setTimezone( new \DateTimeZone( 'UTC' ) );
		$d->setDate( $year, $month, 1 );
		$end = $d->format( 'Y-m-t 23:59:59' );
		return [ $start, $end ];
	}

	/**
	 * Get revenue record
	 *
	 * @param $revenue_id
	 * @return \stdClass
	 */
	public function get( $revenue_id ) {
		$query = <<<SQL
			SELECT * FROM {$this->table}
			WHERE revenue_id = %d
SQL;
		return $this->get_row( $query, $revenue_id );
	}

	/**
	 * Update value
	 *
	 * @param int $revenue_id
	 * @param int $status
	 *
	 * @return bool
	 */
	public function update_status( $revenue_id, $status ) {
		$old_revenue = $this->get( $revenue_id );
		if ( ! $old_revenue ) {
			return false;
		}
		$old_status = (int) $old_revenue->status;
		$old_label  = $this->label( $old_status );
		$new_label  = $this->label( $status );
		if ( $old_status === (int) $status ) {
			// Nothing changes.
			return false;
		}
		$updated = $this->update(
			[
				'status' => $status,
			],
			[
				'revenue_id' => $revenue_id,
			]
		);
		if ( $updated ) {
			// Save log.
			// translators: %1$s is old status, %2$s is new status.
			$message = sprintf( __( 'Status changed %1$s to %2$s.', 'sharee' ), $old_label, $new_label );
			$this->revenue_meta->add_meta( 'log', $revenue_id, $message );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Add new revenue data.
	 *
	 * @param string $type
	 * @param int    $object_id
	 * @param float  $price
	 * @param array  $args Optional args.
	 *   - unit        int    Default 1
	 *   - tax         float  Default 0
	 *   - deducting   float  Default 0
	 *   - total       float  Default, price value.
	 *   - description string Default empty.
	 *
	 * @return \WP_Error|int
	 */
	public function add_revenue( $type, $object_id, $price, $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'revenue_type' => $type,
				'object_id'    => $object_id,
				'price'        => $price,
				'total'        => $price,
				'unit'         => 1,
				'tax'          => 0,
				'deducting'    => 0,
				'description'  => '',
				'status'       => 0,
				'created'      => current_time( 'mysql', $this->use_gmt() ),
			]
		);
		// Save revenue.
		return $this->insert( $args );
	}

	/**
	 * Search
	 *
	 * @param array $args
	 * @param bool  $total Default false. If true, calculate total.
	 * @return array|\stdClass
	 */
	public function search( $args, $total = false ) {
		$args = wp_parse_args( $args, $this->default_search_args() );
		// Build where flags.
		$wheres = [];
		$type   = array_filter( (array) $args['type'] );
		if ( 1 === count( $type ) ) {
			$wheres[] = $this->db->prepare( '( type = %s )', $type[0] );
		} elseif ( $type ) {
			$wheres[] = sprintf(
				'( `type` IN ( %s ) )',
				implode(
					', ',
					array_map(
						function( $t ) {
							return $this->db->prepare( '%s', $t );
						},
						$type
					)
				)
			);
		}
		if ( $args['object_id'] ) {
			if ( is_array( $args['object_id'] ) ) {
				$wheres[] = sprintf( '( object_id IN ( %s ) )', implode( ',', array_map( 'intval', $args['object_id'] ) ) );
			} else {
				$wheres[] = $this->db->prepare( '( object_id = %d )', $args['object_id'] );
			}
		}
		if ( is_numeric( $args['status'] ) ) {
			$wheres[] = $this->db->prepare( '( `status` = %d )', $args['status'] );
		}
		$year  = (int) $args['year'];
		$month = (int) $args['month'];
		if ( $year && $month ) {
			$wheres[] = sprintf( '( EXTRACT(YEAR_MONTH from `created`) = %04d%02d )', $year, $month );
		} elseif ( $year ) {
			$wheres[] = sprintf( '( EXTRACT(YEAR from `created`) = %04d )', $year );
		} elseif ( $month ) {
			$wheres[] = sprintf( '( EXTRACT(MONTH from `created`) = %02d )', $month );
		}
		if ( $args['start'] && $args['end'] ) {
			$wheres[] = $this->db->prepare( '( `created` BETWEEN %s AND %s )', $args['start'], $args['end'] );
		} elseif ( $args['start'] ) {
			$wheres[] = $this->db->prepare( '( `created` >= %s )', $args['start'] );
		} elseif ( $args['end'] ) {
			$wheres[] = $this->db->prepare( '( `created` <= %s )', $args['end'] );
		}

		$where = $wheres ? 'WHERE ' . implode( ' AND ', $wheres ) : '';
		if ( ! $total ) {
			$query = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table}
			{$where}
			ORDER BY created DESC
SQL;
			if ( $args['per_page'] ) {
				$query .= sprintf( ' LIMIT %d, %d', $args['per_page'] * ( max( 1, $args['page'] ) - 1 ), $args['per_page'] );
			}
			return $this->get_results( $query );
		} else {
			$query = <<<SQL
			SELECT
				AVG(price) AS price,
				AVG(tax) AS tax,
				AVG(unit) AS unit,
				AVG(deducting) AS deducting,
				AVG(total) AS total,
				SUM(total) AS total_sum,
			  	SUM(unit)  AS unit_sum
			FROM {$this->table}
			{$where}
SQL;
			return $this->get_row( $query );

		}
	}

	/**
	 * Get billing list to be paid.
	 *
	 * @param int       $year
	 * @param int       $month
	 * @param int|array $user_id
	 * @param int|null  $status  If null set, no status. Default 0(pending)
	 *
	 * @return array
	 */
	public function get_billing_list( $year, $month, $user_id = 0, $status = 0 ) {
		$wheres = $this->get_billing_where( $year, $month, $user_id, $status );
		$query  = <<<SQL
			SELECT SUM(total) AS total, object_id, SUM(deducting) AS deducting
			FROM {$this->table}
			{$wheres}
			GROUP BY object_id
			ORDER BY total DESC
SQL;
		return $this->get_results( $query );
	}

	/**
	 * Get billing summary
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $user_id
	 * @param int $status
	 * @return \stdClass
	 */
	public function get_billing_summary( $year, $month, $user_id = 0, $status = 0 ) {
		$wheres = $this->get_billing_where( $year, $month, $user_id, $status );
		$query  = <<<SQL
		SELECT COUNT(revenue_id) AS record_number, SUM(total) AS total, SUM(deducting) AS deducting
			FROM {$this->table}
			{$wheres}
SQL;
		return $this->get_row( $query );
	}

	/**
	 * Get where clause for billing list
	 *
	 * @param int       $year
	 * @param int       $month
	 * @param int|array $user_id
	 * @param int       $status
	 * @param array     $types
	 * @return string
	 */
	protected function get_billing_where( $year, $month, $user_id = 0, $status = 0, $types = [] ) {
		$wheres = [];
		if ( $year && $month ) {
			list( $start, $end ) = $this->get_month_range( $year, $month );
			$wheres[]            = $this->db->prepare( '( created BETWEEN %s AND %s )', $start, $end );
		} elseif ( $year ) {
			$wheres[] = $this->db->prepare( '( EXTRACT(YEAR FROM created) = %d )', $year );
		} elseif ( $month ) {
			$wheres[] = $this->db->prepare( '( EXTRACT(MONTH FROM created) = %d )', $month );
		}
		if ( is_numeric( $status ) ) {
			$wheres[] = $this->db->prepare( '( status = %d )', $status );
		}
		if ( $types ) {
			$wheres[] = sprintf(
				'( revenue_type IN ( %s ) )',
				implode(
					', ',
					array_map(
						function( $type ) {
							return $this->db->prepare( '%s', $type );
						},
						$types
					)
				)
			);
		}
		if ( $user_id ) {
			if ( is_array( $user_id ) ) {
				$user_ids = array_filter( array_map( 'intval', $user_id ) );
				if ( $user_ids ) {
					$wheres[] = sprintf( '( object_id IN ( %s ) )', implode( ', ', $user_ids ) );
				}
			} else {
				$wheres[] = $this->db->prepare( '( object_id = %d )', $user_id );
			}
		}
		if ( $wheres ) {
			$wheres = 'WHERE ' . implode( ' AND ', $wheres );
		} else {
			$wheres = '';
		}
		return $wheres;
	}

	/**
	 * Update record to be billed.
	 *
	 * @param array $object_ids
	 * @param int   $year
	 * @param int   $month
	 * @param array $type
	 * @return int
	 */
	public function fix_billing( $object_ids, $type = [], $year = 0, $month = 0 ) {
		$object_ids = array_filter( array_map( 'intval', (array) $object_ids ) );
		if ( ! $object_ids ) {
			return 0;
		}
		$wheres          = $this->get_billing_where( $year, $month, $object_ids, 0, $type );
		$query           = <<<SQL
			SELECT revenue_id, object_id FROM {$this->table}
			{$wheres}
SQL;
		$current_records = $this->get_results( $query );
		if ( ! $current_records ) {
			return 0;
		}
		$revenue_ids = [];
		$user_ids    = [];
		foreach ( $current_records as $row ) {
			$revenue_ids[] = $row->revenue_id;
			if ( ! in_array( $row->object_id, $user_ids, true ) ) {
				$user_ids[] = $row->object_id;
			}
		}
		$update_query = <<<SQL
			UPDATE {$this->table}
			SET status=1, fixed=%s, updated=%s
			{$wheres}
SQL;
		$now          = current_time( 'mysql', $this->use_gmt() );
		$updated      = $this->db->query( $this->db->prepare( $update_query, $now, $now ) );
		if ( count( $revenue_ids ) === $updated ) {
			$this->revenue_meta->bulk_insert(
				array_map(
					function( $revenue_id ) {
						return [
							'key'        => 'billing_method',
							'revenue_id' => $revenue_id,
							'value'      => 'bank',
						];
					},
					$revenue_ids
				)
			);
		}
		do_action( 'sharee_revenue_transfered', $user_ids );
		return $updated;
	}

	/**
	 * Get fixed billing in month.
	 *
	 * @param int   $year  Billing year.
	 * @param int   $month Billing month. If 0 is set, all month.
	 * @param array $types Predefined type of billing.
	 * @return array
	 */
	public function get_fixed_billing( $year, $month = 0, $types = [] ) {
		$wheres = [];
		if ( $month ) {
			// Search with year month.
			$wheres[] = $this->db->prepare( '( EXTRACT(YEAR_MONTH FROM fixed) = %d )', sprintf( '%04d%02d', $year, $month ) );
		} else {
			// Search with year only.
			$wheres[] = $this->db->prepare( '( EXTRACT(YEAR FROM fixed) = %d )', sprintf( '%04d', $year ) );
		}
		$wheres[] = '( status = 1 )';
		if ( $types ) {
			$wheres[] = sprintf(
				'( revenue_type IN (%s) )',
				implode(
					', ',
					array_map(
						function( $type ) {
							return $this->db->prepare( '%s', $type );
						},
						$types
					)
				)
			);
		}
		$wheres = 'WHERE ' . implode( ' AND ', $wheres );
		$query  = <<<SQL
			SELECT
				SUM( price * unit ) AS before_tax,
				SUM(tax) AS tax,
				SUM(deducting) AS deducting,
				SUM(total) AS total,
				object_id,
				`fixed`
			FROM {$this->table}
			{$wheres}
			GROUP BY object_id
			ORDER BY object_id DESC
SQL;
		return $this->get_results( $query );
	}

	/**
	 * Get payment result
	 *
	 * @param int $year
	 * @param int $user_id
	 * @return array
	 */
	public function get_payment_list( $year, $user_id = 0 ) {
		$wheres = [];
		if ( $user_id ) {
			$wheres[] = sprintf( '(r.object_id = %d)', $user_id );
		}
		$wheres[] = '( r.status = 1 )';
		$wheres[] = sprintf( '( EXTRACT(YEAR from r.fixed) = %04d )', $year );
		$wheres   = ' WHERE ' . implode( ' AND ', $wheres );
		$query    = <<<SQL
			SELECT
				SUM(r.total) AS total,
				SUM(r.deducting) AS deducting,
				r.object_id AS user_id,
				u.display_name,
				fixed
			FROM {$this->table} AS r
			LEFT JOIN {$this->db->users} AS u
			ON u.ID = r.object_id
			{$wheres}
			group by r.object_id, r.fixed
			ORDER BY r.fixed
SQL;
		return $this->get_results( $query );
	}

	/**
	 * Get list of my number.
	 *
	 * @param int $year Year
	 *
	 * @return array
	 */
	public function get_my_numbers( $year ) {
		$users    = $this->select( 'u.*, SUM( s.total ) AS amount' )
			->from( "{$this->db->users} AS u" )
			->join( "{$this->table} AS s", 'u.ID = s.user_id' )
			->wheres(
				[
					'EXTRACT( YEAR FROM s.fixed ) = %d' => $year,
				]
			)
			->group_by( 'u.ID' )
			->result();
		$user_ids = [];
		foreach ( $users as $user ) {
			$user_ids[] = $user->ID;
		}
		// Get user meta
		$metas = $this->select( '*' )
			->from( $this->db->usermeta )
			->where_in( 'user_id', $user_ids, '%d' )
			->where_in( 'meta_key', [ '_billing_name', '_billing_number', '_billing_address' ] )
			->result();
		return array_map(
			function( $user ) use ( $metas ) {
				$user->my_number = '';
				$user->address   = '';
				foreach ( $metas as $row ) {
					if ( (int) $row->user_id !== (int) $user->ID ) {
						continue;
					}
					switch ( $row->meta_key ) {
						case '_billing_name':
							$user->display_name = $row->meta_value;
							break;
						case '_billing_number':
							$user->my_number = $row->meta_value;
							break;
						case '_billing_address':
							$user->address = $row->meta_value;
							break;
						default:
							// Do nothing.
							break;
					}
				}
				return $user;
			},
			$users
		);
	}

	/**
	 * Get availalble years.
	 */
	public function available_years() {
		$query  = <<<SQL
			SELECT EXTRACT(YEAR FROM created) FROM {$this->table}
			ORDER BY created ASC
			LIMIT 1
SQL;
		$oldest = intval( $this->get_var( $query ) ?: date_i18n( 'Y' ) );
		$range  = range( $oldest, (int) date_i18n( 'Y' ) );
		return array_reverse( $range );
	}

	/**
	 * Format price.
	 *
	 * @param float $price
	 * @return string
	 */
	public function format( $price ) {
		return apply_filters( 'sharee_price_formatting', sprintf( '&yen; %s', number_format( $price ) ), $price );
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'label':
				return $this->get_labels();
				break;
			case 'status':
				return $this->get_status();
				break;
			case 'status_class':
				return $this->get_status_class();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
