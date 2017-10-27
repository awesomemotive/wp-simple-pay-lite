<?php

namespace SimplePay\Core\Admin\Fields;

use SimplePay\Core\Abstracts\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Select input field.
 *
 * A standard dropdown or a multiselect field.
 *
 * @since 3.0.0
 */
class Select extends Field {

	/**
	 * Enhanced select.
	 *
	 * @access public
	 * @var bool
	 */
	public $enhanced = false;

	/**
	 * Multiselect.
	 *
	 * @var bool
	 */
	public $multiselect = false;


	/**
	 * Page select.
	 *
	 * @var bool
	 */
	public $page_select = false;

	/**
	 * Allow void option.
	 *
	 * @access private
	 * @var bool
	 */
	private $allow_void = false;

	public $class = '';

	/**
	 * Construct.
	 *
	 * @since 3.0.0
	 *
	 * @param array $field
	 */
	public function __construct( $field ) {

		$class = 'simpay-field-select';

		$enhanced = isset( $field['enhanced'] ) ? $field['enhanced'] : '';
		if ( 'enhanced' == $enhanced ) {
			$this->enhanced = true;
			$class          .= ' simpay-field-select-enhanced';
		}

		$multiselect = isset( $field['multiselect'] ) ? $field['multiselect'] : '';
		if ( 'multiselect' == $multiselect ) {
			$this->multiselect = true;
			$class             .= ' simpay-field-multiselect';
		}

		$page_select = isset( $field['page_select'] ) ? $field['page_select'] : '';
		if ( 'page_select' == $page_select ) {
			$this->page_select = true;
		}

		$classes = isset( $field['class'] ) ? $field['class'] : '';
		if ( ! empty( $classes ) ) {
			if ( is_array( $classes ) ) {
				foreach( $classes as $class ) {
					$class .= ' ' . $class;
				}
			}
		}


		if ( isset( $field['default'] ) ) {
			$this->default = $field['default'];
		}

		$this->class = $class;

		$allow_void       = isset( $field['allow_void'] ) ? $field['allow_void'] : '';
		$this->allow_void = 'allow_void' == $allow_void ? true : false;

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 3.0.0
	 */
	public function html() {

		if ( $this->page_select ) {
			$this->page_select_html();

			return;
		}

		if ( $this->multiselect === true && ! is_array( $this->value ) ) {
			$this->value = explode( ',', $this->value );
		}

		if ( $this->default ) {
			if ( empty( $this->value ) || $this->value == '' ) {
				$this->value = $this->default;
			}
		}

		?>
		<select name="<?php echo $this->name; ?><?php if ( $this->multiselect === true ) {
			echo '[]';
		} ?>"
		        id="<?php echo $this->id; ?>"
		        style="<?php echo $this->style; ?>"
		        class="<?php echo $this->class; ?>"
			<?php echo $this->attributes; ?>
			<?php echo ( $this->multiselect === true ) ? ' multiple="multiple"' : ''; ?>>
			<?php

			if ( $this->allow_void === true ) {
				echo '<option value=""' . selected( '', $this->value, false ) . '></option>';
			}

			if ( ! empty( $this->options ) && is_array( $this->options ) ) {
				foreach ( $this->options as $option => $name ) {
					if ( is_array( $this->value ) ) {
						$selected = selected( in_array( $option, $this->value ), true, false );
					} else {
						$selected = selected( $this->value, trim( strval( esc_html( $option ) ) ), false );
					}
					echo '<option value="' . $option . '" ' . $selected . '>' . esc_attr( $name ) . '</option>';
				}
			}

			?>
		</select>
		<?php

		if ( ! empty( $this->description ) ) {
			echo '<p class="description">' . wp_kses_post( $this->description ) . '</p>';
		}

	}

	/**
	 * Make use of the wp_dropdown_pages function provided by WP to output a list of the site's pages in a select box.
	 *
	 * @since 3.0.0
	 */
	public function page_select_html() {

		$args = array(
			'depth'                 => 0,
			'child_of'              => 0,
			'selected'              => absint( $this->value ),
			'echo'                  => 1,
			'name'                  => $this->name,
			'id'                    => $this->id,
			'class'                 => $this->class, // string
			'show_option_none'      => null, // string
			'show_option_no_change' => null, // string
			'option_none_value'     => null, // string
		);

		wp_dropdown_pages( $args );

		echo '<p class="description">' . $this->description . '</p>';
	}

}
