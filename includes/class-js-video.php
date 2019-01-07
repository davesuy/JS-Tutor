<?php

class JS_topic_Manager_Video {

	public function __construct() {

	}

	public function js_video_home_shortcode() {
		$api = new JS_topic_Manager_Api();
		$video = $api->getHomeVideo();

		if ( !empty($video) ) : ?>
			<video controls autoplay loop>
				<source src="<?php echo $video ?>" type="video/mp4">
			</video> 
		<?php endif;
	}

	public function js_video_intro_shortcode() {
		$api = new JS_topic_Manager_Api();
		$videos = $api->getIntroVideo();

		if ( !empty($videos) && count($videos) > 0) :
			foreach ($videos as $key => $video) :	
				if ($key % 2 == 0) : ?>
					<div class="video-item">
						<div class="video">
							<video controls>
								<source src="<?php echo $video->video_src; ?>" type="video/mp4">
							</video>
						</div>
						<div class="content">
							<h5 class="header"><?php echo $video->name; ?></h5>
							<p class="description">
								<?php if (!empty($video->long_description)) : ?>
									<?php echo str_replace("\n", "<br/>", $video->long_description); ?>
								<?php else : ?>
									<?php echo str_replace("\n", "<br/>", $video->description); ?>
								<?php endif; ?>
							</p>
						</div>
					</div>
				<?php else: ?>
					<div class="video-item">
						<div class="content">
							<h5><?php echo $video->name; ?></h5>
							<p class="description">
								<?php if (!empty($video->long_description)) : ?>
									<?php echo str_replace("\n", "<br/>", $video->long_description); ?>
								<?php else : ?>
									<?php echo str_replace("\n", "<br/>", $video->description); ?>
								<?php endif; ?>
							</p>
						</div>
						<div class="video">
							<video controls="">
								<source src="<?php echo $video->video_src; ?>" type="video/mp4">
							</video>
						</div>
					</div>
				<?php endif;
			endforeach;
		endif;
	}

	public function js_video_welcome_shortcode() {
		$api = new JS_topic_Manager_Api();
		$video = $api->getWelcomeVideo();

		if ( !empty($video) ) : ?>
			<video controls loop>
				<source src="<?php echo $video; ?>" type="video/mp4">
			</video>
		<?php endif;
	}

	public function js_videos_subjects_shortcode() { ?>
		<div id="video-library-navigation"></div>
		<div id="video-library">
			
			<h1><?php echo __('Choose from the Following Subjects'); ?></h1>

			<div id="accordion">
				<!-- Kinder prep subjects -->
				<?php self::generate_subjects_list( 
					__('Pre-Kindergarten'), 
					'kindergarten-preparation' ); ?>
				<!-- Kinder to grade school subjects -->
				<?php self::generate_subjects_list( 
					__('Kindergarten through 12th Grade Subjects'), 
					'kindergarten-to-grade-12' ); ?>
			  <!-- College prep subjects -->
			  <?php self::generate_subjects_list( 
			  	__('College Preparation Subjects'), 
			  	'college-preparation' ); ?>
			</div>

			<a class="btn-default white btn-next"><?php echo __( 'Next' ); ?></a>

		</div>
	<?php }

	public function js_videos_topics_shortcode( $attrs ) {
		$api = new JS_topic_Manager_Api();
		$selected_level = $api->getLevel( (int)$attrs['level'] );
		$selected_subject = $api->getSubjectById( (int)$attrs['subject'] );
		$selected_topic = $api->getTopicById( (int)$attrs['topic'] );
		$levels = $api->getGradeLevels();
		$subjects = $api->getSubjectsBySlugAndId( $selected_level->group_slug, $selected_level->level_id );
		$topics = $api->getTopics( $selected_subject->subject_id );

		if (   !empty($selected_level->bright_cove_video_tag) 
			&& !empty($selected_subject->bright_cove_video_tag)
			&& !empty($selected_topic)) {
			// Inherited videos from educ level and subject
			$inherited_tags = array(
				trim($selected_level->bright_cove_video_tag),
				trim($selected_subject->bright_cove_video_tag)
			);

			// Topics
			$included_topics = array();
			foreach ($topics as $i => $t) {
				if (   empty($selected_topic)
					|| (!empty($selected_topic) && $t['topic_id'] === $selected_topic['topic_id'])) {
					$topic_tags = $inherited_tags;
					$topic_tags[] = $t['bright_cove_video_tag'];
					$topic_videos = $api->getVideosByTag( $t['bright_cove_video_tag'], false );
					$include_topic_videos = array();
					foreach ($topic_videos as $vi => $v) {
						$include = array_intersect($topic_tags, $v->tags);
						if (!empty($include) && count($topic_tags) === count($include)) {
							$include_topic_videos[] = $v;
						}
					}
					if (!empty($include_topic_videos)) {
						$t['videos'] = $include_topic_videos;
						$included_topics[] = $t;
					}
				}
			}
		} else {
			$included_topics = array();
		}
		wp_localize_script( 'js-topic-manager-client-js', 
			'js_topic_manager', 
			array( 
				'ajax_url' 		=> admin_url( 'admin-ajax.php' )
			)
		);
		?>

		<div id="video-library-navigation">
			<a class="btn-default white btn-back"><?php echo __( 'Back to Subjects' ); ?></a>
		</div>
		<div id="video-library" class="topics">

			<h1><?php echo __('Choose from the Following topics'); ?></h1>

			<div class="filters">
				<!-- Grade level filter -->
				<div class="select-container">
					<select name="topics_level">
					<option value=''>Select Grade</option>
					<?php foreach ($levels as $level) : ?>
						<option value="<?php echo $level['level_id'];?>" <?php selected( $selected_level->level_id, $level['level_id'] ); ?>>
							<?php echo $level['level_name']; ?>
						</option>
					<?php endforeach; ?>
					</select>
				</div>
				<!-- Subjects filter -->
				<div class="select-container">
					<select name="topics_subject">
					<?php foreach ($subjects as $subject) : ?>
						<option value="<?php echo $subject['subject_id'];?>" <?php selected( $selected_subject->subject_id, $subject['subject_id'] ); ?>>
							<?php echo $subject['subject_name']; ?>
						</option>
					<?php endforeach; ?>
					</select>
				</div>
				<!-- Topics filter -->
				<div class="select-container" style="<?php echo empty($topics) ? 'display: none;' : ''; ?>">
					<select name="topics_selector">
					<option value=''>Select Topic</option>
					<?php foreach ($topics as $topic) : ?>
						<option value="<?php echo $topic['topic_id'];?>" <?php selected( $selected_topic['topic_id'], $topic['topic_id'] ); ?>>
							<?php echo $topic['topic_name']; ?>
						</option>
					<?php endforeach; ?>
					</select>
				</div>
			</div>

			<?php if ( count($included_topics) > 0 ) : ?>
				<div id="accordion" class="topics">
					<?php foreach ($included_topics as $topic) : ?>
						<?php if ( !empty($topic['videos']) && count($topic['videos']) > 0 ) : ?>
							<h3><?php echo $topic['topic_name']; ?></h3>
							<div>
								<ul>
									<?php foreach ($topic['videos'] as $key => $video) : ?>
										<li class="topic">
											<div class="title">
												<div class="roundedOne">
													<input type="radio" value="<?php echo $video->id; ?>" 
														id="video-<?php echo $video->id; ?>" 
														name="video">
													<label for="roundedOne"></label>
												</div>
												<div class="topic-name"><?php echo $video->name; ?></div>
												<a class="read-more">Read more</a>
												<a class="watch-video" href="<?php echo site_url() . '/video/?v=' . $video->id; ?>">Watch</a>
											</div>
											<div class="content">
												<p class="description">
													<?php if (!empty($video->long_description)) : ?>
														<?php echo str_replace("\n", "<br/>", $video->long_description); ?>
													<?php else : ?>
														<?php echo str_replace("\n", "<br/>", $video->description); ?>
													<?php endif; ?>
												</p>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php else : ?>
							<h3><?php echo $topic['topic_name']; ?></h3>
							<div>
								<p class="placeholder"><?php echo __( 'No videos.' ); ?></p>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<a class="btn-default white btn-next"><?php echo __( 'Next' ); ?></a>
			<?php endif; ?>

			<?php if (!empty($selected_topic) && empty($included_topics)) : ?>
				<p class="placeholder"><?php echo __( 'No videos.' ); ?></p>
			<?php endif; ?>
		</div>

	<?php }

	public function js_videos_tutorials_shortcode() { 
		$id = $_GET['v'];
		$api = new JS_topic_Manager_Api();
		$video = $api->getVideoById($id);
		$related_videos = $api->getRelatedVideos($id);
		?>
	
		<div id="video-library" class="video-tutorial">
			<div class="col col-3">
				<div class="col-3-2">
					<div id="video-library-navigation">
						<a class="btn-default white btn-back"><?php echo __( 'Back to topics' ); ?></a>
					</div>
					<?php if ( !empty($video) ) : ?>
						<video controls="">
							<source src="<?php echo $video->video_src; ?>" type="video/mp4">
						</video>
						<h1 class="header"><?php echo $video->name; ?></h1>
						<p class="description">
							<?php if (!empty($video->long_description)) : ?>
								<?php echo str_replace("\n", "<br/>", $video->long_description) ?>
							<?php else : ?>
								<?php echo str_replace("\n", "<br/>", $video->description) ?>
							<?php endif; ?>
						</p>
					<?php else : ?>
						<p class="placeholder"><?php echo __('Video not found.'); ?></p>
					<?php endif; ?>
				</div>
				<?php if (!empty($related_videos)) : ?>
					<div class="col-3-1 suggested-videos">
						<h5><?php echo __('Other Suggested Videos'); ?></h5>
						<?php if (!empty($related_videos->related_video_1)) : ?>
							<?php $video = $api->getVideoById($related_videos->related_video_1); ?>
							<?php if (!empty($video)) : ?>
								<div class="video-item">
									<div class="video">
										<img src="<?php echo $video->images->thumbnail->src; ?>" alt="<?php echo $video->name; ?>">
									</div>
									<div class="content">
										<h6 class="header"><?php echo $video->name; ?></h6>
										<p class="description">
											<?php if (!empty($video->long_description)) : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->long_description, 0, 100)) . '...'; ?>
											<?php else : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->description, 0, 100)) . '...'; ?>
											<?php endif; ?>
										</p>
										<a class="watch-video" href="<?php echo site_url() . '/video/?v=' . $video->id; ?>">Watch</a>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<?php if (!empty($related_videos->related_video_2)) : ?>
							<?php $video = $api->getVideoById($related_videos->related_video_2); ?>
							<?php if (!empty($video)) : ?>
								<div class="video-item">
									<div class="video">
										<img src="<?php echo $video->images->thumbnail->src; ?>" alt="<?php echo $video->name; ?>">
									</div>
									<div class="content">
										<h6 class="header"><?php echo $video->name; ?></h6>
										<p class="description">
											<?php if (!empty($video->long_description)) : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->long_description, 0, 100)) . '...'; ?>
											<?php else : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->description, 0, 100)) . '...'; ?>
											<?php endif; ?>
										</p>
										<a class="watch-video" href="<?php echo site_url() . '/video/?v=' . $video->id; ?>">Watch</a>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<?php if (!empty($related_videos->related_video_3)) : ?>
							<?php $video = $api->getVideoById($related_videos->related_video_3); ?>
							<?php if (!empty($video)) : ?>
								<div class="video-item">
									<div class="video">
										<img src="<?php echo $video->images->thumbnail->src; ?>" alt="<?php echo $video->name; ?>">
									</div>
									<div class="content">
										<h6 class="header"><?php echo $video->name; ?></h6>
										<p class="description">
											<?php if (!empty($video->long_description)) : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->long_description, 0, 100)) . '...'; ?>
											<?php else : ?>
												<?php echo str_replace("\n", "<br/>", substr($video->description, 0, 100)) . '...'; ?>
											<?php endif; ?>
										</p>
										<a class="watch-video" href="<?php echo site_url() . '/video/?v=' . $video->id; ?>">Watch</a>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php }

	private function generate_subjects_list( $title = '', $slug = '' ) { ?>
		<h3><?php echo $title; ?></h3>
		<div>
			<?php $api = new JS_topic_Manager_Api();
				$levels = $api->getGradeLevels( $slug );

			wp_localize_script( 'js-topic-manager-client-js', 
				'js_topic_manager', 
				array( 
					'ajax_url' 		=> admin_url( 'admin-ajax.php' )
				)
			);

			if ( !empty($levels) && count($levels) > 1 ) : 
				$selected_level = $levels[0]['level_id'];
			?>
				<div class="filters">
					<div class="select-container">
						<select name="grade_level">
							<option>Select Grade</option>
							<?php foreach ($levels as $level) : ?>
								<option value="<?php echo $level['level_id'];?>" <?php selected( $selected_level, $level['level_id'] ); ?>>
									<?php echo $level['level_name']; ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>
			<?php endif; ?>

			<?php $subjects = $api->getSubjectsBySlugAndId( $slug, $selected_level );
			if ( empty($selected_level) ) {
				$l = $api->getLevelBySlug($slug);
				if (!empty($l)) $selected_level = $l->level_id;
			}
			if ( !empty($subjects) && count($subjects) > 0 ) : ?>
				<ul>
					<?php foreach ($subjects as $subject) : 
					$topics = $api->getTopics( $subject['subject_id'] );
					?>
						<li>
							<?php if ( empty($topics) ) : ?>
								<div class="roundedOne">
									<input type="radio" value="<?php echo $subject['subject_id']; ?>" 
										id="subject-<?php echo $subject['subject_id']; ?>" 
										class="subject"
										name="subject-topic"
										level="<?php echo $selected_level; ?>">
									<label for="roundedOne"></label>
								</div>
								<div class="subject-name"><?php echo $subject['subject_name']; ?></div>
							<?php else : ?>
								<div class="subject-topic-accordion">
									<div class="subject">
										<div class="roundedOne">
											<input type="radio" value="<?php echo $subject['subject_id']; ?>" 
												id="subject-<?php echo $subject['subject_id']; ?>" 
												class="subject"
												name="subject-topic"
												level="<?php echo $selected_level; ?>">
											<label for="roundedOne"></label>
										</div>
										<div class="subject-name"><?php echo $subject['subject_name']; ?></div>
									</div>
									<div class="topics" style="display: none;">
										<?php foreach ($topics as $topic) : ?>
											<div class="topic">
												<div class="roundedOne">
													<input type="radio" value="<?php echo $topic['topic_id']; ?>" 
														id="topic-<?php echo $topic['topic_id']; ?>" 
														class="topic"
														name="subject-topic"
														level="<?php echo $selected_level; ?>"
														subject="<?php echo $subject['subject_id']; ?>">
													<label for="roundedOne"></label>
												</div>
												<div class="topic-name"><?php echo $topic['topic_name']; ?></div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
			  <p class="placeholder"><?php echo __( 'No subjects.' ); ?></p>
			<?php endif; ?>
		</div>
	<?php }

	/**
	 * Client AJAX class
	 */

	public function loadEducLevelSubjectsDropdown() {
		global $wpdb;

		$api = new JS_topic_Manager_Api();

		$level_id = $_GET['level_id'];

		$subjects = $api->getSubjectsById( $level_id );
			
		$html = '<select name="topics_subject"><option value="">Select Subject</option>';
		foreach ($subjects as $subject ) {
			$html .= '<option value="' . $subject['subject_id'] . '">' . $subject['subject_name'] . '</option>';
		}
		$html .= '</select>';

		echo $html;
	}

	public function loadEducLevelSubjectsList() {
		global $wpdb;

		$api = new JS_topic_Manager_Api();

		$level_id = $_GET['level_id'];

		$subjects = $api->getSubjectsById( $level_id );
			
		if ( !empty($subjects) && count($subjects) > 0 ) {
			$html = '<ul>';

			foreach ($subjects as $subject) {
				$topics = $api->getTopics( $subject['subject_id'] );
				
				$html .= '<li>';
				
				if ( empty($topics) ) {
					$html .= '<div class="roundedOne">'
						. '		<input type="radio" value="' . $subject['subject_id'] . '" id="subject-' . $subject['subject_id'] . '" class="subject" name="subject-topic" level="' . $level_id . '">'
						. '		<label for="roundedOne"></label>'
						. '</div>'
						. '<div class="subject-name">' . $subject['subject_name'] . '</div>';
				} else {
					$html .= '<div class="subject-topic-accordion">'
						. '		<div class="subject">'
						. '			<div class="roundedOne">'
						. '				<input type="radio" value="' . $subject['subject_id'] . '" id="subject-' . $subject['subject_id'] . '" class="subject" name="subject-topic" level="' . $level_id . '">'
						. '				<label for="roundedOne"></label>'
						. '			</div>'
						. '			<div class="subject-name">' . $subject['subject_name'] . '</div>'
						. '		</div>'
						. '		<div class="topics" style="display: none;">';

					foreach ($topics as $topic) {
						$html .= '<div class="topic">'
							. '		<div class="roundedOne">'
							. '			<input type="radio" value="' . $topic['topic_id'] . '" id="topic-' . $topic['topic_id'] . '" class="topic" name="subject-topic" level="' . $level_id . '" subject="' . $subject['subject_id'] . '">'
							. '			<label for="roundedOne"></label>'
							. '		</div>'
							. '		<div class="topic-name">' . $topic['topic_name'] . '</div>'
							. '</div>';
					}

					$html .= '</div></div>';
				}

				$html .= '</li>';
			}

			$html .= '</ul>';
		} else {
			$html = '<p class="placeholder">No subjects.</p>';
		}

		echo $html;
	}

	public function loadSubjectTopicsDropdown() {
		global $wpdb;

		$api = new JS_topic_Manager_Api();

		$subject_id = $_GET['subject_id'];
		
		$topics = $api->getTopics($subject_id);

		$html = '<select name="topics_selector"><option value="">Select Topic</option>';
		foreach ($topics as $topic ) {
			$html .= '<option value="' . $topic['topic_id'] . '">' . $topic['topic_name'] . '</option>';
		}
		$html .= '</select>';

		echo $html;
	}

}

?>