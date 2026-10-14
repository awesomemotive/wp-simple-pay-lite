<?php
/**
 * Admin setting fields: Radio
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Radio input field.
 *
 * Outputs a fieldset of radio inputs for multiple choices.
 *
 * @since 3.0.0
 */
class Radio extends Field {

	/**
	 * Inline radios.
	 *
	 * @access private
	 * @var bool
	 */
	private $inline = false;

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {

		$this->type_class = 'simpay-field-radios';
		$this->inline     = isset( $field['inline'] ) ? ( 'inline' == $field['inline'] ? true : false ) : false;

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		if ( ! empty( $this->options ) && is_array( $this->options ) && count( $this->options ) > 0 ) {
			?>

				<fieldset
					id="<?php echo $this->id;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
					<?php echo $this->style ? 'style="' . $this->style . '"' : '';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>
				>
				<?php

				if ( ! empty( $this->title ) ) :

					?>
					<legend class="screen-reader-text">
						<span><?php echo $this->title;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?></span>
					</legend>
					<?php

				endif;

				?>

				<ul <?php echo $this->inline === true ? 'class="simpay-field-radios-inline"' : ''; ?>>
					<?php foreach ( $this->options as $option => $name ) : ?>
						<li>
							<label for="<?php echo esc_attr( $this->id . '-' . trim( strval( $option ) ) ); ?>">
								<input
									name="<?php echo $this->name;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
									id="<?php echo esc_attr( $this->id . '-' . trim( strval( $option ) ) ); ?>"
									class="simpay-field simpay-field-radio <?php echo $this->class;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
									type="radio"
									value="<?php echo esc_attr( trim( strval( $option ) ) ); ?>"
									<?php echo $this->attributes;  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>
									<?php checked( $option, $this->value, true ); ?>
								/>
								<?php echo esc_attr( $name ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php echo $this->description ? '<p class="description">' . wp_kses_post( $this->description ) . '</p>' : ''; ?>

			</fieldset>
			<?php
		}
	}

}
