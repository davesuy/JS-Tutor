<?php

class JS_topic_Manager_Admin_Menu {

	public function __construct() {

	}

	/**
	 * Generates the Jan Sixt topic manager menus in admin page
	 */
	public function register_admin_menu() {
		global $submenu;

		if (current_user_can('manage_options')) {
			add_menu_page( esc_html__( 'topic Manager', 'js_topic_manager' ), 
				esc_html__( 'Topic Manager', 'js_topic_manager' ), 
				'edit_posts', 
				'js_topic_manager', 
				null, 
				'dashicons-welcome-learn-more');
			
			add_submenu_page( 'js_topic_manager', 
				esc_html__( 'Settings', 'js_topic_manager' ), 
				esc_html__( 'Settings', 'js_topic_manager' ), 
				'manage_options', 
				'js_settings', 
				array( $this, 'js_show_settings_page' ) );

			add_submenu_page( 'js_topic_manager', 
				esc_html__( 'Educational Level', 'js_topic_manager' ), 
				esc_html__( 'Educational Level', 'js_topic_manager' ), 
				'edit_posts', 
				'js_grade_level', 
				array( $this, 'js_show_grade_level_page' ) );

			add_submenu_page( 'js_topic_manager', 
				esc_html__( 'Subjects', 'js_topic_manager' ), 
				esc_html__( 'Subjects', 'js_topic_manager' ), 
				'edit_posts', 
				'js_subject', 
				array( $this, 'js_show_subjects_page' ) );

			add_submenu_page( 'js_topic_manager', 
				esc_html__( 'Topics', 'js_topic_manager' ), 
				esc_html__( 'Topics', 'js_topic_manager' ), 
				'edit_posts', 
				'js_topics', 
				array( $this, 'js_show_topics_page' ) );

			add_submenu_page( 'js_topic_manager', 
				esc_html__( 'Related Videos', 'js_topic_manager' ), 
				esc_html__( 'Related Videos', 'js_topic_manager' ), 
				'manage_options', 
				'js_related_videos', 
				array( $this, 'js_show_related_videos_page' ) );

			// Removes the 'topic Manager' submenu from the menu that WP automatically provides when registering a top level page
			array_shift( $submenu['js_topic_manager'] );
		}
	}

	public function js_show_settings_page() {
		/**
		 * Fires when the setting page loads.
		 */
		do_action( 'js_topic_manager_settings_page' );
	}

	public function js_show_grade_level_page() {
		$action = $_REQUEST['action'];
		?>
		<div class="wrap js-topic-manager">
			<?php switch($action) {
				case 'add';
				case 'edit':
				?>
					<h1><?php echo ucfirst($action); ?> Grade Level</h1>
					<form method="post">
					<?php 
						$page = new JS_topic_Manager_Grade_Level_Page();
						$page->grade_level_form();
					?>
					</form>
				<?php
				break;
				default:
				?>
					<h1>
						Grade Level
						<?php printf( '<a class="page-title-action" href="?page=%s&action=%s">Add New</a>', esc_attr( $_REQUEST['page'] ), 'add' ) ?>
					</h1>
					<form method="post">
					<?php
						$list = new JS_topic_Manager_Grade_Level_List_Page();
						$list->prepare_items();
						$list->display(); 
					?>
					</form>
				<?php
				break;
			} ?>
			</form>
		</div>
		<?php
	}

	public function js_show_subjects_page() {
		$action = $_REQUEST['action'];
		?>
		<div class="wrap js-topic-manager">
			<?php switch($action) {
				case 'add';
				case 'edit':
				?>
					<h1><?php echo ucfirst($action); ?> Subject</h1>
					<form method="post">
					<?php 
						$page = new JS_topic_Manager_Subject_Page();
						$page->subject_form();
					?>
					</form>
				<?php
				break;
				default:
				?>
					<h1>
						Subjects
						<?php printf( '<a class="page-title-action" href="?page=%s&action=%s">Add New</a>', esc_attr( $_REQUEST['page'] ), 'add' ) ?>
					</h1>
					<form method="post">
					<?php
						$list = new JS_topic_Manager_Subject_List_Page();
						$list->advanced_filters();
						$list->prepare_items();
						$list->display(); 
					?>
					</form>
				<?php
				break;
			} ?>
			</form>
		</div>
		<?php
	}

	public function js_show_topics_page() {
		$action = $_REQUEST['action'];
		?>
		<div class="wrap js-topic-manager">
			<?php switch($action) {
				case 'add';
				case 'edit':
				?>
					<h1><?php echo ucfirst($action); ?> Topic</h1>
					<form method="post">
					<?php 
						$page = new JS_topic_Manager_topic_Page();
						$page->topic_form();
					?>
					</form>
				<?php
				break;
				default:
				?>
					<h1>
						Topics
						<?php printf( '<a class="page-title-action" href="?page=%s&action=%s">Add New</a>', esc_attr( $_REQUEST['page'] ), 'add' ) ?>
					</h1>
					<form method="post">
					<?php
						$list = new JS_topic_Manager_topic_List_Page();
						$list->advanced_filters();
						$list->prepare_items();
						$list->display(); 
					?>
					</form>
				<?php
				break;
			} ?>
			</form>
		</div>
		<?php
	}

	public function js_show_related_videos_page() {
		/**
		 * Fires when the related videos page loads.
		 */
		do_action( 'js_topic_manager_related_videos_page' );
	}

}

?>