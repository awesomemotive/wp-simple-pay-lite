<?php
/**
 * Settings: Select setting
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setting_Select class
 *
 * @since 4.0.0
 */
class Setting_Select extends Setting_Input {

	/**
	 * Setting options.
	 *
	 * @since 4.0.0
	 * @var mixed
	 */
	public $options;

	/**
	 * Constructs the Setting.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type array $options Setting options.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'options' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Options.
		$this->options = $args['options'];

		parent::__construct( $args );
	}

	/**
	 * Outputs the setting markup.
	 *
	 * @since 4.0.0
	 */
	public function output() {
		?>

		<select
			name="<?php echo esc_attr( $this->get_name() ); ?>"
			id="<?php echo esc_attr( $this->get_id() ); ?>"
			class="<?php echo esc_attr( $this->get_class() ); ?>"
		>
			<?php foreach ( $this->options as $value => $label ) : ?>
				<option
					value="<?php echo esc_attr( $value ); ?>"
					<?php selected( $value, $this->get_value() ); ?>
				>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php
		if ( ! empty( $this->description ) ) :
			echo wp_kses_post( $this->description );
		endif;
		?>

		<?php
	}

}
