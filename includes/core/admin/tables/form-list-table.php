<?php

namespace SimplePay\Core\Admin\Tables;

use SimplePay\Core\Admin\WP_List_Table;

/**
 * Class to extend the WP List Table for outputting what we want for form data
 *
 * Class Form_List_Table
 *
 * @package SimplePay\Admin\Tables
 */
class Form_List_Table extends WP_List_Table {

	public $results = array(); // Search results

	private $preview_form_id = null;

	private $admin_page_url = '';

	/**
	 * Form_List_Table constructor.
	 */
	public function __construct() {

		$this->admin_page_url  = admin_url( 'admin.php?page=simpay' );

		parent::__construct( array(
			'singular' => esc_html__( 'Payment Form', 'stripe' ),
			'plural'   => esc_html__( 'Payment Forms', 'stripe' ),
			'ajax'     => false,
		) );
	}

	/**
	 * Get the different views (links above bulk actions)
	 *
	 * @return array
	 */
	public function get_views() {
		$views = array();

		$current = ( ! empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all' );

		// All link
		$class = ( 'all' === $current ? ' class="current"' : '' );
		//$all_url      = remove_query_arg( 'status' );
		$views['all'] = '<a href="' . esc_url( $this->admin_page_url ) . '" ' . $class . '>' . esc_html__( 'All', 'stripe' ) . ' <span class="count">(' . $this->get_total_forms( array(
				'publish',
				'draft',
			) ) . ')</span></a>';

		// Published Link
		$class              = ( 'published' === $current ? ' class="current"' : '' );
		$published_url      = add_query_arg( 'status', 'published', $this->admin_page_url );
		$views['published'] = '<a href="' . esc_url( $published_url ) . '" ' . $class . '>' . esc_html__( 'Published', 'stripe' ) . ' <span class="count">(' . $this->get_total_forms( 'publish' ) . ')</span></a>';

		// Draft Link
		$class          = ( 'draft' === $current ? ' class="current"' : '' );
		$draft_url      = add_query_arg( 'status', 'draft', $this->admin_page_url );
		$views['draft'] = '<a href="' . esc_url( $draft_url ) . '" ' . $class . '>' . esc_html__( 'Draft', 'stripe' ) . ' <span class="count">(' . $this->get_total_forms( 'draft' ) . ')</span></a>';

		// Trash Link
		$class          = ( 'trash' === $current ? ' class="current"' : '' );
		$trash_url      = add_query_arg( 'status', 'trash', $this->admin_page_url );
		$views['trash'] = '<a href="' . esc_url( $trash_url ) . '" ' . $class . '>' . esc_html__( 'Trash', 'stripe' ) . ' <span class="count">(' . $this->get_total_forms( 'trash' ) . ')</span></a>';

		return $views;
	}

	/**
	 * Get all the forms.
	 *
	 * @param int    $per_page
	 * @param int    $page_number
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_forms( $per_page = -1, $page_number = 1, $status = 'any' ) {

		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'date';
		$order   = isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : 'desc';

		$form_posts = get_posts( array(
			'posts_per_page' => $per_page,
			'offset'         => ( $per_page * ( $page_number - 1 ) ),
			'orderby'        => $orderby,
			'order'          => $order,
			'post_type'      => 'simple-pay',
			'post_status'    => $status,
		) );

		$forms = array();

		$i = 0;

		// WP returns the posts as Post objects but we need an array, so we cycle through here and only store the information we want.
		if ( ! empty( $form_posts ) ) {
			foreach ( $form_posts as $form ) {

				// Skip showing the form used for previews
				if ( $form->ID == $this->preview_form_id ) {
					continue;
				}

				$forms[ $i ]['id']    = $form->ID;
				$forms[ $i ]['title'] = $form->post_title;
				$i++;
			}
		}

		return $forms;
	}

	/**
	 * Get search results
	 *
	 * @param $search
	 *
	 * @return array
	 */
	public function get_search_results( $search ) {

		global $wpdb;

		// Trim Search Term
		$search = trim( $search );

		// Find all the search results by searching only the simple-pay post type
		$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "posts WHERE `post_title` LIKE '%%%s%%' AND `post_type` = 'simple-pay'", $search ), ARRAY_A );

		$this->results = array();
		$i             = 0;

		// $data is not in the format we need so we reorganize it here
		if ( ! empty( $data ) ) {
			foreach ( $data as $form ) {
				$this->results[ $i ]['id']    = $form['ID'];
				$this->results[ $i ]['title'] = $form['post_title'];

				$i++;
			}
		}

		return $this->results;
	}

	/**
	 * Return total number of search result items
	 *
	 * @return int
	 */
	public function get_total_search_results() {
		return count( $this->results );
	}

	/**
	 * Get the total count of all simple-pay CPT posts
	 *
	 * @param string $status
	 *
	 * @return int
	 */
	public function get_total_forms( $status = 'any' ) {
		return count( get_posts( array(
			'posts_per_page' => -1,
			'post_type'      => 'simple-pay',
			'post_status'    => $status,
		) ) );
	}

	/**
	 * Construct and return the delete link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_trash_link( $id ) {

		$delete_nonce = wp_create_nonce( 'simpay_trash_form' );

		$url = add_query_arg( array(
			'action'      => 'trash',
			'simpay_form' => absint( $id ),
			'_wpnonce'    => $delete_nonce,
		), $this->admin_page_url );

		$link = '<a href="' . $url . '">';
		$link .= esc_html__( 'Trash', 'stripe' );
		$link .= '</a>';

		return $link;
	}

	/**
	 * Construct and return edit link
	 *
	 * @param        $id
	 * @param string $link_text
	 *
	 * @return string
	 */
	public function get_edit_link( $id, $link_text = '' ) {

		if ( empty( $link_text ) ) {
			$link_text = __( 'Edit', 'stripe' );
		}

		$url = add_query_arg( array(
			'action'  => 'edit',
			'form_id' => absint( $id ),
		), $this->admin_page_url );

		return '<a href="' . esc_attr( $url ) . '">' . esc_html( $link_text ) . '</a>';
	}

	/**
	 * Construct and return preview link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_preview_link( $id ) {

		$url = add_query_arg( 'simpay-preview', $id, site_url() );

		return '<a href="' . esc_attr( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Preview', 'stripe' ) . '</a>';
	}

	/**
	 * Get the link to untrash a post
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_untrash_link( $id ) {

		$nonce = wp_create_nonce( 'simpay_untrash_form' );

		$url = add_query_arg( array(
			'action'      => 'untrash',
			'simpay_form' => absint( $id ),
			'_wpnonce'    => $nonce,
		) );

		$link = '<a href="' . esc_url( $url ) . '">';
		$link .= esc_html__( 'Restore', 'stripe' );
		$link .= '</a>';

		return $link;
	}

	/**
	 * Get the Permanently Delete link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_permanent_delete_link( $id ) {

		$nonce = wp_create_nonce( 'simpay_permanent_delete_form' );

		$url = add_query_arg( array(
			'action'      => 'delete',
			'simpay_form' => absint( $id ),
			'_wpnonce'    => $nonce,
		) );

		$link = '<a href="' . esc_url( $url ) . '" class="submitdelete">';
		$link .= esc_html__( 'Permanently Delete', 'stripe' );
		$link .= '</a>';

		return $link;
	}

	/**
	 * Get the Duplicate link
	 *
	 * @param $id
	 *
	 * @return string
	 */
	public function get_duplicate_link( $id ) {
		$nonce = wp_create_nonce( 'simpay_duplicate_form' );

		$url = add_query_arg( array(
			'action'      => 'duplicate',
			'simpay_form' => absint( $id ),
			'_wpnonce'    => $nonce,
		) );

		$link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Duplicate', 'stripe' ) . '</a>';

		return $link;
	}

	/**
	 * Duplicate a form
	 *
	 * @param $id
	 */
	public function duplicate_form( $id ) {

		$id = absint( $id );

		if ( empty( $id ) ) {
			return;
		}


		$post = get_post( $id );

		// Check if we actually got a post back
		if ( false !== $post && null !== $post && 0 !== $post ) {

			// Get the post meta
			$post_meta = get_post_meta( $post->ID );

			$new_title = $post->post_title;

			// Check for a counter
			preg_match_all( '/(\\(([0-9])*\\))$/mi', $new_title, $counter );

			// If count does not exist, set count to 1.
			if ( empty( $counter[0] ) ) {

				// Set initial count.
				$count = 1;
			} else {

				// Set counter to what we found + 1
				$count = absint( $counter[2][0] ) + 1;

				// Remove counter
				$new_title = preg_replace( '/(\\(([0-9])*\\))$/mi', null, $post->post_title );
			}

			// Trim the title
			$new_title = trim( $new_title );

			// Update new title with our counter
			$new_title = $new_title . " ($count)";

			// Loop through until we find a valid title counter
			while ( ! $this->duplicated_title( $new_title ) ) {
				$count++;
				$new_title = $post->post_title . " ($count)";
			}

			// Insert the new post using the original post values plus some modifications
			$new_post = wp_insert_post( array(
				'post_title'  => $new_title,
				'post_author' => wp_get_current_user()->ID,
				'post_type'   => $post->post_type,
				'post_status' => $post->post_status,
			) );

			// If the new post did not get inserted then exit now
			if ( ! $new_post ) {
				return;
			}

			// Get the post for our newly created post since we only have the ID so far
			$new_post = get_post( $new_post );

			// Loop through each post meta option and add it to the new post
			if ( is_array( $post_meta ) ) {
				foreach ( $post_meta as $k => $v ) {

					$v = maybe_unserialize( $v );

					if ( is_array( $v ) ) {

						if ( '_custom_fields' === $k ) {
							$custom_fields = $this->update_custom_fields( maybe_unserialize( $v ), $new_post->ID );
							update_post_meta( $new_post->ID, $k, $custom_fields );
							continue;
						}

						foreach ( $v as $k2 => $v2 ) {
							update_post_meta( $new_post->ID, $k, maybe_unserialize( $v2 ) );
						}
					} else {
						update_post_meta( $new_post->ID, $k, $v );
					}
				}
			}

			// Show an admin message
			$this->display_admin_message( esc_html__( 'Form duplicated.', 'stripe' ) );
		} else {

			// Show an admin message about the error
			$this->display_admin_message( esc_html__( 'An error occurred while trying to duplicate. Please try again.', 'stripe' ), 'error' );
		}
	}

	/**
	 * Update the custom field IDs to the new form ID
	 *
	 * @param $fields
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function update_custom_fields( $fields, $post_id ) {

		$return_array = array();

		foreach ( $fields as $k => $v ) {

			$v = maybe_unserialize( $v );

			foreach ( $v as $k2 => $v2 ) {

				$v2 = maybe_unserialize( $v2 );

				foreach ( $v2 as $k3 => $v3 ) {


					if ( 'payment_button' === $k2 ) {
						$v3['id'] = 'simpay_' . $post_id . '_' . $k2;
					} else {
						$v3['id'] = 'simpay_' . $post_id . '_' . $k2 . '_' . $v3['uid'];
					}


					$return_array[ $k ][ $k2 ][ $k3 ] = $v3;
				}
			}
		}

		return $return_array[0];

	}

	/**
	 * Check for duplicated titles
	 *
	 * @param $title
	 *
	 * @return bool
	 */
	public function duplicated_title( $title ) {
		$forms = $this->get_forms();
		foreach ( $forms as $k => $v ) {

			$form = get_post( $v['id'] );

			if ( strtolower( $form->post_title ) === strtolower( $title ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Restore form
	 *
	 * @param $id
	 */
	public function untrash_form( $id ) {
		wp_untrash_post( $id );

		$this->display_admin_message( __( 'Form restored from the Trash.', 'stripe' ) );
	}

	/**
	 * Bulk restore
	 *
	 * @param $ids
	 */
	public function bulk_untrash_forms( $ids ) {

		$total = count( $ids );

		if ( ! empty( $ids ) ) {
			// loop over the array of record IDs and delete them
			foreach ( $ids as $id ) {
				wp_untrash_post( $id );
			}
		}

		$this->display_admin_message( sprintf( _n( '%d form restored from the Trash.', '%d forms restored from the Trash.', $total, 'stripe' ), $total ) );
	}

	/**
	 * Trash the form
	 *
	 * @param $id
	 */
	public function trash_form( $id ) {
		wp_trash_post( $id );

		$this->display_admin_message( __( 'Form moved to the Trash.', 'stripe' ) );
	}

	/**
	 * Bulk Trash
	 *
	 * @param $ids
	 */
	public function bulk_trash_forms( $ids ) {

		$total = count( $ids );

		if ( ! empty( $ids ) ) {
			// loop over the array of record IDs and trash them
			foreach ( $ids as $id ) {
				wp_trash_post( $id );
			}
		}

		$this->display_admin_message( sprintf( _n( '%d form moved to the Trash.', '%d forms moved to the Trash.', $total, 'stripe' ), $total ) );
	}

	/**
	 * Permanently delete the post
	 *
	 * @param $id
	 */
	public function permanently_delete_form( $id ) {
		wp_delete_post( $id );

		$this->display_admin_message( __( 'Form permanently deleted.', 'stripe' ) );
	}

	/**
	 * Bulk permanently delete
	 *
	 * @param $ids
	 */
	public function bulk_permanenetly_delete( $ids ) {

		$total = count( $ids );

		if ( ! empty( $ids ) ) {
			// loop over the array of record IDs and delete them
			foreach ( $ids as $id ) {
				wp_delete_post( $id );
			}
		}

		$this->display_admin_message( sprintf( _n( '%d form permanently delted.', '%d forms permanently deleted.', $total, 'stripe' ), $total ) );
	}

	/**
	 * Empty the trash
	 */
	public function empty_trash() {

		$posts = get_posts( array(
			'post_type'   => 'simple-pay',
			'post_status' => 'trash',
		) );

		$total = count( $posts );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				wp_delete_post( $post->ID );
			}
		}

		$this->display_admin_message( sprintf( _n( '%d form permanently deleted.', '%d forms permanently deleted.', $total, 'stripe' ), $total ) );
	}

	/**
	 * When no forms are found
	 */
	public function no_items() {
		esc_html_e( 'No forms found.', 'stripe' );
	}

	/**
	 * Specific output for the ID column
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_title( $item ) {

		$title = ! empty( $item['title'] ) ? $item['title'] : __( '(no title)', 'stripe' );

		$form   = get_post( $item['id'] );
		$status = '';

		if ( 'draft' === $form->post_status ) {
			$status = ' — <span class="post-state">' . esc_html__( 'Draft', 'stripe' ) . '</span>';
		}

		$title = '<strong>' . $this->get_edit_link( $item['id'], $title ) . $status . '</strong>';

		if ( isset( $_REQUEST['status'] ) && 'trash' === $_REQUEST['status'] ) {

			$actions = array(
				'untrash' => $this->get_untrash_link( $item['id'] ),
				'delete'  => $this->get_permanent_delete_link( $item['id'] ),
			);

		} else {

			$actions = array(
				'edit'         => $this->get_edit_link( $item['id'] ),
				'preview_link' => $this->get_preview_link( $item['id'] ),
				'duplicate'    => $this->get_duplicate_link( $item['id'] ),
				'trash'        => $this->get_trash_link( $item['id'] ),
			);
		}

		return $title . $this->row_actions( $actions );
	}

	/**
	 * Nav table (to show search field)
	 *
	 * @param string $which
	 */
	public function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			?>

			<?php
			$this->search_box( __( 'Search', 'stripe' ), 'simpay-search' );

			$value = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $value ); ?>" />

			<?php
		}

		parent::display_tablenav( $which );
	}

	/**
	 * Show the extra navigation
	 *
	 * @param string $which
	 */
	public function extra_tablenav( $which ) {

		if ( isset( $_REQUEST['status'] ) && 'trash' == $_REQUEST['status'] ) {
			?>

			<div class="alignleft actions" style="overflow: visible;">
				<input type="submit" name="empty_trash" id="delete_all" value="<?php esc_attr_e( 'Empty Trash', 'stripe' ); ?>" class="button apply" />
			</div>

			<?php
		}
	}

	/**
	 * Shortcode column output
	 *
	 * @param $item
	 */
	public function column_shortcode( $item ) {
		?>

		<div id="simpay-get-shortcode">
			<?php simpay_print_shortcode_tip( $item['id'] ); ?>
		</div>
		<?php
	}

	/**
	 * Last modified date output
	 *
	 * @param $item
	 *
	 * @return string
	 */
	public function column_date( $item ) {

		$modified = '<abbr title="' . get_the_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $item['id'] ) . '">' . get_the_date( '', $item['id'] ) . '</abbr>';

		return $this->get_readable_post_status( $item['id'] ) . '<br>' . $modified;
	}

	/**
	 * Get the more friendly output of the post status i.e. instead of "Publish" it will say "Published"
	 *
	 * @param $post_id
	 *
	 * @return string|void
	 */
	public function get_readable_post_status( $post_id ) {

		$status = get_post_status( get_post( $post_id ) );

		switch ( $status ) {
			case 'draft':
				return __( 'Draft', 'stripe' );
			case 'publish':
				return __( 'Published', 'stripe' );
			default:
				return '';
		}
	}

	/**
	 * Output a column when no column specific method exist.
	 *
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'shortcode':
				return $item[ $column_name ];
			default:
				return '';
		}
	}

	/**
	 * Output the bulk edit checkbox
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="simpay_bulk[]" value="%s" />', esc_attr( $item['id'] ) );
	}

	/**
	 * Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'title'     => __( 'Title', 'stripe' ),
			'shortcode' => __( 'Shortcode', 'stripe' ),
			'date'      => __( 'Date', 'stripe' ),
		);

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'title' => array( 'title', true ),
			'date'  => array( 'date', true ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {

		if ( isset( $_REQUEST['status'] ) && 'trash' === $_REQUEST['status'] ) {

			$actions = array(
				'bulk_untrash' => esc_html__( 'Restore', 'stripe' ),
				'bulk_delete'  => esc_html__( 'Permanently Delete', 'stripe' ),
			);

		} else {

			$actions = array(
				'bulk_trash' => esc_html__( 'Move to Trash', 'stripe' ),
			);
		}

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'forms_per_page', 20 );
		$current_page = $this->get_pagenum();

		$status = ( isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all' );

		switch ( $status ) {
			case 'published':
				$status = 'publish';
				break;
			case 'draft':
			case 'trash':
				break;
			default:
				$status = 'any';
		}

		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
			// do search and get items

			$this->items = $this->get_search_results( sanitize_text_field( $_REQUEST['s'] ) );

			$this->set_pagination_args( array(
				'total_items' => $this->get_total_search_results(), //WE have to calculate the total number of items
				'per_page'    => $per_page  //WE have to determine how many items to show on a page
			) );

		} else {

			// no search
			$this->set_pagination_args( array(
				'total_items' => $this->get_total_forms( $status ), //WE have to calculate the total number of items
				'per_page'    => $per_page  //WE have to determine how many items to show on a page
			) );

			$this->items = $this->get_forms( $per_page, $current_page, $status );
		}

	}

	/**
	 * Process bulk action requests
	 */
	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'trash' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'simpay_trash_form' ) ) {
				die( esc_html__( 'Not authorized', 'stripe' ) );
			} else {
				$this->trash_form( absint( $_GET['simpay_form'] ) );
			}

		} elseif ( 'untrash' === $this->current_action() ) {

			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'simpay_untrash_form' ) ) {
				die( esc_html__( 'Not Authorized', 'stripe' ) );
			} else {
				$this->untrash_form( absint( $_GET['simpay_form'] ) );
			}
		} elseif ( 'delete' === $this->current_action() ) {

			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'simpay_permanent_delete_form' ) ) {
				die ( esc_html__( 'Not Authorized', 'stripe' ) );
			} else {
				$this->permanently_delete_form( absint( $_GET['simpay_form'] ) );
			}
		} elseif ( 'duplicate' === $this->current_action() ) {

			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'simpay_duplicate_form' ) ) {
				die ( esc_html__( 'Not Authorized', 'stripe' ) );
			} else {
				$this->duplicate_form( absint( $_GET['simpay_form'] ) );
			}
		}

		if ( isset( $_REQUEST['empty_trash'] ) ) {
			$this->empty_trash();
		}

		/** BULK ACTIONS **/
		// If the delete bulk action is triggered
		if ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk_trash' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk_trash' ) ) {

			$trash_ids = esc_sql( $_REQUEST['simpay_bulk'] );

			$this->bulk_trash_forms( $trash_ids );

		} elseif ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk_untrash' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk_untrash' ) ) {

			$untrash_ids = esc_sql( $_REQUEST['simpay_bulk'] );

			$this->bulk_untrash_forms( $untrash_ids );

		} elseif ( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'bulk_delete' ) || ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] == 'bulk_delete' ) ) {

			$delete_ids = esc_sql( $_REQUEST['simpay_bulk'] );

			$this->bulk_permanenetly_delete( $delete_ids );
		}
	}

	/**
	 * Display admin messages for actions
	 *
	 * @param        $message
	 * @param string $class
	 */
	public function display_admin_message( $message, $class = 'updated' ) {
		?>

		<div class="<?php echo esc_attr( $class ); ?>">
			<p>
				<?php echo esc_html( $message ); ?>
			</p>
		</div>

		<?php
	}

}
