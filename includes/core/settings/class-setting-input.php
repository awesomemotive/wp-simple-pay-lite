<?php
/**
 * Settings: Input Setting
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
 * Setting_Input class
 *
 * @since 4.0.0
 */
class Setting_Input extends Setting {

	/**
	 * Setting option name.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $option_name = 'simpay_settings';

	/**
	 * Setting type.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $type;

	/**
	 * Setting placeholder.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $placeholder;

	/**
	 * Setting minimum (number, range, etc).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $min;

	/**
	 * Setting maximum (number, range, etc).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $max;

	/**
	 * Setting step (number, range, etc).
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $step;

	/**
	 * Additional input attributes (readonly, disabled, etc)
	 *
	 * @since 4.0.0
	 * @var array
	 */
	public $attributes = array();

	/**
	 * Constructs the Setting.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting configuration.
	 *
	 *   @type string $type Setting type.
	 *   @type string $placeholder Setting placeholder.
	 *   @type string $min Setting minimum.
	 *   @type string $max Setting maximum.
	 *   @type string $step Setting step.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'type'        => 'text',
			'value'       => '',
			'placeholder' => '',
			'min'         => '',
			'max'         => '',
			'step'        => '',
			'description' => '',
			'classes'     => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Type.
		$type = $args['type'];

		$allowed_types = array(
			'text',
			'url',
			'number',
			'range',
			'tel',
			'password',
		);

		if ( ! in_array( $type, $allowed_types, true ) ) {
			$type = 'text';
		}

		$this->type = esc_attr( $args['type'] );

		// Value.
		$this->value = esc_attr( $args['value'] );

		// Placeholder.
		$this->placeholder = esc_attr( $args['placeholder'] );

		// Min/Max/Step.
		$this->min  = esc_attr( $args['min'] );
		$this->max  = esc_attr( $args['max'] );
		$this->step = esc_attr( $args['step'] );

		// Description.
		$this->description = $args['description'];

		// Classes.
		$this->classes = is_array( $args['classes'] )
			? array_merge(
				array(
					'',
				),
				$args['classes']
			)
			: array();

		// Allowed attributes.
		$allowed_attributes = array(
			'readonly',
			'disabled',
		);

		// Search arguments for any additional attributes that are allowed.
		foreach ( $args as $key => $value ) {
			if ( in_array( $key, $allowed_attributes, true ) ) {
				$this->attributes[ $key ] = $value;
			}
		}

		parent::__construct( $args );
	}

	/**
	 * Return's the setting's value.
	 *
	 * @since 4.0.0
	 *
	 * @return null|mixed
	 */
	protected function get_value() {
		return esc_attr( $this->value );
	}

	/**
	 * Return's the setting's `id` attribute.
	 *
	 * @since 4.0.0
	 *
	 * @param string $group Setting name group. Optional. Default empty string.
	 * @return string
	 */
	protected function get_name( $group = '' ) {
		return esc_attr(
			sprintf(
				'%1$s%2$s[%3$s]',
				$this->option_name,
				$group,
				$this->id
			)
		);
	}

	/**
	 * Return's the setting's `name` attribute.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_id() {
		return esc_attr(
			sprintf(
				'simpay-settings-%1$s-%2$s-%3$s',
				$this->section,
				$this->subsection,
				$this->id
			)
		);
	}

	/**
	 * Return's the setting's `class` attribute.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	protected function get_class() {
		return implode(
			' ',
			array_map(
				'trim',
				$this->classes
			)
		);
	}

	/**
	 * Outputs the setting markup.
	 *
	 * @since 4.0.0
	 */
	public function output() {
		?>

		<input
			name="<?php echo esc_attr( $this->get_name() ); ?>"
			id="<?php echo esc_attr( $this->get_id() ); ?>"
			value="<?php echo esc_attr( $this->get_value() ); ?>"
			class="<?php echo esc_attr( $this->get_class() ); ?>"
			type="<?php echo esc_attr( $this->type ); ?>"
			min="<?php echo esc_attr( $this->min ); ?>"
			max="<?php echo esc_attr( $this->max ); ?>"
			step="<?php echo esc_attr( $this->step ); ?>"
			placeholder="<?php echo esc_attr( $this->placeholder ); ?>"
			autocomplete="off"
			<?php foreach ( $this->attributes as $attribute => $value ) : ?>
				<?php echo esc_html( $attribute ); ?>="<?php echo esc_attr( $value ); ?>"
			<?php endforeach; ?>
		/>

		<?php
		if ( ! empty( $this->description ) ) :
			echo wp_kses_post( $this->description );
		endif;
		?>

		<?php
	}

}
