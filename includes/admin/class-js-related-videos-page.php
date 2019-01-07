<?php

class JS_topic_Manager_Related_Videos_Page {

	public function __construct() {
		add_action( 'js_topic_manager_related_videos_page', array( $this, 'render' ) );
	}

	/**
	 * Displays related videos page
	 */
	public function render() { 
		$api = new JS_topic_Manager_Api();
		wp_localize_script( 'js-topic-manager-admin-js', 
			'js_topic_manager', 
			array( 
				'ajax_url' 		=> admin_url( 'admin-ajax.php' )
			)
		);
		$grade_levels = $api->getGradeLevels();
		?>
		<div class="wrap js-topic-manager">
			<div class="related-videos">
				<h1>Related Videos</h1>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Grade Level:</th>
							<td>
								<?php if (!empty($grade_levels)) : ?>
									<select class="grade-level">
										<option value="">---Select Grade Level---</option>
										<?php foreach ($grade_levels as $level) : ?>
											<option value="<?php echo $level['level_id']; ?>">
												<?php echo $level['level_name']; ?>
											</option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row">Subject:</th>
							<td><select class="subject"></select></td>
						</tr>
						<tr>
							<th scope="row">Topic:</th>
							<td><select class="topic"></select></td>
						</tr>
						<tr class="video-selector">
							<th scope="row">Primary Video:</th>
							<td>
								<select class="primary-video"></select>
								<p class="selected-video" style="display: none;">
									<span class="label"></span>
									<span class="remove">x</span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Related Video 1:</th>
							<td>
								<select class="related-video-1"></select>
								<p class="selected-video" style="display: none;">
									<span class="label"></span>
									<span class="remove">x</span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Related Video 2:</th>
							<td>
								<select class="related-video-2"></select>
								<p class="selected-video" style="display: none;">
									<span class="label"></span>
									<span class="remove">x</span>
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">Related Video 3:</th>
							<td>
								<select class="related-video-3"></select>
								<p class="selected-video" style="display: none;">
									<span class="label"></span>
									<span class="remove">x</span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
				<input type="button" class="save-related-video button-primary" value="Save">
				<br/><br/>
				<table class="wp-list-table widefat fixed striped subjects">
					<thead>
						<tr>
							<th>Primary Video</th>
							<th>Related Video 1</th>
							<th>Related Video 2</th>
							<th>Related Video 3</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php $relatedVideos = $this->getRelatedVideos(); ?>
						<?php if (!empty($relatedVideos)) : ?>
							<?php foreach ($relatedVideos as $v) : ?>
								<tr id="<?php echo $v->related_video_id; ?>">
									<td><?php echo sprintf('%s <b>(%s)</b>', $v->primary_video_name, $v->primary_video); ?></td>
									<td><?php echo sprintf('%s <b>(%s)</b>', $v->related_video_1_name, $v->related_video_1); ?></td>
									<td><?php echo sprintf('%s <b>(%s)</b>', $v->related_video_2_name, $v->related_video_2); ?></td>
									<td><?php echo sprintf('%s <b>(%s)</b>', $v->related_video_3_name, $v->related_video_3); ?></td>
									<td><a href="#" class="delete-video">Delete</a></td>
								</tr>
							<?php endforeach; ?>
						<?php else : ?>
							<tr>
								<td colspan="5">No items.</td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php }

	public function loadEducLevelSubjectsOptions() {
		global $wpdb;

		$level_id = $_GET['level_id'];

		$sql = "SELECT * FROM {$wpdb->prefix}js_subjects WHERE is_active = 1 "
			. "AND subject_id IN (SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $level_id. ") "
			. "ORDER BY subject_name ASC";
		$subjects = $wpdb->get_results( $sql );

		$html = '';
		if ( !empty($subjects) && count($subjects) > 0 ) {
			$html .= '<option value="">---Select Subject---</option>';
			foreach ($subjects as $subject) {
				$html .= '<option value="' . $subject->subject_id . '">'
					.		$subject->subject_name
					. '</option>';
			}
		}

		echo $html;
	}

	public function loadSubjectstopicsOptions() {
		global $wpdb;

		$subject_id = $_GET['subject_id'];

		$topics = $wpdb->get_results( "SELECT topic.*, topic_subject.topic_subject_id, topic_subject.subject_id FROM {$wpdb->prefix}js_topics AS topic "
			. "JOIN {$wpdb->prefix}js_topic_subjects AS topic_subject ON topic.topic_id = topic_subject.topic_id "
			. "WHERE topic_subject.subject_id = " . $subject_id . " "
			. "AND topic.is_active = 1 ORDER BY topic.topic_name ASC", 'ARRAY_A' );
		
		$html = '';
		if ( !empty($topics) && count($topics) > 0 ) {
			$html .= '<option value="">---Select Topic---</option>';
			foreach ($topics as $topic ) {
				$html .= '<option value="' . $topic['topic_id'] . '">' . $topic['topic_name'] . '</option>';
			}
		}

		echo $html;
	}

	public function getVideosByGradeSubjecttopicIds() {
		global $wpdb;

		$data = $_GET['data'];

		if (!empty($data) && count($data) > 0) {
			$inherited_tags = array();
			$level_tag = null;
			$topic_tag = null;
			foreach ($data as $d) {
				$sql = "SELECT * FROM {$wpdb->prefix}" . $d['type'] . " WHERE ";
				if ($d['type'] === 'js_grade_level') {
					$sql .= "level_id = " . (int)$d['id'];
				} else if ($d['type'] === 'js_subjects') {
					$sql .= "subject_id = " . (int)$d['id'];
				} else {
					$sql .= "topic_id = " . (int)$d['id'];
				}
				$sql .= " AND is_active = 1";

				$result = $wpdb->get_row($sql);

				if (!empty($result)) {
					if ($d['type'] === 'js_grade_level') {
						$level_tag = $result->bright_cove_video_tag;
						$inherited_tags[] = $result->bright_cove_video_tag;
					}
					if ($d['type'] === 'js_subjects') {
						$inherited_tags[] = $result->bright_cove_video_tag;
					}
					if ($d['type'] === 'js_topics') {
						$topic_tag = $result->bright_cove_video_tag;
					}
				}
			}

			if (!empty($level_tag) && !empty($topic_tag)) {
				$api = new JS_topic_Manager_Api();
				$tags = array_filter($tags);
				$videos = array();
				// Primary video selection
				$topic_tags = $inherited_tags;
				$topic_tags[] = $topic_tag;
				$topic_videos = $api->getVideosByTag($topic_tag, false);
				foreach ($topic_videos as $vi => $v) {
					$include = array_intersect($topic_tags, $v->tags);
					if (!empty($include) && count($topic_tags) === count($include)) {
						$videos['primary'][] = $v;
					}
				}
				// Related videos
				$videos['related'] = $api->getVideosByTag($level_tag, false);
				echo json_encode($videos);
			} else {
				echo json_encode(array());
			}
		} else {
			echo json_encode(array());
		}
	}

	public function saveRelatedVideos() {
		global $wpdb;

		$data = $_POST['data'];
		$result = array();
		$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}js_related_videos WHERE primary_video = '" . $data['primary_video'] . "'");
		
		if ((int)$count > 0) {
			$result = array( 'error' => 'already_exists' );
		} else {
			$result = $wpdb->insert(
				"{$wpdb->prefix}js_related_videos",
				array(
					'primary_video' 		=> $data['primary_video'],
					'primary_video_name'	=> $data['primary_video_name'],
					'related_video_1' 		=> $data['related_video_1'],
					'related_video_1_name'	=> $data['related_video_1_name'],
					'related_video_2'		=> $data['related_video_2'],
					'related_video_2_name'	=> $data['related_video_2_name'],
					'related_video_3'		=> $data['related_video_3'],
					'related_video_3_name'	=> $data['related_video_3_name'],
				)
			);
		}

		echo json_encode($result);
		
	}

	public function getRelatedVideos() {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_related_videos" );
	}

	public function deleteRelatedVideos() {
		global $wpdb;

		$id = $_POST['data'];
		$result = $wpdb->delete( 
			"{$wpdb->prefix}js_related_videos",
			array( 'related_video_id' => $id ) 
		);


		echo json_encode($result);		
	}

}

?>