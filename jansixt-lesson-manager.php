<?php

/**

 * Plugin Name: Jan Sixt Topic Manager

 * Description: Manages grade level, subjects, topics and modules


 * Author: Kooeedirnnn


 * Version: 1.0

*/



define( 'JAN_SIXT_topic_MANAGER_VERSION', '1.0' );

define( 'JAN_SIXT_topic_MANAGER_URL', plugin_dir_url( __FILE__ ) );

define( 'JAN_SIXT_topic_MANAGER_PATH', dirname( __FILE__ ) . '/' );

define( 'JAN_SIXT_topic_MANAGER_BASENAME', plugin_basename( __FILE__ ) );



register_activation_hook( __FILE__, 'js_topic_manager_add_table', 0 );



if ( !function_exists('js_topic_manager_add_table') ) {

	/**

	 * Create new table to database intended for api

	 */

	function js_topic_manager_add_table(){

		global $jal_db_version;

		global $wpdb;



		$grade_level_table_name = $wpdb->prefix . 'js_grade_level';

		$subjects_table_name = $wpdb->prefix . 'js_subjects';

		$subject_levels_table_name = $wpdb->prefix . 'js_subject_levels';

		$topics_table_name = $wpdb->prefix . 'js_topics';

		$topic_subjects_table_name = $wpdb->prefix . 'js_topic_subjects';

		$related_video_table_name = $wpdb->prefix . 'js_related_videos';

		$jal_db_version = '1.0';



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$grade_level_table_name'" ) != $grade_level_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $grade_level_table_name (

		    	level_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		level_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	group_slug TINYTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	level_order BIGINT(20),

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (level_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$subjects_table_name'" ) != $subjects_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $subjects_table_name (

		    	subject_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		subject_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (subject_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$subject_levels_table_name'" ) != $subject_levels_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $subject_levels_table_name (

		    	subject_level_id BIGINT(20) NOT NULL AUTO_INCREMENT,

		    	subject_id BIGINT(20) NOT NULL,

	          	level_id BIGINT(20) NOT NULL,

	          	UNIQUE KEY id (subject_level_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$topics_table_name'" ) != $topics_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $topics_table_name (

		    	topic_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		topic_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (topic_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$topic_subjects_table_name'" ) != $topic_subjects_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $topic_subjects_table_name (

		    	topic_subject_id BIGINT(20) NOT NULL AUTO_INCREMENT,

		    	topic_id BIGINT(20) NOT NULL,

	          	subject_id BIGINT(20) NOT NULL,

	          	UNIQUE KEY id (topic_subject_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$related_video_table_name'" ) != $related_video_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $related_video_table_name (

		    	related_video_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		primary_video VARCHAR(255) NOT NULL,

          		primary_video_name LONGTEXT,

	          	related_video_1 VARCHAR(255),

	          	related_video_1_name LONGTEXT,

	          	related_video_2 VARCHAR(255),

	          	related_video_2_name LONGTEXT,

	          	related_video_3 VARCHAR(255),

	          	related_video_3_name LONGTEXT,

	          	UNIQUE KEY id (related_video_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}

	}

}



/**

 * Register admin css

 */

add_action( 'admin_enqueue_scripts', 'js_topic_manager_enqueue_admin_assets', 10 );

function js_topic_manager_enqueue_admin_assets() {

	// Styling CSS

	wp_enqueue_style( 'js-topic-manager-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );



	wp_enqueue_script('js-topic-manager-admin-js', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array('jquery'), null, true);

}



// Register frontend styles and scripts

add_action( 'wp_enqueue_scripts', 'js_topic_manager_register_frontend_assets', 100 );

function js_topic_manager_register_frontend_assets() {

	// Client css

	wp_enqueue_style( 'js-topic-manager-client-css', plugin_dir_url( __FILE__ ) . 'assets/css/client.css' );

	// Client script

	wp_enqueue_script( 'js-topic-manager-client-js', plugin_dir_url( __FILE__ ) .'assets/js/client.js', array('jquery'), null, true );

}



/**

 * WP_List_Table class

 */

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}



/**

 * JS_topic_Manager_Admin_Menu class

 */

if ( !class_exists( 'JS_topic_Manager_Admin_Menu' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-admin-menu.php';

}



/**

 * JS_topic_Manager_Api class

 */

if ( !class_exists( 'JS_topic_Manager_Api' ) ) {

	require_once dirname( __FILE__ ) . '/includes/class-js-api.php';

}



/**

 * JS_topic_Manager_Video class

 */

if ( !class_exists( 'JS_topic_Manager_Video' ) ) {

	require_once dirname( __FILE__ ) . '/includes/class-js-video.php';

}



/**

 * JS_topic_Manager_Settings_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Settings_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-settings-page.php';

}



/**

 * JS_topic_Manager_Grade_Level_List_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Grade_Level_List_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-grade-level-page.php';

}



/**

 * JS_topic_Manager_Subjects_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Subjects_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-subjects-page.php';

}



/**

 * JS_topic_Manager_topics_Page class

 */

if ( !class_exists( 'JS_topic_Manager_topics_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-topics-page.php';

}



/**

 * JS_topic_Manager_Related_Videos_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Related_Videos_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-related-videos-page.php';

}



add_action( 'admin_menu', 'js_setup_admin_menu' );

if ( !function_exists( 'js_setup_admin_menu' ) ) {

	/**

	 * Add new menu item in admin

	 */

	function js_setup_admin_menu() {

		// Load topics manager plugin classes

		new JS_topic_Manager_Settings_Page();

		new JS_topic_Manager_Video();

		new JS_topic_Manager_Related_Videos_Page();

		

		$menu = new JS_topic_Manager_Admin_Menu;

		$menu->register_admin_menu();

	}

}



/**

 * Admin AJAX calls

 */

add_action( 'wp_ajax_js_topic_manager_load_subjects', 'js_topic_manager_load_subjects_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_load_subjects', 'js_topic_manager_load_subjects_callback' );

function js_topic_manager_load_subjects_callback() {

	$topic = new JS_topic_Manager_topic_Page();

	$topic->loadEducLevelSubjects();

	die();

}



add_action( 'wp_ajax_js_topic_manager_admin_load_subjects_select', 'js_topic_manager_admin_load_subjects_select_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_load_subjects_select', 'js_topic_manager_admin_load_subjects_select_callback' );

function js_topic_manager_admin_load_subjects_select_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->loadEducLevelSubjectsOptions();

	die();

}



add_action( 'wp_ajax_js_topic_manager_admin_load_topics_select', 'js_topic_manager_admin_load_topics_select_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_load_topics_select', 'js_topic_manager_admin_load_topics_select_callback' );

function js_topic_manager_admin_load_topics_select_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->loadSubjectstopicsOptions();

	die();

}



// Get videos for grade level, subject, topic

add_action( 'wp_ajax_js_topic_manager_admin_get_videos', 'js_topic_manager_admin_get_videos_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_get_videos', 'js_topic_manager_admin_get_videos_callback' );

function js_topic_manager_admin_get_videos_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->getVideosByGradeSubjecttopicIds();

	die();

}



// Save related videos

add_action( 'wp_ajax_js_topic_manager_admin_save_related_videos', 'js_topic_manager_admin_save_related_videos_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_save_related_videos', 'js_topic_manager_admin_save_related_videos_callback' );

function js_topic_manager_admin_save_related_videos_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->saveRelatedVideos();

	die();

}



// Delete related videos

add_action( 'wp_ajax_js_topic_manager_admin_delete_related_video', 'js_topic_manager_admin_delete_related_video_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_delete_related_video', 'js_topic_manager_admin_delete_related_video_callback' );

function js_topic_manager_admin_delete_related_video_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->deleteRelatedVideos();

	die();

}



/**

 * Client AJAX calls

 */

add_action( 'wp_ajax_js_topic_manager_client_load_subjects', 'js_topic_manager_client_load_subjects_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_subjects', 'js_topic_manager_client_load_subjects_callback' );

function js_topic_manager_client_load_subjects_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadEducLevelSubjectsDropdown();

	die();

}



add_action( 'wp_ajax_js_topic_manager_client_load_subjects_list', 'js_topic_manager_client_load_subjects_list_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_subjects_list', 'js_topic_manager_client_load_subjects_list_callback' );

function js_topic_manager_client_load_subjects_list_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadEducLevelSubjectsList();

	die();

}



add_action( 'wp_ajax_js_topic_manager_client_load_topics', 'js_topic_manager_client_load_topics_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_topics', 'js_topic_manager_client_load_topics_callback' );

function js_topic_manager_client_load_topics_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadSubjectTopicsDropdown();

	die();

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-home', array( 'JS_topic_Manager_Video', 'js_video_home_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-intro', array( 'JS_topic_Manager_Video', 'js_video_intro_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-welcome', array( 'JS_topic_Manager_Video', 'js_video_welcome_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-subjects', array( 'JS_topic_Manager_Video', 'js_videos_subjects_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-topics', array( 'JS_topic_Manager_Video', 'js_videos_topics_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-tutorials', array( 'JS_topic_Manager_Video', 'js_videos_tutorials_shortcode' ));

}



?>
=======
<?php

/**

 * Plugin Name: Jan Sixt Topic Manager

 * Description: Manages grade level, subjects, topics and modules

 * Author: Koodire

 * Version: 1.0

*/



define( 'JAN_SIXT_topic_MANAGER_VERSION', '1.0' );

define( 'JAN_SIXT_topic_MANAGER_URL', plugin_dir_url( __FILE__ ) );

define( 'JAN_SIXT_topic_MANAGER_PATH', dirname( __FILE__ ) . '/' );

define( 'JAN_SIXT_topic_MANAGER_BASENAME', plugin_basename( __FILE__ ) );



register_activation_hook( __FILE__, 'js_topic_manager_add_table', 0 );



if ( !function_exists('js_topic_manager_add_table') ) {

	/**

	 * Create new table to database intended f api

	 */

	function js_topic_manager_add_table(){

		global $jal_db_version;

		global $wpdb;


		$grade_level_table_name = $wpdb->prefix . 'js_grade_level';

		$subjects_table_name = $wpdb->prefix . 'js_subjects';

		$subject_levels_table_name = $wpdb->prefix . 'js_subject_levels';

		$topics_table_name = $wpdb->prefix . 'js_topics';

		$topic_subjects_table_name = $wpdb->prefix . 'js_topic_subjects';

		$related_video_table_name = $wpdb->prefix . 'js_related_videos';

		$jal_db_version = '1.0';



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$grade_level_table_name'" ) != $grade_level_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $grade_level_table_name (

		    	level_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		level_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	group_slug TINYTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	level_order BIGINT(20),

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (level_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$subjects_table_name'" ) != $subjects_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $subjects_table_name (

		    	subject_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		subject_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (subject_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$subject_levels_table_name'" ) != $subject_levels_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $subject_levels_table_name (

		    	subject_level_id BIGINT(20) NOT NULL AUTO_INCREMENT,

		    	subject_id BIGINT(20) NOT NULL,

	          	level_id BIGINT(20) NOT NULL,

	          	UNIQUE KEY id (subject_level_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$topics_table_name'" ) != $topics_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $topics_table_name (

		    	topic_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		topic_name VARCHAR(255) NOT NULL,

	          	description LONGTEXT,

	          	is_active BOOLEAN NOT NULL,

	          	bright_cove_video_tag LONGTEXT,

	          	date_created DATETIME DEFAULT '0000-00-00 00:00:00',

	          	last_updated DATETIME DEFAULT '0000-00-00 00:00:00',

	          	UNIQUE KEY id (topic_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$topic_subjects_table_name'" ) != $topic_subjects_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $topic_subjects_table_name (

		    	topic_subject_id BIGINT(20) NOT NULL AUTO_INCREMENT,

		    	topic_id BIGINT(20) NOT NULL,

	          	subject_id BIGINT(20) NOT NULL,

	          	UNIQUE KEY id (topic_subject_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}



		if ( $wpdb->get_var( "SHOW TABLES LIKE '$related_video_table_name'" ) != $related_video_table_name ) {

		    $charset_collate = $wpdb->get_charset_collate();

		 

		    $sql = "CREATE TABLE $related_video_table_name (

		    	related_video_id BIGINT(20) NOT NULL AUTO_INCREMENT,

          		primary_video VARCHAR(255) NOT NULL,

          		primary_video_name LONGTEXT,

	          	related_video_1 VARCHAR(255),

	          	related_video_1_name LONGTEXT,

	          	related_video_2 VARCHAR(255),

	          	related_video_2_name LONGTEXT,

	          	related_video_3 VARCHAR(255),

	          	related_video_3_name LONGTEXT,

	          	UNIQUE KEY id (related_video_id)

		    ) $charset_collate;";

		    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		    dbDelta( $sql );



		    add_option( 'jal_db_version', $jal_db_version );

		}

	}

}



/**

 * Register admin css

 */

add_action( 'admin_enqueue_scripts', 'js_topic_manager_enqueue_admin_assets', 10 );

function js_topic_manager_enqueue_admin_assets() {

	// Styling CSS

	wp_enqueue_style( 'js-topic-manager-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );



	wp_enqueue_script('js-topic-manager-admin-js', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', array('jquery'), null, true);

}



// Register frontend styles and scripts

add_action( 'wp_enqueue_scripts', 'js_topic_manager_register_frontend_assets', 100 );

function js_topic_manager_register_frontend_assets() {

	// Client css

	wp_enqueue_style( 'js-topic-manager-client-css', plugin_dir_url( __FILE__ ) . 'assets/css/client.css' );

	// Client script

	wp_enqueue_script( 'js-topic-manager-client-js', plugin_dir_url( __FILE__ ) .'assets/js/client.js', array('jquery'), null, true );

}



/**

 * WP_List_Table class

 */

if ( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

}



/**

 * JS_topic_Manager_Admin_Menu class

 */

if ( !class_exists( 'JS_topic_Manager_Admin_Menu' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-admin-menu.php';

}



/**

 * JS_topic_Manager_Api class

 */

if ( !class_exists( 'JS_topic_Manager_Api' ) ) {

	require_once dirname( __FILE__ ) . '/includes/class-js-api.php';

}



/**

 * JS_topic_Manager_Video class

 */

if ( !class_exists( 'JS_topic_Manager_Video' ) ) {

	require_once dirname( __FILE__ ) . '/includes/class-js-video.php';

}



/**

 * JS_topic_Manager_Settings_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Settings_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-settings-page.php';

}



/**

 * JS_topic_Manager_Grade_Level_List_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Grade_Level_List_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-grade-level-page.php';

}



/**

 * JS_topic_Manager_Subjects_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Subjects_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-subjects-page.php';

}



/**

 * JS_topic_Manager_topics_Page class

 */

if ( !class_exists( 'JS_topic_Manager_topics_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-topics-page.php';

}



/**

 * JS_topic_Manager_Related_Videos_Page class

 */

if ( !class_exists( 'JS_topic_Manager_Related_Videos_Page' ) ) {

	require_once dirname( __FILE__ ) . '/includes/admin/class-js-related-videos-page.php';

}



add_action( 'admin_menu', 'js_setup_admin_menu' );

if ( !function_exists( 'js_setup_admin_menu' ) ) {

	/**

	 * Add new menu item in admin

	 */

	function js_setup_admin_menu() {

		// Load topics manager plugin classes

		new JS_topic_Manager_Settings_Page();

		new JS_topic_Manager_Video();

		new JS_topic_Manager_Related_Videos_Page();

		

		$menu = new JS_topic_Manager_Admin_Menu;

		$menu->register_admin_menu();

	}

}



/**

 * Admin AJAX calls

 */

add_action( 'wp_ajax_js_topic_manager_load_subjects', 'js_topic_manager_load_subjects_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_load_subjects', 'js_topic_manager_load_subjects_callback' );

function js_topic_manager_load_subjects_callback() {

	$topic = new JS_topic_Manager_topic_Page();

	$topic->loadEducLevelSubjects();

	die();

}



add_action( 'wp_ajax_js_topic_manager_admin_load_subjects_select', 'js_topic_manager_admin_load_subjects_select_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_load_subjects_select', 'js_topic_manager_admin_load_subjects_select_callback' );

function js_topic_manager_admin_load_subjects_select_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->loadEducLevelSubjectsOptions();

	die();

}



add_action( 'wp_ajax_js_topic_manager_admin_load_topics_select', 'js_topic_manager_admin_load_topics_select_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_load_topics_select', 'js_topic_manager_admin_load_topics_select_callback' );

function js_topic_manager_admin_load_topics_select_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->loadSubjectstopicsOptions();

	die();

}



// Get videos for grade level, subject, topic

add_action( 'wp_ajax_js_topic_manager_admin_get_videos', 'js_topic_manager_admin_get_videos_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_get_videos', 'js_topic_manager_admin_get_videos_callback' );

function js_topic_manager_admin_get_videos_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->getVideosByGradeSubjecttopicIds();

	die();

}



// Save related videos

add_action( 'wp_ajax_js_topic_manager_admin_save_related_videos', 'js_topic_manager_admin_save_related_videos_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_save_related_videos', 'js_topic_manager_admin_save_related_videos_callback' );

function js_topic_manager_admin_save_related_videos_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->saveRelatedVideos();

	die();

}



// Delete related videos

add_action( 'wp_ajax_js_topic_manager_admin_delete_related_video', 'js_topic_manager_admin_delete_related_video_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_admin_delete_related_video', 'js_topic_manager_admin_delete_related_video_callback' );

function js_topic_manager_admin_delete_related_video_callback() {

	$related = new JS_topic_Manager_Related_Videos_Page();

	$related->deleteRelatedVideos();

	die();

}



/**

 * Client AJAX calls

 */

add_action( 'wp_ajax_js_topic_manager_client_load_subjects', 'js_topic_manager_client_load_subjects_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_subjects', 'js_topic_manager_client_load_subjects_callback' );

function js_topic_manager_client_load_subjects_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadEducLevelSubjectsDropdown();

	die();

}



add_action( 'wp_ajax_js_topic_manager_client_load_subjects_list', 'js_topic_manager_client_load_subjects_list_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_subjects_list', 'js_topic_manager_client_load_subjects_list_callback' );

function js_topic_manager_client_load_subjects_list_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadEducLevelSubjectsList();

	die();

}



add_action( 'wp_ajax_js_topic_manager_client_load_topics', 'js_topic_manager_client_load_topics_callback' );

add_action( 'wp_ajax_nopriv_js_topic_manager_client_load_topics', 'js_topic_manager_client_load_topics_callback' );

function js_topic_manager_client_load_topics_callback() {

	$video = new JS_topic_Manager_Video();

	$video->loadSubjectTopicsDropdown();

	die();

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-home', array( 'JS_topic_Manager_Video', 'js_video_home_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-intro', array( 'JS_topic_Manager_Video', 'js_video_intro_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-video-welcome', array( 'JS_topic_Manager_Video', 'js_video_welcome_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-subjects', array( 'JS_topic_Manager_Video', 'js_videos_subjects_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-topics', array( 'JS_topic_Manager_Video', 'js_videos_topics_shortcode' ));

}



if ( class_exists('JS_topic_Manager_Video') ) {

	add_shortcode('js-videos-tutorials', array( 'JS_topic_Manager_Video', 'js_videos_tutorials_shortcode' ));

}



?>
>>>>>>> 8dec0290b65f2cd41d40ce3de5d157ef3865baca
