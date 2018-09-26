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

	protected $version = '0.8.0';

	protected $name    = 'revenues';

	protected $default_placeholder = [
		'revenue_id' => '%d',
		'revenue_type' => '%s',
		'object_id' => '%d',
		'price' => '%f',
		'unit' => '%f',
		'tax' => '%f',
		'deducting' => '%f',
		'total' => '%f',
		'status' => '%s',
		'description' => '%s',
		'created' => '%s',
		'fixed'   => '%s',
		'updated' => '%s',
	];

	protected $models = [
		'revenue_meta' => RevenueMetaModel::class,
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
		return in_array( $type, $this->get_types_for_post_type() );
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
	 * Get labels
	 *
	 * @return array
	 */
	public function get_status() {
		return array_merge( [
			0  => _x( 'Pending', 'revenue-status', 'sharee' ),
			1  => _x( 'Paid', 'revenue-status', 'sharee' ),
			-1 => _x( 'Rejected', 'revenue-status', 'sharee' ),
		], apply_filters( 'sharee_additional_status', [] ) );
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
	 * Get status clss
	 *
	 * @return array
	 */
	public function get_status_class() {
		return apply_filters( 'sharee_status_class', [
			0  => 'warning',
			1  => 'success',
			-1 => 'default',
		] );
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
		$d = new \DateTime();
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
		$old_status = $old_revenue->status;
		$old_label  = $this->label( $old_status );
		$new_label  = $this->label( $status );
		if ( $old_status == $status ) {
			// Nothing changes.
			return false;
		}
		$updated = $this->update( [
			'status'   => $status,
		], [
			'revenue_id' => $revenue_id,
		] );
		if ( $updated ) {
			// Save log.
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
		$args = wp_parse_args( $args, [
			'revenue_type' => $type,
			'object_id'    => $type,
			'price'        => $price,
			'total'        => $price,
			'unit'         => 1,
			'tax'          => 0,
			'deducting'    => 0,
			'description'  => '',
			'status'       => 0,
		] );
		// Save revenue.
		return $this->insert( $args );
	}

	/**
	 * Search
	 *
	 * @param array $args
	 * @return array
	 */
	public function search( $args ) {
		$args = wp_parse_args( $args, [
			'year'     => 0,
			'month'    => 0,
			'page'     => 1,
			'per_page' => 20,
			'status'   => null,
			'type'     => [],
		] );
		// Build where flags.
		$wheres = [];
		$type = array_filter( (array) $args['type'] );
		if ( 1 == count( $type ) ) {
			$wheres[] = $this->db->prepare( '( type = %s )', $type[0] );
		} else if ( $type ) {
			$wheres[] = sprintf( '( `type` IN ( %s ) )', implode( ', ', array_map( function( $t ) {
				return $this->db->prepare( '%s', $t );
			}, $type ) ) );
		}
		if ( is_numeric( $args['status'] ) ) {
			$wheres[] = $this->db->prepare( '( `status` = %d )', $args['status'] );
		}
		if ( $args['year'] && $args['month']) {
			$wheres[] = sprintf( '( EXTRACT(YEAR_MONTH from `created`) = %04d%02d )', $args['year'], $args['month'] );
		} elseif ( $args['year'] ) {
			$wheres[] = sprintf( '( EXTRACT(YEAR from `created`) = %04d )', $args['year'] );
		} elseif ( $args['month'] ) {
			$wheres[] = sprintf( '( EXTRACT(MONTH from `created`) = %02d )', $args['month'] );
		}
		$where = $wheres ? 'WHERE ' . implode( ' AND ', $wheres ) : '';
		$query = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS * FROM {$this->table}
			{$where}
			ORDER BY created DESC
SQL;
		if ( $args['per_page'] ) {
			$query .= sprintf( ' LIMIT %d, %d', $args['per_page'] * ( max( 1, $args['page'] ) - 1 ), $args['per_page']  );
		}
		return $this->get_results( $query );
	}

	/**
	 * Get availalble years.
	 */
	public function available_years() {
		$query = <<<SQL
			SELECT EXTRACT(YEAR FROM created) FROM {$this->table}
			ORDER BY created ASC
			LIMIT 1
SQL;
		$oldest = intval( $this->get_var( $query ) ?: date_i18n( 'Y') );
		$range = range( $oldest, (int) date_i18n( 'Y' ) );
		return array_reverse( $range );
	}

	/**
	 * Format price.
	 *
	 * @param float $price
	 * @return string
	 */
	public function format( $price ) {
		return sprintf( '&yen; %s', number_format( $price ) );
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
