<?php

namespace Hametuha\Sharee\Table;

use Hametuha\Sharee\Master\Account;
use Hametuha\Sharee\Master\Address;
use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Utilities\TableHelper;
use Hametuha\Sharee\Service\Bank;

/**
 * Billing List
 *
 * @package sharee
 * @property-read RevenueModel $model
 */
class BillingListTable extends \WP_List_Table {

	use TableHelper;

	public $summary = null;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'user_billing',
				'plural'   => 'user_billings',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		$classes = [];
		foreach ( parent::get_table_classes() as $class ) {
			if ( 'fixed' === $class ) {
				continue;
			}
			$classes[] = $class;
		}
		return $classes;
	}


	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'        => '<input type="checkbox" />',
			'payable'   => '',
			'user'      => __( 'User' ),
			'account'   => __( 'Bank Account', 'sharee' ),
			'deducting' => __( 'Deducting', 'sharee' ),
			'total'     => __( 'Transfer Amount', 'sharee' ),
		];
	}

	/**
	 * Register actions
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return [
			'update'   => __( 'Mark as paid', 'sharee' ),
			'download' => __( 'Download CSV', 'sharee' ),
		];
	}

	/**
	 * Register items.
	 */
	public function prepare_items() {
		// Set column header
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		// Get current status
		list( $status, $year, $monthnum, $type, $page_num ) = $this->get_current_properties();
		$this->items                                        = $this->model->get_billing_list( $year, $monthnum );
		$this->summary                                      = $this->model->get_billing_summary( $year, $monthnum );
		$total = $this->model->found_rows();
		$this->set_pagination_args(
			[
				'total_items' => $total,
				'per_page'    => $total,
			]
		);
	}

	/**
	 * Checkbox column
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return '<input type="checkbox" class="billing-user" name="user_id[]" value="' . esc_attr( $item->object_id ) . '" />';
	}

	/**
	 * Get column
	 *
	 * @param \stdClass $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'payable':
				$billing = new Address( $item->object_id );
				if ( $billing->validate() ) {
					$color = 'green';
					$icon  = 'yes';
				} else {
					$color = 'red';
					$icon  = 'no';
				}
				printf( '<i class="dashicons dashicons-%s" style="color: %s"></i>', $icon, $color );
				break;
			case 'user':
				$user = get_userdata( $item->object_id );
				if ( ! $user ) {
					printf( '<span style="color: lightgrey">%s</span>', esc_html__( 'Deleted User', 'sharee' ) );
				} else {
					echo esc_html( $user->display_name );
				}
				break;
			case 'account':
				$account = new Account( $item->object_id );
				if ( $account->validate() ) {
					echo esc_html( $account->format_line() );
				} else {
					echo '<span style="color: lightgrey">---</span>';
				}
				break;
			case 'deducting':
			case 'total':
				echo $this->model->format( $item->{$column_name} );
				break;
		}
	}


	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 3.1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		$this->filter_inputs();
	}

	/**
	 * Get model
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'model':
				return RevenueModel::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}
}
