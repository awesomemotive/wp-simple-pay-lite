<?php
/**
 * Server compatibility check.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Bootstrap\Compatibility;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a list of server requirements.
 *
 * These requirements are based off the Stripe PHP binding dependencies.
 *
 * @link https://github.com/stripe/stripe-php
 * @link https://github.com/stripe/stripe-php#dependencies
 *
 * @since 3.6.0
 *
 * @return array List of requirements.
 */
function get_requirements_list() {
	return array(
		'wp'       => array(
			'name'     => 'WordPress',
			'requires' => '4.9.0',
			'met'      => version_compare( get_bloginfo( 'version' ), '4.9.0', '>=' ),
		),
		'php'      => array(
			'name'     => 'PHP',
			'requires' => '5.6.0',
			'met'      => version_compare( PHP_VERSION, '5.6.0', '>=' ),
		),
		'curl'     => array(
			'name' => 'cURL',
			'met'  => function_exists( 'curl_version' ),
		),
		'json'     => array(
			'name' => 'JSON',
			'met'  => function_exists( 'json_encode' ),
		),
		'mbstring' => array(
			'name' => 'Multibyte String',
			'met'  => function_exists( 'mb_strtolower' ),
		),
	);
}

/**
 * Determines if all requirements are met.
 *
 * @since 3.6.0
 *
 * @return bool $met True if all requirements are met.
 */
function server_requirements_met() {
	$requirements = get_requirements_list();
	$to_meet      = wp_list_pluck( $requirements, 'met' );
	$met          = ! array_search( false, $to_meet, true );

	return $met;
}

/**
 * Shows a notice for each unmet requirement.
 *
 * @since 3.6.0
 */
function show_admin_notices() {
	wp_load_translations_early();
	$requirements = get_requirements_list();

	foreach ( $requirements as $requirement ) {
		// Do nothing if the requirement is met.
		if ( true === $requirement['met'] ) {
			continue;
		}

		/**
		 * Generates a notice for an umet requirement.
		 *
		 * If a specific version number is required, show it.
		 *
		 * @since 3.6.0
		 *
		 * @uses $requirement Current requirement to check.
		 */
		add_action( 'admin_notices', function() use ( $requirement ) {
?>

<div class="error">
	<p>
	<?php
	if ( isset( $requirement['requires'] ) ) :
		echo wp_kses(
			sprintf(
				esc_html__(
					/* translators: %1$s Plugin name, do not translate. %2$s Requirement name, do not translate. %3$s Requirement version, do not translate. */
					'%1$s requires %2$s version %3$s or higher.',
					'stripe'
				),
				SIMPLE_PAY_ITEM_NAME,
				'<code>' . esc_html( $requirement['name'] ) . '</code>',
				'<code>' . esc_html( $requirement['requires'] ) . '</code>'
			),
			array(
				'code' => true,
			)
		);
	else :
		echo wp_kses(
			sprintf(
				esc_html__(
					/* translators: %1$s Plugin name, do not translate. %2$s Requirement name, do not translate. */
					'%1$s requires %2$s to be installed.',
					'stripe'
				),
				SIMPLE_PAY_ITEM_NAME,
				'<code>' . esc_html( $requirement['name'] ) . '</code>'
			),
			array(
				'code' => true,
			)
		);
	endif;
	?>
	</p>

	<p><strong><?php _e( 'Need help with your server? Ask your web host!', 'stripe' ); ?></strong></p>
	<p>
	<?php
	$hosting_url = add_query_arg(
		array(
			'utm_source'   => 'inside-plugin',
			'utm_medium'   => 'link',
			'utm_campaign' => defined( 'SIMPLE_PAY_ITEM_ID' ) ? 'pro-plugin' : 'lite-plugin',
			'utm_content'  => 'compatibility-check',
		),
		'https://wpsimplepay.com/recommended-wordpress-hosting/'
	);

	echo wp_kses(
		sprintf(
			__(
				'Many web hosts can give you instructions on how/where to upgrade your server through their control panel, or may even be able to do it for you. If you need to change hosts, please see %1$sour hosting recommendations%2$s.',
				'stripe'
			),
			'<a href="' . esc_url( $hosting_url ) .'" target="_blank" rel="noopener noreferrer">',
			'</a>'
		),
		array(
			'a' => array(
				'href'   => true,
				'target' => true,
				'rel'    => true,
			),
		)
	);
	?>
	</p>
</div>

<?php
		} );
	}
}
