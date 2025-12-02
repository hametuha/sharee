# Sharēe

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

Reward list on WordPress Dashboard.

### Payment List

Payment list is an expected payment list for user's reward.
Works fine with [hametuha/hashboard](https://packagist.org/packages/hametuha/hashboard).

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
Some lines of code are required.

### Add Record

```php
// Add revenue.
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

## Query User

There are 2 additional query vars for user query.

- `paid_since`: Filter users who have been paid since the date.
- `paid_until`: Filter users who have been paid until the date.

This works with `WP_User_Query`.

```php
// get users who got paid between Year 2018.
$query = new WP_User_Query( [
	'role'        => 'subscriber',
	'paid_since'  => '2018-01-01',
	'paid_until'  => '2018-12-31',
	'number'      => 10,
	'paged'       => 1,
] );
```
