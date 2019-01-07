<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'JS_topic_Manager_Subject_List_Page' ) ) {

	class JS_topic_Manager_Subject_List_Page extends WP_List_Table {

		public function __construct() {
			parent::__construct( [
				'singular' => __( 'Subject', 'js_topic_manager' ), 
				'plural'   => __( 'Subjects', 'js_topic_manager' ),
				'ajax'     => false
			] );
		}

		/**
		 * Retrieve subject data from the database
		 *
		 * @param int $per_page
		 * @param int $page_number
		 *
		 * @return mixed
		 */
		public static function get_subjects( $per_page = 20, $page_number = 1 ) {
			global $wpdb;

			$sql = "SELECT subject.*, subject_level.subject_level_id, subject_level.level_id FROM {$wpdb->prefix}js_subjects AS subject "
				. "JOIN {$wpdb->prefix}js_subject_levels AS subject_level ON subject.subject_id = subject_level.subject_id "
				. "WHERE subject.is_active = 1";
			
			if ( ! empty( $_REQUEST['level'] ) ) {
				$sql .= " AND subject_level.level_id = " . absint( $_REQUEST['level'] ) . " ";
			} else {
				$default_level = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "
					. "WHERE is_active = 1 ORDER BY level_order ASC LIMIT 1" );
				$sql .= " AND subject_level.level_id = " . $default_level->level_id . " ";
			}

			if ( ! empty( $_REQUEST['orderby'] ) ) {
				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
			} else {
				// Default order
				$sql .= ' ORDER BY subject_name ASC';
			}

			$sql .= " LIMIT $per_page";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

			$result = $wpdb->get_results( $sql, 'ARRAY_A' );

			return $result;
		}

		/**
		 * Delete a subject record.
		 *
		 * @param int $id subject_id
		 */
		public static function delete_subject( $id ) {
			global $wpdb;

			$wpdb->update(
				"{$wpdb->prefix}js_subjects",
				array(
					'is_active' => false
				),
				array(
					'subject_id' => $id
				)
			);

			$wpdb->delete(
				"{$wpdb->prefix}js_subject_levels",
				array(
					'subject_id' => $id
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

			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}js_subjects WHERE is_active = 1";

			if ( ! empty( $_REQUEST['level'] ) ) {
				$sql .= " AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels "
					. "WHERE level_id = " . absint( $_REQUEST['level'] ) . ") ";
			} else {
				$default_level = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "
					. "WHERE is_active = 1 ORDER BY level_order ASC LIMIT 1" );
				$sql .= " AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels "
					. "WHERE level_id = " . $default_level->level_id . ") ";
			}

			return $wpdb->get_var( $sql );
		}

		/**
		 * Text displayed when no subject data is available 
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
				case 'subject_name':
				case 'description':
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
				'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['subject_id']
			);
		}

		/**
		 * Method for name column
		 *
		 * @param array $item an array of DB data
		 *
		 * @return string
		 */
		public function column_subject_name( $item ) {

			$delete_nonce = wp_create_nonce( 'js_delete_subject' );

			$title = '<strong>' . $item['subject_name'] . '</strong>';

			$actions = [
				'edit' 		=> sprintf( '<a href="?page=%s&action=%s&subject_id=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['subject_id'] ) ),
				'delete'	=> sprintf( '<a href="?page=%s&action=%s&subject_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['subject_id'] ), $delete_nonce )
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
				'cb'      				=> '<input type="checkbox" />',
				'subject_name'    		=> __( 'Subject Name' ),
				'description' 			=> __( 'Description' ),
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
				'subject_name' => array( 'subject_name', true )
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
		 * Show the time views, date filters and the search box
		 * @since 0.0.1
		 */
		public function advanced_filters() {
			global $wpdb;

			$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1 ORDER BY level_order ASC";
			$levels = $wpdb->get_results( $sql );
			$views = array();

			if ( !empty( $_GET['level'] ) ) {
				$selected_level = sanitize_text_field( $_GET['level'] );
			} else {
				$selected_level = $levels[0]->level_id;
			}

			if ( !empty($levels) ) {
				foreach ( $levels as $level ) {
					$views[$level->level_id] =
						sprintf( '<option value="%s"%s>%s</option>', 
								 $level->level_id, 
								 $selected_level === $level->level_id ? ' selected' : '', 
								 __( $level->level_name, 'js_topic_manager' ) );
				}
			}
			?>

			<p class="filter-title">Education Level:</p>
			<select name="subject_level">
				<?php echo join( '', $views ); ?>
			</select>

			<?php
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

			$this->items = self::get_subjects( 20, $current_page );

			$this->set_pagination_args( [
				'total_items' => $total_items,
				'per_page'    => 20
			] );
		}

		public function process_bulk_action() {
			$redirect = remove_query_arg(array('action', 'subject_id', '_wpnonce'));

			//Detect when a bulk action is being triggered
			if ( 'delete' === $this->current_action() ) {

				// In our file that handles the request, verify the nonce.
				$nonce = esc_attr( $_REQUEST['_wpnonce'] );

				if ( ! wp_verify_nonce( $nonce, 'js_delete_subject' ) ) {
					die( 'Error' );
				}
				else {
					self::delete_subject( absint( $_GET['subject_id'] ) );
			        
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
					self::delete_subject( $id );
				}

				wp_redirect( $redirect );
				exit;
			}
		}

	}
}

if ( !class_exists( 'JS_topic_Manager_Subject_Page' ) ) {

	class JS_topic_Manager_Subject_Page {

		public function __construct() {
			if ( !empty($_POST) && !empty($_POST['subject_id']) && $_POST['request'] == 'edit_subject_request' ) {
				self::update_subject();
			} else if ( !empty($_POST) && $_POST['request'] == 'add_subject_request' ) {
				self::add_subject();
			}
		}

		private function add_subject() {
			global $wpdb;

			if ( isset($_POST['subject_name']) && !empty($_POST['subject_name']) ) {
				$result = $wpdb->insert(
					"{$wpdb->prefix}js_subjects",
					array(
						'subject_name'			=> $_POST['subject_name'],
						'description'			=> $_POST['description'],
						'is_active'				=> true,
						'bright_cove_video_tag'	=> $_POST['bright_cove_video_tag'],
						'date_created'			=> date( 'Y-m-d H:i:s', time() ),
						'last_updated'			=> date( 'Y-m-d H:i:s', time() )
					)
				);

				if ( !is_wp_error($result) && $result !== false ) {
					$subject_id = $wpdb->insert_id;
					$level_ids = $_POST['level_id'];
					foreach ($level_ids as $level_id) {
						$wpdb->insert(
							"{$wpdb->prefix}js_subject_levels",
							array(
								'subject_id'	=> $subject_id,
								'level_id'		=> $level_id
							)
						);
					}
				}
			}

			wp_redirect( remove_query_arg( array('action') ) );
		}

		private function update_subject() {
			global $wpdb;

			if ( isset($_POST['subject_name']) && !empty($_POST['subject_name']) ) {
				$result = $wpdb->update(
					"{$wpdb->prefix}js_subjects",
					array(
						'subject_name'			=> $_POST['subject_name'],
						'description'			=> $_POST['description'],
						'bright_cove_video_tag'	=> $_POST['bright_cove_video_tag'],
						'last_updated'			=> date( 'Y-m-d H:i:s', time() )
					),
					array(
						'subject_id' => absint( $_POST['subject_id'] )
					)
				);

				if ( !is_wp_error($result) && $result !== false ) {
					$res = $wpdb->delete(
						"{$wpdb->prefix}js_subject_levels",
						array(
							'subject_id' => absint( $_POST['subject_id'] )
						) 
					);
					if ( !is_wp_error($res) && $res !== false ) {
						$level_ids = $_POST['level_id'];
						foreach ($level_ids as $level_id) {
							$wpdb->insert(
								"{$wpdb->prefix}js_subject_levels",
								array(
									'subject_id'	=> $_POST['subject_id'],
									'level_id'		=> $level_id
								)
							);
						}
					}
				}
			}

			wp_redirect( remove_query_arg( array('action', 'subject_id') ) );
		}

		private function get_subject() {
			global $wpdb;

			$subject = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_subjects "
				. "WHERE subject_id = " . absint( $_REQUEST['subject_id'] ) );
			if ( !is_wp_error($subject) && $subject !== false ) {
				$sql = "SELECT * FROM {$wpdb->prefix}js_subject_levels WHERE subject_id = " . $subject->subject_id;
				$result = $wpdb->get_results( $sql );
				if ( !is_wp_error($result) && $result !== false ) {
					foreach ($result as $res) {
						$level_ids[] = absint($res->level_id);
					}
				} else {
					$level_ids = array();
				}
				return array(
					'subject_id'			=> $subject->subject_id,
					'level_ids'				=> $level_ids,
					'subject_name'			=> $subject->subject_name,
					'description'			=> $subject->description,
					'is_active'				=> $subject->is_active,
					'bright_cove_video_tag'	=> $subject->bright_cove_video_tag,
					'date_created'			=> $subject->date_created,
					'last_updated'			=> $subject->last_updated
				);
			} else {
				return array();
			}

			return $result;
		}

		private function get_educational_levels() {
			global $wpdb;

			$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1 ORDER BY level_order ASC";
			$result = $wpdb->get_results( $sql );

			return $result;
		}

		public function subject_form() {
			$parent_url = remove_query_arg( array('action', 'subject_id', '_wpnonce') );
			if ($_REQUEST['action'] == 'edit') {
				$subject = self::get_subject();
			} else {
				$subject = array(
					'level_ids'	=> array()
				);
			}
			$request_type = $_REQUEST['action'] . '_subject_request';
			$levels = self::get_educational_levels();
			if ( $_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit' ) { ?>
				<form method="post">
					<input type="hidden" name="request" value="<?php echo $request_type; ?>">
					<input type="hidden" name="subject_id" value="<?php echo $subject['subject_id']; ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>Subject Name</th>
								<td><input type="text" name="subject_name" value="<?php echo $subject['subject_name']; ?>"></td>
							</tr>
							<tr>
								<th>Description</th>
								<td><textarea type="text" name="description"><?php echo esc_textarea($subject['description']); ?></textarea></td>
							</tr>
							<tr>
								<th>Educational Level</th>
								<td>
									<select class="form-control" name="level_id[]" multiple="multiple" style="min-height: 300px;">
									<?php foreach ($levels as $level) { ?>
										<option value="<?php echo $level->level_id;?>" <?php echo in_array($level->level_id, $subject['level_ids']) ? 'selected' : ''; ?>><?php echo $level->level_name;?></option>
									<?php }?>
									</select>
								</td>
							</tr>
							<tr>
								<th>BrightCove Tag</th>
								<td><input type="text" name="bright_cove_video_tag" value="<?php echo esc_textarea($subject['bright_cove_video_tag']); ?>"></td>
							</tr>
						</tbody>
					</table>
					<?php submit_button(); ?>
				</form>
			<?php
			} else {
			?>
				<p>Subject not found. Go back to <b><a href="<?php echo $parent_url; ?>">subjects list</a></b>.</p>
			<?php
			}
		}

	}

}

?>