<?php

namespace Hametuha\Sharee\Hooks;


use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\SingletonPattern\Singleton;

/**
 * Add additional user quer.
 *
 * Add user query to filter by payment history.
 *
 * @since 0.8.11
 */
class AdditionalUserQuery extends Singleton {

	protected function init() {
		add_action( 'pre_user_query', [ $this, 'filter_user_query' ] );
		add_action( 'pre_get_users', [ $this, 'append_paid_date_filters' ] );
		add_action( 'restrict_manage_users', [ $this, 'add_paid_filter_to_users_list' ] );
	}

	/**
	 * Filter user query.
	 *
	 * @param \WP_User_Query $query
	 *
	 * @return void
	 */
	public function filter_user_query( $query ) {
		// Format date.
		foreach ( [
			[ 'paid_since', 'Y-m-01', ' 00:00:00' ],
			[ 'paid_until', 'Y-m-t', ' 23:59:59' ],
		] as list( $key, $format, $suffix ) ) {
			if ( ! isset( $query->query_vars[ $key ] ) ) {
				// Not set.
				continue;
			}
			$date = $this->ensure_datetime( $query->query_vars[ $key ] );
			if ( ! $date ) {
				// This is invalid date.
				$query->set( $key, false );
			} else {
				$query->set( $key, $date );
			}
		}
		$since = $query->query_vars['paid_since'] ?? false;
		$until = $query->query_vars['paid_until'] ?? false;
		if ( ! $since && ! $until ) {
			// No period. Do nothing.
			return;
		}
		// Add DISTINCT to avoid duplicated user by LEFT JOIN.
		if ( ! preg_match( '/\bDISTINCT\b/i', $query->query_fields ) ) {
			// SQL_CALC_FOUND_ROWS is not compatible with DISTINCT.
			$query->query_fields = preg_replace( '/\bsql_calc_found_rows\b/i', 'SQL_CALC_FOUND_ROWS DISTINCT', $query->query_fields, 1 );
		} else {
			$query->query_fields = 'DISTINCT ' . $query->query_fields;
		}
		// Add join clause.
		$query->query_from .= " LEFT JOIN {$this->model()->table} AS ur ON ur.object_id = {$this->model()->db->users}.ID";
		// Add where clause to filter by paid date.
		if ( $since && $until ) {
			$query->query_where .= $this->model()->db->prepare( ' AND ur.fixed BETWEEN %s AND %s', $since, $until );
		} elseif ( $since ) {
			$query->query_where .= $this->model()->db->prepare( ' AND ur.fixed >= %s', $since, $until );
		} else {
			$query->query_where .= $this->model()->db->prepare( ' AND ur.fixed <= %s', $since, $until );
		}
	}

	/**
	 * Get model instance.
	 *
	 * @return RevenueModel
	 */
	protected function model() {
		return RevenueModel::get_instance();
	}

	/**
	 * Ensure datetime format.
	 *
	 * @param string $date_string
	 * @return string
	 */
	public function ensure_datetime( $date_string, $format = 'Y-m-01', $suffix = ' 00:00:00' ) {
		try {
			$date_string = trim( $date_string );
			if ( preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date_string ) ) {
				// This is date time format. Do nothing.
				return $date_string;
			}
			// If this is year-month format, add day.
			if ( preg_match( '/^\d{4}-\d{2}$/', $date_string ) ) {
				$date_string = ( new \DateTime( $date_string . '-01', wp_timezone() ) )->format( $format );
			}
			// If this is date format, add time.
			if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_string ) ) {
				$date_string .= $suffix;
			}
			// Finally check this is really datetime.
			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date_string ) ) {
				// Allow 'now' format.
				$date = new \DateTime( $date_string, wp_timezone() );
				return $date->format( 'Y-m-d H:i:s' );
			}
			return $date_string;
		} catch ( \Exception $e ) {
			// Invalid date.
			return '';
		}
	}

	/**
	 * Add optional query if url query parameter is set.
	 *
	 * @param \WP_User_Query $query
	 * @return void
	 */
	public function append_paid_date_filters( $query ) {
		// If this is admin screen, check the input.
		if ( is_admin() ) {
			$screen = get_current_screen();
			if ( 'users' === $screen->base && current_user_can( 'list_users' ) ) {
				// This is user list.
				$paid_since = filter_input( INPUT_GET, 'paid_since' );
				$paid_until = filter_input( INPUT_GET, 'paid_until' );
				if ( $paid_since ) {
					$query->set( 'paid_since', $paid_since );
				}
				if ( $paid_until ) {
					$query->set( 'paid_until', $paid_until );
				}
			}
		}
	}

	/**
	 * Add paid user filter.
	 *
	 * @return void
	 */
	public function add_paid_filter_to_users_list( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		$paid_since = filter_input( INPUT_GET, 'paid_since' );
		$paid_until = filter_input( INPUT_GET, 'paid_until' );
		?>
		<label for="paid_since" style="margin-left: 1em;"><?php echo esc_html_x( 'Paid since', 'paid-period', 'sharee' ); ?></label>
		<input type="date" name="paid_since" id="paid_since" value="<?php echo esc_attr( $paid_since ); ?>">

		<label for="paid_until"><?php echo esc_html_x( 'Until', 'paid-period', 'sharee' ); ?></label>
		<input type="date" name="paid_until" id="paid_until" value="<?php echo esc_attr( $paid_until ); ?>">
		<?php
	}
}
