<?php

namespace Hametuha\Sharee\Screen;


use Hametuha\Pattern\TableScreen;
use Hametuha\Sharee\Table\RewardListTable;

class RewardList extends TableScreen {

	protected $slug   = 'user-reward';

	protected $parent = 'users.php';

	protected $table_class = RewardListTable::class;

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
		?>
		<style>
			.label-default{
				color: lightgray;
			}
			.label-warning{
				color: orange;
			}
			.label-success{
				color: green;
			}
		</style>
		<?php
	}


}
