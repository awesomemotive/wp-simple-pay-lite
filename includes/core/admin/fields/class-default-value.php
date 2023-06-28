<?php
/**
 * Admin setting fields: Default value
 *
 * @package SimplePay\Core\Admin\Fields
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Admin\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default value
 *
 * @since 4.6.7
 */
class Default_Value extends Standard {

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @param array $field Field data.
	 */
	public function __construct( $field ) {
		$this->subtype   = 'text';
		$this->multiline = false;

		$field['class'] = isset( $field['class'] )
			? array_merge( $field['class'], array( 'simpay-field-text' ) )
			: array( 'simpay-field-text' );

		if ( isset( $field['label'] ) ) {
			$this->label = $field['label'];
		}

		parent::__construct( $field );
	}

	/**
	 * Outputs the field markup.
	 *
	 * @since 4.6.7
	 */
	public function html() {
		$tags = $this->get_smart_tags();

		?>
		<th style="padding-bottom: 0;">
			<div style="display: flex; align-items: center; justify-content: space-between; max-width: 75%;">
				<label for="<?php echo esc_attr( $this->id ); ?>">
					<?php echo esc_html( $this->label ); ?>
				</label>
				<button
					type="button"
					class="button button-secondary button-link smart-tags-toggle"
					style="margin-left: 10px; font-weight: normal; text-decoration: none; display: flex; align-items: center;"
					data-id="<?php echo esc_attr( $this->id ); ?>"
				>
					<span
						class="dashicons dashicons-admin-generic"
						style="margin-right: 4px;"
					></span>

					<?php esc_html_e( 'Dynamic Value', 'stripe' ); ?>
				</button>
			</div>
		</th>
		<td>
			<div
				data-smart-tag-list="<?php echo esc_attr( $this->id ); ?>"
				class="smart-tags-list"
				style="margin-bottom: 10px; max-height: 140px; overflow-y: auto; max-width: 75%; border: 1px solid #ccd0d4; background: #fff; display: none;"
			>
				<?php foreach ( $tags as $tag => $label ) : ?>
					<button
						value="<?php echo esc_attr( $tag ); ?>"
						class="button"
						style="display: block; background-color: transparent; border: 0; border-bottom: 1px solid #ccd0d4; width: 100%; text-align: left; border-radius: 0;"
					>
						<?php echo esc_html( $label ); ?>
					</button>
				<?php endforeach; ?>
			</div>

			<div data-smart-tag-receiver="<?php echo esc_attr( $this->id ); ?>">
				<?php echo parent::html(); ?>
			</div>
		</td>
		<?php
	}

	/**
	 * Returns a list of dynamic value tags that determine a default value.
	 *
	 * @since 4.6.7
	 *
	 * @return array<string, string>
	 */
	private function get_smart_tags() {
		return array(
			'{query var=""}'     => __( 'Query String Variable', 'stripe' ),
			'{form-id}'          => __( 'Form ID', 'stripe' ),
			'{form-title}'       => __( 'Form Title', 'stripe' ),
			'{form-description}' => __( 'Form Description', 'stripe' ),
			'{page-id}'          => __( 'Embedded Post/Page ID', 'stripe' ),
			'{page-title}'       => __( 'Embedded Post/Page Title', 'stripe' ),
			'{page-url}'         => __( 'Embedded Post/Page URL', 'stripe' ),
			'{user-id}'          => __( 'User ID', 'stripe' ),
			'{user-email}'       => __( 'User Email', 'stripe' ),
			'{user-first-name}'  => __( 'User First Name', 'stripe' ),
			'{user-last-name}'   => __( 'User Last Name', 'stripe' ),
			'{user-ip}'          => __( 'User IP', 'stripe' ),
		);
	}

}
