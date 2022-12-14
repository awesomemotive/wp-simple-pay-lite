<?php
/**
 * Admin: "Form Templates" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.5
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * FormTemplatesPage class.
 *
 * @since 4.5.6
 */
class FormTemplatesPage extends AbstractAdminPage implements AdminSecondaryPageInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Template explorer.
	 *
	 * @since 4.6.5
	 * @var \SimplePay\Core\Admin\FormBuilder\TemplateExplorer
	 */
	private $template_explorer;

	/**
	 * FormTemplatesPage.
	 *
	 * @since 4.6.5
	 *
	 * @param \SimplePay\Core\Admin\FormBuilder\TemplateExplorer $template_explorer Template explorer.
	 */
	public function __construct( $template_explorer ) {
		$this->template_explorer = $template_explorer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 3;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		$has_new = __unstable_simpay_has_new_form_templates();

		return sprintf(
			'%s %s',
			__( 'Form Templates', 'stripe' ),
			$has_new
				? sprintf(
					'<span class="simpay-menu-new">%s</span>',
					esc_html__( 'New!', 'stripe' )
				)
				: ''
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Form Templates', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay_form_templates';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		?>

		<div class="wrap">
			<h1 class="wp-heading-inline">
				<?php esc_html_e( 'Form Templates', 'stripe' ); ?>
			</h1>
			<hr class="wp-header-end">

			<?php $this->template_explorer->render(); ?>
		</div>

		<?php
	}

}
