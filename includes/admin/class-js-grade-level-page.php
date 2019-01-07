<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'JS_topic_Manager_Grade_Level_List_Page' ) ) {

	class JS_topic_Manager_Grade_Level_List_Page extends WP_List_Table {

		public function __construct() {
			parent::__construct( [
				'singular' => __( 'Grade Level', 'js_topic_manager' ), 
				'plural'   => __( 'Grade Levels', 'js_topic_manager' ),
				'ajax'     => false
			] );
		}

		/**
		 * Retrieve grade level data from the database
		 *
		 * @param int $per_page
		 * @param int $page_number
		 *
		 * @return mixed
		 */
		public static function get_grade_levels( $per_page = 20, $page_number = 1 ) {

			global $wpdb;

			$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1";
			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			} else {
				// Default order
				$sql .= ' ORDER BY level_order ASC';
			}

			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			$result = $wpdb->get_results( $sql, 'ARRAY_A' );

			return $result;
		}

		/**
		 * Delete a grade level record.
		 *
		 * @param int $id Level ID
		 */
		public static function delete_grade_level( $id ) {
			global $wpdb;

			$wpdb->update(
				"{$wpdb->prefix}js_grade_level",
				array(
					'is_active' => false
				),
				array(
					'level_id' => $id
				)
			);
		}

		/**
		 * Returns the count of records in the database.
		 *
		 * @return null|string
		 */
		public static function record_count() {
			global $wpdb;

			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1";

			return $wpdb->get_var( $sql );
		}

		/**
		 * Text displayed when no grade level data is available 
		 */
		public function no_items() {
			_e( 'No items avaliable.', 'js_topic_manager' );
		}

		/**
		 * Render a column when no column specific method exist.
		 *
		 * @param array $item
		 * @param string $column_name
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'level_order':
				case 'level_name':
				case 'description':
				case 'group_slug':
				case 'bright_cove_video_tag':
					return $item[ $column_name ];
				default:
					//Show the whole array for troubleshooting purposes
					return print_r( $item, true );
			}
		}

		/**
		 * Render the bulk edit checkbox
		 *
		 * @param array $item
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['level_id']
			);
		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		public function column_level_name( $item ) {

			$delete_nonce = wp_create_nonce( 'js_delete_grade_level' );

			$title = '<strong>' . $item['level_name'] . '</strong>';

			$actions = [
				'edit' 		=> sprintf( '<a href="?page=%s&action=%s&level_id=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['level_id'] ) ),
				'delete'	=> sprintf( '<a href="?page=%s&action=%s&level_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['level_id'] ), $delete_nonce )
			];

			return $title . $this->row_actions( $actions );
		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'cb'					=> '<input type="checkbox" />',
				'level_order'			=> __( 'Order' ),
				'level_name'			=> __( 'Level Name' ),
				'description'			=> __( 'Description' ),
				'group_slug'			=> __( 'Group' ),
				'bright_cove_video_tag'	=> __( 'BrightCove Tag' )
			);
		}

		/**
		 * Columns to make sortable.
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			$sortable_columns = array(
				'level_order' => array( 'level_order', true ),
				'level_name' => array( 'level_name', true )
			);

			return $sortable_columns;
		}

		/**
		 * Returns an associative array containing the bulk action
		 *
		 * @return array
		 */
		public function get_bulk_actions() {
			$actions = [
				'bulk-delete' => 'Delete'
			];

			return $actions;
		}

		/**
		 * Handles data query and filter, sorting, and pagination.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = array(); // No hidden columns
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			// Process bulk action
			$this->process_bulk_action();

			$current_page = $this->get_pagenum();
			$total_items  = self::record_count();

			$this->items = self::get_grade_levels( 20, $current_page );

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => 20
			] );
		}

		public function process_bulk_action() {
			$redirect = remove_query_arg(array('action', 'level_id', '_wpnonce'));

			//Detect when a bulk action is being triggered
			if ( 'delete' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'js_delete_grade_level' ) ) {
					die( 'Error' );
				}
				else {
					self::delete_grade_level( absint( $_GET['level_id'] ) );
			        
			        wp_redirect( $redirect );
					exit;
				}

			}

			// If the delete bulk action is triggered
			if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
			     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
			) {

				$delete_ids = esc_sql( $_POST['bulk-delete'] );

				// loop over the array of record IDs and delete them
				foreach ( $delete_ids as $id ) {
					self::delete_grade_level( $id );
				}

				wp_redirect( $redirect );
				exit;
			}
		}

	}
}

if ( !class_exists( 'JS_topic_Manager_Grade_Level_Page' ) ) {

	class JS_topic_Manager_Grade_Level_Page {

		public function __construct() {
			if ( !empty($_POST) && !empty($_POST['level_id']) && $_POST['request'] == 'edit_level_request' ) {
				self::update_grade_level();
			} else if ( !empty($_POST) && $_POST['request'] == 'add_level_request' ) {
				self::add_grade_level();
			}
		}

		private function add_grade_level() {
			global $wpdb;

			$wpdb->insert(
				"{$wpdb->prefix}js_grade_level",
				array(
					'level_name'			=> $_POST['level_name'],
					'description'			=> $_POST['description'],
					'group_slug'			=> $_POST['group_slug'],
					'is_active'				=> true,
					'level_order'			=> absint( $_POST['level_order'] ),
					'bright_cove_video_tag'	=> $_POST['bright_cove_video_tag'],
					'date_created'			=> date( 'Y-m-d H:i:s', time() ),
					'last_updated'			=> date( 'Y-m-d H:i:s', time() )
				)
			);

			wp_redirect( remove_query_arg( array('action') ) );
		}

		private function update_grade_level() {
			global $wpdb;

			$wpdb->update(
				"{$wpdb->prefix}js_grade_level",
				array(
					'level_name'			=> $_POST['level_name'],
					'description'			=> $_POST['description'],
					'group_slug'			=> $_POST['group_slug'],
					'level_order'			=> absint( $_POST['level_order'] ),
					'bright_cove_video_tag'	=> $_POST['bright_cove_video_tag'],
					'last_updated'			=> date( 'Y-m-d H:i:s', time() )
				),
				array(
					'level_id' => absint( $_POST['level_id'] )
				)
			);

			wp_redirect( remove_query_arg( array('action', 'level_id') ) );
		}

		private function get_grade_level() {
			global $wpdb;

			$result;
			if ( ! empty( $_REQUEST['level_id'] ) ) {
				$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1 AND level_id = " . absint( $_REQUEST['level_id'] );
				$result = $wpdb->get_results( $sql );
			}

			return $result;
		}

		public function grade_level_form() {
			$parent_url = remove_query_arg( array('action', 'level_id', '_wpnonce') );
			$grade_levels = self::get_grade_level();
			$request_type = $_REQUEST['action'] . '_level_request';
			if ( $_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit' )  {
				if ( !empty($grade_levels) )
					$grade_level = $grade_levels[0];
			?>
				<form method="post">
					<input type="hidden" name="request" value="<?php echo $request_type; ?>">
					<input type="hidden" name="level_id" value="<?php echo $grade_level->level_id; ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>Level Order #</th>
								<td><input type="number" name="level_order" value="<?php echo $grade_level->level_order; ?>"></td>
							</tr>
							<tr>
								<th>Level Name</th>
								<td><input type="text" name="level_name" value="<?php echo $grade_level->level_name; ?>"></td>
							</tr>
							<tr>
								<th>Group</th>
								<td>
									<p><input type="radio" name="group_slug" value="kindergarten-preparation" <?php checked( $grade_level->group_slug, 'kindergarten-preparation' ); ?> /> Pre-Kindergarten</p>
									<p><input type="radio" name="group_slug" value="kindergarten-to-grade-12" <?php checked( $grade_level->group_slug, 'kindergarten-to-grade-12' ); ?> /> Kindergarten through 12th Grade</p>
									<p><input type="radio" name="group_slug" value="college-preparation" <?php checked( $grade_level->group_slug, 'college-preparation' ); ?> /> College Preparation</p>
								</td>
							</tr>
							<tr>
								<th>Description</th>
								<td><textarea type="text" name="description"><?php echo esc_textarea($grade_level->description); ?></textarea></td>
							</tr>
							<tr>
								<th>BrightCove Tag</th>
								<td><input type="text" name="bright_cove_video_tag" value="<?php echo esc_textarea($grade_level->bright_cove_video_tag); ?>"></td>
							</tr>
						</tbody>
					</table>
					<?php submit_button(); ?>
				</form>
			<?php
			} else {
			?>
				<p>Grade level not found. Go back to <b><a href="<?php echo $parent_url; ?>">grade level list</a></b>.</p>
			<?php
			}
		}

	}

}

?>