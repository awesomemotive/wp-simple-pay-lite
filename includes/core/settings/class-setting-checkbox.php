<?php
/**
 * Settings: Checkbox Setting
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
 * Setting_Checkbox class
 *
 * @since 4.0.0
 */
class Setting_Checkbox extends Setting_Input {

	/**
	 * Checkbox input label.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $input_label;

	/**
	 * Constructs the Setting.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting configuration.
	 *
	 *   @type string $input_label Setting input label.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'input_label' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Input label.
		$this->input_label = $args['input_label'];

		parent::__construct( $args );
	}

	/**
	 * Outputs the setting markup.
	 *
	 * @since 4.0.0
	 */
	public function output() {
		?>

		<label for="<?php echo esc_attr( $this->get_id() ); ?>">
			<input
				name="<?php echo esc_attr( $this->get_name() ); ?>"
				id="<?php echo esc_attr( $this->get_id() ); ?>"
				value="yes"
				class="<?php echo esc_attr( $this->get_class() ); ?>"
				type="checkbox"
				<?php checked( 'yes', $this->get_value() ); ?>
			/>

			<?php echo wp_kses_post( $this->input_label ); ?>
		</label>

		<?php
		if ( ! empty( $this->description ) ) :
			echo wp_kses_post( $this->description );
		endif;
		?>

		<?php
	}

}
