<?php

namespace Hametuha\Sharee\Screen;


use Hametuha\Pattern\TableScreen;
use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Table\RewardListTable;

class RewardList extends TableScreen {

	protected $slug   = 'user-reward';

	protected $parent = 'users.php';

	protected $table_class = RewardListTable::class;

	protected $has_search = false;

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	protected function get_title() {
		return __( 'User Rewards', 'sharee' );
	}

	/**
	 * Do something before table.
	 */
	protected function before_table() {
        $model = RevenueModel::get_instance();
        if ( $this->table->summary ) : ?>
        <table class="sharee-summary-table">
            <caption><?php esc_html_e( 'Summary of Current Criteria', 'sharee' ) ?></caption>
            <thead>
            <tr>
                <th>&nbsp;</th>
                <th><?php esc_html_e( 'Found Count', 'sharee' ) ?></th>
                <th><?php esc_html_e( 'Sales', 'sharee' ) ?></th>
                <th><?php esc_html_e( 'Unit', 'sharee' ) ?></th>
                <th><?php esc_html_e( 'Tax', 'sharee' ) ?></th>
                <th><?php esc_html_e( 'Deducting', 'sharee' ) ?></th>
                <th><?php esc_html_e( 'Subtotal', 'sharee' ) ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th><?php esc_html_e( 'Total', 'sharee' ) ?></th>
                <td class="number"><?php echo number_format_i18n( $this->table->total_record() ) ?></td>
                <td class="number">-</td>
                <td class="number"><?php echo number_format_i18n( $this->table->summary->unit_sum ) ?></td>
                <td class="number">-</td>
                <td class="number">-</td>
                <td class="number"><?php echo $model->format( $this->table->summary->total_sum ) ?></td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <th><?php esc_html_e( 'Average', 'sharee' ) ?></th>
                <td class="number">-</td>
                <td class="number"><?php echo $model->format( $this->table->summary->price ) ?></td>
                <td class="number"><?php echo number_format_i18n( $this->table->summary->unit ) ?></td>
                <td class="number"><?php echo $model->format( $this->table->summary->tax ) ?></td>
                <td class="number"><?php echo $model->format( $this->table->summary->deducting ) ?></td>
                <td class="number"><?php echo $model->format( $this->table->summary->total ) ?></td>
            </tr>
            </tbody>
        </table>
        <?php endif;
	}
}
