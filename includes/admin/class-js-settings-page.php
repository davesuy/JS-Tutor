<?php

class JS_topic_Manager_Settings_Page {

	public function __construct() {
		add_action( 'js_topic_manager_settings_page', array( $this, 'render' ) );
		add_action( 'admin_init', array( $this, 'js_topic_manager_settings_init') );
	}

	/**
	 * Displays settings page
	 */
	public function render() { ?>
		<div class="wrap js-topic-manager">
			<h1>Topic Manager Settings</h1>
			<form action='options.php' method='post'>
				<?php
					settings_fields( 'jstopicManagerSettingsPage' );
					do_settings_sections( 'jstopicManagerSettingsPage' );
					submit_button();
				?>
			</form>
		</div>
	<?php }

	public function js_topic_manager_settings_init() { 
		register_setting( 'jstopicManagerSettingsPage', 'js_topic_manager_settings' );

		add_settings_section(
			'js_topic_manager_jstopicManagerSettingsPage_section', 
			null, 
			null, 
			'jstopicManagerSettingsPage'
		);

		add_settings_field( 
			'js_topic_manager_brightcove_account_id', 
			__( 'BrightCove Account ID:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_brightcove_account_id_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);

		add_settings_field( 
			'js_topic_manager_brightcove_client_id', 
			__( 'BrightCove Client ID:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_brightcove_client_id_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);

		add_settings_field( 
			'js_topic_manager_brightcove_client_secret', 
			__( 'BrightCove Client Secret:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_brightcove_client_secret_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);

		add_settings_field( 
			'js_topic_manager_home_page_video_id', 
			__( 'Home Page Video ID:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_home_page_video_id_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);

		add_settings_field( 
			'js_topic_manager_video_introduction_page_video_tag', 
			__( 'Video Introduction Page Tag:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_video_introduction_page_video_tag_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);

		add_settings_field( 
			'js_topic_manager_video_library_page_video_id', 
			__( 'Video Library Page Video ID:', 'js_topic_manager' ), 
			array( $this, 'js_topic_manager_video_library_page_video_id_render' ), 
			'jstopicManagerSettingsPage', 
			'js_topic_manager_jstopicManagerSettingsPage_section' 
		);
	}

	public function js_topic_manager_brightcove_account_id_render(  ) { 
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_brightcove_account_id]' value='<?php echo $options['js_topic_manager_brightcove_account_id']; ?>'>
	<?php }

	public function js_topic_manager_brightcove_client_id_render() {
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_brightcove_client_id]' value='<?php echo $options['js_topic_manager_brightcove_client_id']; ?>'>
	<?php }

	public function js_topic_manager_brightcove_client_secret_render() {
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_brightcove_client_secret]' value='<?php echo $options['js_topic_manager_brightcove_client_secret']; ?>'>
	<?php }

	public function js_topic_manager_home_page_video_id_render(  ) { 
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_home_page_video_id]' value='<?php echo $options['js_topic_manager_home_page_video_id']; ?>'>
		<p class="description">Video to be shown in <b><i>home page</i></b>.</p>
	<?php }

	public function js_topic_manager_video_introduction_page_video_tag_render(  ) { 
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_video_introduction_page_video_tag]' value='<?php echo $options['js_topic_manager_video_introduction_page_video_tag']; ?>'>
		<p class="description">Videos with specified "tag" to be shown in <b><i>video introduction page</i></b> for not logged in users. Comma separated video tags.</p>
	<?php }

	public function js_topic_manager_video_library_page_video_id_render(  ) { 
		$options = get_option( 'js_topic_manager_settings' ); ?>
		<input type='text' name='js_topic_manager_settings[js_topic_manager_video_library_page_video_id]' value='<?php echo $options['js_topic_manager_video_library_page_video_id']; ?>'>
		<p class="description">Video to be shown in <b><i>video library page</i></b> for logged in users.</p>
	<?php }

}

?>