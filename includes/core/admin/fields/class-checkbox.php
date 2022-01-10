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
			<fieldset class="<?php echo $this->class; ?>" <?php echo ! empty( $this->style ) ? 'style="' . $this->style . '"' : ''; ?>>
				<?php

				if ( ! empty( $this->title ) ) {
					echo '<legend class="screen-reader-text"><span>' . $this->title . '</span></legend>';
				}

				?>
				<ul>
					<?php foreach ( $this->options as $option => $name ) : ?>
						<li>
							<label for="<?php echo $this->id . '-' . trim( strval( $option ) ); ?>">
								<input name="<?php echo $this->name; ?>"
									   id="<?php echo $this->id . '-' . trim( strval( $option ) ); ?>"
									   class="simpay-field simpay-field-checkbox"
									   type="checkbox"
									   value="<?php echo trim( strval( $option ) ); ?>"
									<?php checked( $this->value, 'yes', true ); ?>
									<?php echo $this->attributes; ?>
								/><?php echo esc_attr( $name ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php

		} else {

			?>
			<label for="<?php echo esc_attr( $this->id ); ?>" class="simpay-field-bool" <?php echo $this->style ? 'style="' . $this->style . '"' : ''; ?>>
				<?php if ( ! empty( $this->title ) ) : ?>
					<span class="screen-reader-text"><?php echo $this->title; ?></span>
				<?php endif; ?>
				<input name="<?php echo $this->name; ?>"
					   type="checkbox"
					   id="<?php echo $this->id; ?>"
					   class="simpay-field simpay-field-checkbox <?php echo $this->class; ?>"
					   value="yes"
					<?php checked( $this->value, 'yes', true ); ?>
					<?php echo $this->attributes; ?>/><?php echo( ! empty( $this->text ) ? $this->text : esc_html__( 'Yes', 'stripe' ) ); ?>
			</label>
			<?php

			if ( ! empty( $this->description ) ) {
				echo '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
			}
		}

	}

}
