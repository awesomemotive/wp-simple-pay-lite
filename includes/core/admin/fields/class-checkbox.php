<?php
/**
 * Admin setting fields: Checkbox
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checkbox input field.
 *
 * Outputs one single checkbox or a fieldset of checkboxes for multiple choices.
 *
 * @since 3.0.0
 */
class Checkbox extends Field {

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {
		$this->type_class = 'simpay-field-checkboxes';
		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		if ( ! empty( $this->options ) && is_array( $this->options ) && count( $this->options ) > 0 ) {

			if ( ! empty( $this->description ) ) {
				echo '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
			}

			?>
			<fieldset <?php echo ! empty( $this->style ) ? 'style="' . $this->style . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>>
				<?php

				if ( ! empty( $this->title ) ) {
					echo '<legend class="screen-reader-text"><span>' . $this->title . '</span></legend>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor
				}

				?>
				<ul>
					<?php foreach ( $this->options as $option => $name ) : ?>
						<li>
							<label for="<?php echo esc_attr( $this->id . '-' . trim( strval( $option ) ) ); ?>">
								<input name="<?php echo $this->name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
									   id="<?php echo esc_attr( $this->id . '-' . trim( strval( $option ) ) ); ?>"
									   class="simpay-field simpay-field-checkbox <?php echo $this->class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
									   type="checkbox"
									   value="<?php echo esc_attr( trim( strval( $option ) ) ); ?>"
									<?php checked( $this->value, 'yes', true ); ?>
									<?php echo $this->attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>
								/><?php echo esc_attr( $name ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php

		} else {

			?>
			<label for="<?php echo esc_attr( $this->id ); ?>" class="simpay-field-bool" <?php echo $this->style ? 'style="' . $this->style . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>>
				<?php if ( ! empty( $this->title ) ) : ?>
					<span class="screen-reader-text"><?php echo $this->title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?></span>
				<?php endif; ?>
				<input name="<?php echo $this->name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
					   type="checkbox"
					   id="<?php echo $this->id; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
					   class="simpay-field simpay-field-checkbox <?php echo $this->class; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in constructor ?>"
					   value="yes"
					<?php checked( $this->value, 'yes', true ); ?>
					<?php echo $this->attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>/><?php echo( ! empty( $this->text ) ? wp_kses_post( $this->text ) : esc_html__( 'Yes', 'simple-pay' ) ); ?>
			</label>
			<?php

			if ( ! empty( $this->description ) ) {
				echo '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
			}
		}

	}

}
