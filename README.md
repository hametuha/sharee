# Sharee
WordPress library for Reward manager.

## Installation

```
composer require hametuha/sharee
```	

To enable sharee, call bootstrap method.

```php
// Call before after_setup_theme
\Hametuha\Sharee::get_instance();
```

## Features

### Reward List

Reard list on WordPress Dashboard.

### Payment List

Payment list is a expected payment list for user's reward.
Works fine with hashboard.

```php
/**
 * Enable payment list
 * 
 * @param bool $enabled Default false.
 * @param bool $service Service name to be enabled.
 */
add_filter( 'sharee_should_enable', function( $enabled, $service ) {
	switch ( $service ) {
		case 'billing': // Billing is billing list.
			return true;
		default:
			return $enabled;
	}
}, 10, 2 );
```

## API

Sharee has no screen to add reward record.
You have to enter one manually.

### Add Record

```php
$result = RevenueModel::get_instance()->add_revenue( 'kdp', $user_id, $price, [
	'unit'        => $unit,
	'total'       => $total,
	'tax'         => $tax,
	'deducting'   => $deducting,
	'description' => $label
] );
if ( $result && ! is_wp_error( $result ) ) {
	$success++;
}
```
