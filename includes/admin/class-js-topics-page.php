<?php

if ( ! defined( 'ABSPATH' ) ) exit;



if ( !class_exists( 'JS_topic_Manager_topic_List_Page' ) ) {



	class JS_topic_Manager_topic_List_Page extends WP_List_Table {



		public function __construct() {

			parent::__construct( [

				'singular' => __( 'Topic', 'js_topic_manager' ), 

				'plural'   => __( 'Topics', 'js_topic_manager' ),

				'ajax'     => false

			] );

		}



		/**

		 * Retrieve topic data from the database

		 *

		 * @param int $per_page

		 * @param int $page_number

		 *

		 * @return mixed

		 */

		public static function get_topics( $per_page = 20, $page_number = 1 ) {



			global $wpdb;



			$sql = "SELECT topic.*, topic_subject.topic_subject_id, topic_subject.subject_id FROM {$wpdb->prefix}js_topics AS topic "

				. "JOIN {$wpdb->prefix}js_topic_subjects AS topic_subject ON topic.topic_id = topic_subject.topic_id "

				. "WHERE topic.is_active = 1";



			if ( ! empty( $_REQUEST['subject'] ) ) {

				$sql .= ' AND topic_subject.subject_id = ' . $_REQUEST['subject'] . " ";

			} else {

				if ( ! empty( $_REQUEST['level'] ) ) {

					$level_id = $_REQUEST['level'];

				} else {

					$default_level = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "

						. "WHERE is_active = 1 ORDER BY level_order ASC LIMIT 1" );

					$level_id = $default_level->level_id;

				}

				if (!empty($level_id)) {

					$default_subject = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_subjects "

						. "WHERE subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $level_id . ") "

						. "AND is_active = 1 ORDER BY subject_name ASC LIMIT 1" );

					if (!empty($default_subject)) {

						$sql .= ' AND topic_subject.subject_id = ' . $default_subject->subject_id . " ";

					}

				}

			}



			if ( ! empty( $_REQUEST['orderby'] ) ) {

				$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );

				$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';

			} else {

				// Default order

				$sql .= ' ORDER BY topic_name ASC';

			}



			$sql .= " LIMIT $per_page";

			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;



			$result = $wpdb->get_results( $sql, 'ARRAY_A' );



			return $result;

		}



		/**

		 * Delete a topic record.

		 *

		 * @param int $id topic_id

		 */

		public static function delete_topic( $id ) {

			global $wpdb;



			$wpdb->update(

				"{$wpdb->prefix}js_topics",

				array(

					'is_active' => false

				),

				array(

					'topic_id' => $id

				)

			);



			$wpdb->delete(

				"{$wpdb->prefix}js_topic_subjects",

				array(

					'topic_id' => $id

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



			$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}js_topics AS topic "

				. "JOIN {$wpdb->prefix}js_topic_subjects AS topic_subject ON topic.topic_id = topic_subject.topic_id "

				. "WHERE topic.is_active = 1";



			if ( ! empty( $_REQUEST['subject'] ) ) {

				$sql .= ' AND topic_subject.subject_id = ' . $_REQUEST['subject'] . " ";

			} else {

				if ( ! empty( $_REQUEST['level'] ) ) {

					$sql .= " AND topic_subject.subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $_REQUEST['level'] . ") ";

				} else {

					$default_level = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "

						. "WHERE is_active = 1 ORDER BY level_order ASC LIMIT 1" );

					$sql .= " AND topic_subject.subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $default_level->level_id . ") ";

				}

			}



			return $wpdb->get_var( $sql );

		}



		/**

		 * Text displayed when no topic data is available 

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

				case 'topic_name':

				case 'bright_cove_video_tag':

				case 'description':

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

				'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['topic_id']

			);

		}



		/**

		 * Method for name column

		 *

		 * @param array $item an array of DB data

		 *

		 * @return string

		 */

		public function column_topic_name( $item ) {



			$delete_nonce = wp_create_nonce( 'js_delete_topic' );



			$title = '<strong>' . $item['topic_name'] . '</strong>';



			$actions = [

				'edit' 		=> sprintf( '<a href="?page=%s&action=%s&topic_id=%s">Edit</a>', esc_attr( $_REQUEST['page'] ), 'edit', absint( $item['topic_id'] ) ),

				'delete'	=> sprintf( '<a href="?page=%s&action=%s&topic_id=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['topic_id'] ), $delete_nonce )

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

				'cb'      					=> '<input type="checkbox" />',

				'topic_name' 				=> __( 'Topic Name' ),

				'bright_cove_video_tag'	=> __( 'BrightCove Video Tag' ),

				'description' 				=> __( 'Description' )

			);

		}



		/**

		 * Columns to make sortable.

		 *

		 * @return array

		 */

		public function get_sortable_columns() {

			$sortable_columns = array(

				'topic_name' => array( 'topic_name', true )

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

		 * Shows grade level and subject dropdown filters

		 * @since 0.0.1

		 */

		public function advanced_filters() {

			global $wpdb;



			$levels = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_grade_level "

				. "WHERE is_active = 1 ORDER BY level_order ASC" );

			$level_options = array();



			if ( !empty( $_GET['level'] ) ) {

				$selected_level = sanitize_text_field( $_GET['level'] );

			} else {

				$selected_level = $levels[0]->level_id;

			}



			$subjects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_subjects "

				. "WHERE is_active = 1 AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels "

					. "WHERE level_id = " . $selected_level . ") "

				. "ORDER BY subject_name ASC" , 'ARRAY_A' );

			$subject_options = array();



			if ( !empty( $_GET['subject'] ) ) {

				$selected_subject = sanitize_text_field( $_GET['subject'] );

			} else {

				$selected_subject = $subjects[0]->subject_id;

			}



			if ( !empty($levels) ) {

				foreach ( $levels as $level ) {

					$level_options[$level->level_id] =

						sprintf( '<option value="%s"%s>%s</option>', 

								 $level->level_id, 

								 $selected_level === $level->level_id ? ' selected' : '', 

								 __( $level->level_name, 'js_topic_manager' ) );

				}

			}



			if ( !empty($subjects) ) {

				foreach ( $subjects as $subject ) {

					$subject_options[$subject['subject_id']] =

						sprintf( '<option value="%s"%s>%s</option>', 

								 $subject['subject_id'], 

								 $selected_subject === $subject['subject_id'] ? ' selected' : '', 

								 __( $subject['subject_name'], 'js_topic_manager' ) );

				}

			}

			?>



			<p class="filter-title">Education Level:</p>

			<select name="topic_level">

				<?php echo join( '', $level_options ); ?>

			</select>



			<p class="filter-title">Subject:</p>

			<select name="topic_subject">

				<?php echo join( '', $subject_options ); ?>

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



			$this->items = self::get_topics( 20, $current_page );



			$this->set_pagination_args( [

				'total_items' => $total_items,

				'per_page'    => 20

			] );

		}



		public function process_bulk_action() {

			$redirect = remove_query_arg(array('action', 'topic_id', '_wpnonce'));



			//Detect when a bulk action is being triggered

			if ( 'delete' === $this->current_action() ) {



				// In our file that handles the request, verify the nonce.

				$nonce = esc_attr( $_REQUEST['_wpnonce'] );



				if ( ! wp_verify_nonce( $nonce, 'js_delete_topic' ) ) {

					die( 'Error' );

				}

				else {

					self::delete_topic( absint( $_GET['topic_id'] ) );

			        

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

					self::delete_topic( $id );

				}



				wp_redirect( $redirect );

				exit;

			}

		}



	}

}



if ( !class_exists( 'JS_topic_Manager_topic_Page' ) ) {



	class JS_topic_Manager_topic_Page {



		public function __construct() {

			if ( !empty($_POST) && !empty($_POST['topic_id']) && $_POST['request'] == 'edit_topic_request' ) {

				self::update_topic();

			} else if ( !empty($_POST) && $_POST['request'] == 'add_topic_request' ) {

				self::add_topic();

			}

		}



		private function add_topic() {

			global $wpdb;



			if (   isset($_POST['topic_name']) && !empty($_POST['topic_name'])

				&& isset($_POST['subject_id']) && !empty($_POST['subject_id']) ) {

				$result = $wpdb->insert(

					"{$wpdb->prefix}js_topics",

					array(

						'topic_name'				=> $_POST['topic_name'],

						'description'				=> $_POST['description'],

						'is_active'					=> true,

						'bright_cove_video_tag'		=> $_POST['bright_cove_video_tag'],

						'date_created'				=> date( 'Y-m-d H:i:s', time() ),

						'last_updated'				=> date( 'Y-m-d H:i:s', time() )

					)

				);



				if ( !is_wp_error($result) && $result !== false ) {

					$topic_id = $wpdb->insert_id;

					$subject_ids = $_POST['subject_id'];

					foreach ($subject_ids as $subject_id) {

						$wpdb->insert(

							"{$wpdb->prefix}js_topic_subjects",

							array(

								'topic_id'		=> $topic_id,

								'subject_id'	=> $subject_id

							)

						);

					}

				}

			}



			wp_redirect( remove_query_arg( array('action') ) );

		}



		private function update_topic() {

			global $wpdb;



			if (   isset($_POST['topic_name']) && !empty($_POST['topic_name'])

				&& isset($_POST['subject_id']) && !empty($_POST['subject_id']) ) {

				$result = $wpdb->update(

					"{$wpdb->prefix}js_topics",

					array(

						'topic_name'				=> $_POST['topic_name'],

						'description'				=> $_POST['description'],

						'bright_cove_video_tag'		=> $_POST['bright_cove_video_tag'],

						'last_updated'				=> date( 'Y-m-d H:i:s', time() )

					),

					array(

						'topic_id' => absint( $_POST['topic_id'] )

					)

				);



				if ( !is_wp_error($result) && $result !== false ) {

					$res = $wpdb->delete(

						"{$wpdb->prefix}js_topic_subjects",

						array(

							'topic_id' => absint( $_POST['topic_id'] )

						) 

					);

					if ( !is_wp_error($res) && $res !== false ) {

						$subject_ids = $_POST['subject_id'];

						foreach ($subject_ids as $subject_id) {

							$wpdb->insert(

								"{$wpdb->prefix}js_topic_subjects",

								array(

									'topic_id'		=> $_POST['topic_id'],

									'subject_id'	=> $subject_id

								)

							);

						}

					}

				}

			}



			wp_redirect( remove_query_arg( array('action', 'topic_id') ) );

		}



		private function get_topic() {

			global $wpdb;



			if ( ! empty( $_REQUEST['topic_id'] ) ) {

				$sql = "SELECT * FROM {$wpdb->prefix}js_topics WHERE is_active = 1 AND topic_id = " . absint( $_REQUEST['topic_id'] );

                $result = $wpdb->get_row( $sql );

                if ( !is_wp_error($result) && $result !== false ) {

                	$subjects = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_topic_subjects WHERE topic_id = " . absint( $_REQUEST['topic_id'] ) );

                	$subject_ids = array();

                	$level_ids = array();

                	if ( !is_wp_error($subjects) && $subjects !== false ) {

            			foreach ($subjects as $subject) {

            				$subject_ids[] = (int)$subject->subject_id;

            				// Get levels where subject belong/s to

            				$levels = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_subject_levels WHERE subject_id = " . absint( $subject->subject_id ) );

            				if ( !is_wp_error($levels) && $levels !== false ) {

            					foreach ($levels as $level) {

            						$level_ids[] = (int)$level->level_id;

            					}

            				}

            			}

                	}

                	$result->subject_id = array_unique($subject_ids);

                	$result->level_id = array_unique($level_ids);

                }

			}



			return $result;

		}



		private function get_topic_filters($subject_ids) {

			global $wpdb;



			$levels_sql = "SELECT * FROM {$wpdb->prefix}js_subject_levels";

			if ( !empty($subject_ids) ) {

				$levels_sql .= " WHERE subject_id IN (";

				foreach ($subject_ids as $key => $subject_id) {

					$levels_sql .= $subject_id;

					if ($key < count($subject_ids) - 1) {

						$levels_sql .= ",";

					}

				}

				$levels_sql .= ")";

			}



			$subjects_sql = "SELECT * FROM {$wpdb->prefix}js_subjects WHERE is_active = 1";

			if ( !empty($level) ) {

				$subjects_sql .= " AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $level->level_id . ")";

			}

			$subjects_sql .= " ORDER BY subject_name ASC";



			$result = array(

				'levels'	=> $wpdb->get_results( $levels_sql ),

				'subjects'	=> $wpdb->get_results( $subjects_sql )

			);



			return $result;

		}



		private function get_educational_levels() {

			global $wpdb;



			$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1 ORDER BY level_order ASC";

			$result = $wpdb->get_results( $sql );



			return $result;

		}



		public function topic_form() {

			$parent_url = remove_query_arg( array('action', 'topic_id', '_wpnonce') );

			$request_type = $_REQUEST['action'] . '_topic_request';

			$topic = self::get_topic();



			if ( $_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit' )  {

				$levels = self::get_educational_levels();



				if ( $_REQUEST['action'] == 'edit' ) {

					$filters = self::get_topic_filters($topic->subject_id);

					$subjects = $filters['subjects'];

				}



				wp_localize_script( 'js-topic-manager-admin-js', 

					'js_topic_manager', 

					array( 

						'ajax_url' 		=> admin_url( 'admin-ajax.php' )

					)

				);

			?>

				<form method="post">

					<input type="hidden" name="request" value="<?php echo $request_type; ?>">

					<input type="hidden" name="topic_id" value="<?php echo $topic->topic_id; ?>">

					<table class="form-table">

						<tbody>

							<tr>

								<th>Topic Name</th>

								<td><input type="text" name="topic_name" value="<?php echo $topic->topic_name; ?>"></td>

							</tr>

							<tr>

								<th>Description</th>

								<td><textarea type="text" name="description"><?php echo esc_textarea($topic->description); ?></textarea></td>

							</tr>

							<tr id="topic-levels">

								<th>Educational Level</th>

								<td>

									<select name="level_id[]" multiple="multiple" style="min-height: 200px;">

									<?php for($i = 0; $i < sizeof($levels);$i++){

										$level = $levels[$i]; ?>

										<option value="<?php echo $level->level_id;?>" <?php echo !empty($topic->level_id) && in_array($level->level_id, $topic->level_id) ? 'selected' : ''; ?>><?php echo $level->level_name;?></option>

									<?php }?>

									</select>

								</td>

							</tr>

							<tr id="topic-subjects">

								<th>Subject</th>

								<td>

									<select name="subject_id[]" multiple="multiple" style="min-height: 200px;">

										<option value="">Select Subject</option>

										<?php for($i = 0; $i < sizeof($subjects);$i++){

											$subject = $subjects[$i]; ?>

											<option value="<?php echo $subject->subject_id;?>" <?php echo !empty($topic->subject_id) && in_array($subject->subject_id, $topic->subject_id) ? 'selected' : ''; ?>><?php echo $subject->subject_name;?></option>

										<?php }?>

									</select>

								</td>

							</tr>

							<tr>

								<th>BrightCove Video Tag</th>

								<td>

									<input type="text" name="bright_cove_video_tag" value="<?php echo $topic->bright_cove_video_tag; ?>">

									<p class="description">Comma separated video tags.</p>

								</td>

							</tr>

						</tbody>

					</table>

					<?php submit_button(); ?>

				</form>

			<?php

			} else {

			?>

				<p>topic not found. Go back to <b><a href="<?php echo $parent_url; ?>">topics list</a></b>.</p>

			<?php

			}

		}



		public function loadEducLevelSubjects() {

			global $wpdb;



			$level_ids = $_GET['level_ids'];



			if (!empty($level_ids)) {

				$sql = "SELECT * FROM {$wpdb->prefix}js_subjects WHERE is_active = 1 "

					. "AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id IN (";

				foreach ($level_ids as $key => $level_id) {

					$sql .= $level_id;

					if ($key < count($level_ids) - 1) {

						$sql .= ",";

					}

				}

				$sql .= ")) ORDER BY subject_name ASC";

				$subjects = $wpdb->get_results( $sql );

			}

			

			$html = '<select name="subject_id[]" multiple="multiple" style="min-height: 200px;"><option value="">Select Subject</option>';

			if (!empty($subjects)) {

				foreach ($subjects as $subject ) {

					$html .= '<option value="' . $subject->subject_id . '">' . $subject->subject_name . '</option>';

				}

			}

			$html .= '</select>';



			echo $html;

		}



	}



}



?>