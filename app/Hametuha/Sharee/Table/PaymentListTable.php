<?php

namespace Hametuha\Sharee\Table;

use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Utilities\TableHelper;

/**
 * Display payment history.
 *
 * @package sharee
 */
class PaymentListTable extends \WP_List_Table {

	use TableHelper;

	/**
	 * Get table summary.
	 *
	 * @var null
	 */
	public $summary = null;

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'payment',
				'plural'   => 'payments',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'user'      => __( 'User', 'sharee' ),
			'total'     => __( 'Subtotal', 'sharee' ),
			'deducting' => __( 'Deducting', 'sharee' ),
			'tax'       => __( 'VAT', 'sharee' ),
			'paid_at'   => __( 'Payed At', 'sharee' ),
		];
	}

	/**
	 * Get items.
	 */
	public function prepare_items() {
		// Set column header.
		$this->_column_headers = [
			$this->get_columns(),
			[],
			$this->get_sortable_columns(),
		];
		// Search revenues.
		list( $status, $year, $monthnum, $type, $page_num, $user_id ) = $this->get_current_properties();
		$model       = RevenueModel::get_instance();
		$this->items = $model->get_payment_list( $year, $user_id );
		$this->set_pagination_args(
			[
				'total_items' => count( $this->items ),
				'per_page'    => count( $this->items ),
			]
		);
	}

	/**
	 * Get column
	 *
	 * @param \stdClass $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {
		$model = RevenueModel::get_instance();
		switch ( $column_name ) {
			case 'user':
				$url = add_query_arg( [
					'page'    => 'payment-history',
					'user_id' => $item->user_id,
					'year'    => filter_input( INPUT_GET, 'year' ) ?: date_i18n( 'Y' ),
				], admin_url( 'users.php' ) );
				printf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $item->display_name ) );
				break;
			case 'total':
			case 'tax':
			case 'deducting':
				echo $model->format( $item->{$column_name} );
				break;
			case 'paid_at':
				echo mysql2date( get_option( 'date_format' ), $item->fixed );
				break;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_table_classes() {
		return array_filter(
			parent::get_table_classes(),
			function( $c ) {
				return 'fixed' !== $c;
			}
		);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		?>
		<label>
			<?php esc_html_e( 'User ID', 'sharee' ); ?>
			<input style="width: 3em;" type="number" value="<?php echo esc_attr( filter_input( INPUT_GET, 'user_id' ) ); ?>" name="user_id" />
		</label>
		<?php
		$this->filter_inputs( false );
	}

	/**
	 * {@inheritdoc}
	 */
	public function total_record() {
		return (int) $this->_pagination_args['total_items'];
	}

}
