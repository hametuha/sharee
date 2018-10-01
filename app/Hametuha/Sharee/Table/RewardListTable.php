<?php

namespace Hametuha\Sharee\Table;

use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Utilities\TableHelper;

/**
 * Class RevenueListTable
 *
 * @package sharee
 */
class RewardListTable extends \WP_List_Table {

    use TableHelper;

	/**
     * Get table summary.
     *
	 * @var null
	 */
    public $summary = null;


	function __construct() {
		parent::__construct( array(
			'singular' => 'user_reward',
			'plural'   => 'user_rewards',
			'ajax'     => false,
		) );
	}

	/**
	 * Get columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'label'  => __( 'Label', 'sharee' ),
			'price'  => __( 'Sales', 'sharee' ),
			'total'  => __( 'Subtotal', 'sharee' ),
			'status' => __( 'Status', 'sharee' ),
			'date'   => __( 'Date', 'sharee' ),
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
		list( $status, $year, $monthnum, $type, $page_num ) = $this->get_current_properties();
        $model  = RevenueModel::get_instance();
		$search_args = [
			'year'      => $year,
			'month'     => $monthnum,
			'object_id' => (int) filter_input( INPUT_GET, 'object_id' ),
			'status'    => $status,
			'type'      => $type,
			'per_page'  => 20,
			'page'      => $page_num,
        ];
		$this->items = $model->search( $search_args );
		$this->set_pagination_args( [
			'total_items' => $model->found_rows(),
			'per_page'    => 20,
		] );
		$this->summary = $model->search( $search_args, true );
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
			case 'label':
                $link  = '';
			    if ( $is_post = $model->is_post_revenue( $item->object_id ) ) {
			        // This is post.
			        if ( $post = get_post( $item->object_id ) ) {
						$label = get_the_title( $post );
						$link  = get_edit_post_link( $post );
                    } else {
			            $label = _x( 'Deleted', 'post_object', 'sharee' );
                    }
                } else {
			        // This is user.
					$user = get_userdata( $item->object_id );
					if ( ! $user ) {
						$label = __( 'Deleted User', 'sharee' );
					} else {
						$label = $user->display_name;
                        $link  = add_query_arg( [ 'user_id' => $item->object_id ], admin_url( 'user-edit.php' ) );
					}
                }
                $link = apply_filters( 'sharee_list_table_link', $link, $item->object_id, $is_post );
                if ( $link ) {
                    $label = sprintf( '<a href="%s">%s</a>', esc_url( $link ), esc_html( $label ) );
                } else {
                    $label = sprintf( '<span style="color:lightgrey">%s</span>', esc_html( $label ) );
                }
				printf( '<strong>[%s]</strong> %s -- %s', $model->type_label( $item->revenue_type ), esc_html( $item->description ), $label );
				break;
			case 'price':
				printf( '%s &times; %s', $model->format( $item->price ), number_format_i18n( $item->unit ) );
				break;
			case 'total':
				echo $model->format( $item->total );
				break;
			case 'unit':
				echo number_format( $item->unit );
				break;
			case 'status':
				echo $model->status_label( $item->status, true );
				break;
			case 'user':
				break;
			case 'date':
				echo mysql2date( get_option( 'date_format' ), $item->created );
				break;
		}
	}

	/**
	 * Get a list of CSS classes for the WP_List_Table table tag.
	 *
	 * @since 3.1.0
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
	    return array_filter( parent::get_table_classes(), function( $c ) {
	        return 'fixed' !== $c;
        } );
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
		if ( 'top' != $which ) {
			return;
		}
		$model = RevenueModel::get_instance();
		list( $status, $year, $month, $type, $page_num ) = $this->get_current_properties();
		?>
		<select name="status">
            <option value="all" <?php selected( 'all', $status ) ?>><?php esc_html_e( 'All Status', 'sharee' ) ?></option>
			<?php foreach ( $model->get_status() as $val => $label ) :?>
				<option value="<?= esc_attr( $val ) ?>" <?php selected( $val, $status ) ?>>
					<?= esc_html( $label ) ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php $this->filter_inputs() ?>
		<?php
	}

	/**
     * Returns total record count.
     *
	 * @return int
	 */
	public function total_record() {
	    return (int) $this->_pagination_args['total_items'];
    }

}
