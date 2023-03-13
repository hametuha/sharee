<?php

namespace Hametuha\Sharee\Screen;


use Hametuha\Pattern\TableScreen;
use Hametuha\Sharee\Models\RevenueModel;
use Hametuha\Sharee\Table\PaymentListTable;
use Hametuha\Sharee\Table\RewardListTable;

class PaymentList extends TableScreen {

	protected $slug = 'payment-history';

	protected $parent = 'users.php';

	protected $table_class = PaymentListTable::class;

	protected $has_search = true;

	/**
	 * {@inheritdoc}
	 */
	protected function get_title() {
		return __( 'Payment History', 'sharee' );
	}
}
