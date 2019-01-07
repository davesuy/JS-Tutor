<?php

class JS_topic_Manager_Api {

	private $authBaseUrl;
	private $cmsBaseUrl;
	private $videoSource;
	private $options = array();
	private $accountId;
	private $homeVideoId;
	private $videoIntroTag;
	private $welcomeVideoId;

	public function __construct() {
		$this->authUrl = 'https://oauth.brightcove.com/v4/access_token';
		$this->cmsBaseUrl = 'https://cms.api.brightcove.com/v1';
		$this->videoByTag = '/accounts/{account_id}/videos?limit=1000&q=tags:{tag}';
		$this->videoById = '/accounts/{account_id}/videos/{video_id}';
		$this->videoSource = '/accounts/{account_id}/videos/{video_id}/sources';
		$this->searchVideos = '/accounts/{account_id}/videos?limit=100&q=text:"{text}"';

		$this->options = get_option( 'js_topic_manager_settings' );
		$this->accountId = $this->options['js_topic_manager_brightcove_account_id'];
		$this->homeVideoId = $this->options['js_topic_manager_home_page_video_id'];
		$this->videoIntroTag = $this->options['js_topic_manager_video_introduction_page_video_tag'];
		$this->welcomeVideoId = $this->options['js_topic_manager_video_library_page_video_id'];
	}

	public function getVideoById( $id ) {
		$url = $this->cmsBaseUrl . $this->videoById;
		$url = str_replace('{account_id}', $this->accountId, $url);
		$url = str_replace('{video_id}', $id, $url);
		$video = $this->request($url);
		if (!empty($video)) {
			$video->video_src = $this->getVideoSourceById($video->id);
			return $video;
		} else {
			return null;
		}
	}

	public function getVideoSourceById( $id ) {
		$url = $this->cmsBaseUrl . $this->videoSource;
		$url = str_replace('{account_id}', $this->accountId, $url);
		$url = str_replace('{video_id}', $id, $url);
		$sources = $this->request($url);
		if (!empty($sources)) {
			foreach ($sources as $source) {
				if (strtoupper($source->container) === 'MP4') {
					return $source->src;
					break;
				}
			}
		}
	}

	public function getVideosByTag( $tag, $include_src_video = true ) {
		$tag = array_map('trim',array_filter(explode(',', $tag)));
		$tag = implode(',', $tag);
		$tag = urlencode('"' . $tag . '"');
		$url = $this->cmsBaseUrl . $this->videoByTag;
		$url = str_replace('{account_id}', $this->accountId, $url);
		$url = str_replace('{tag}', $tag, $url);
		$videos = $this->request($url);
		if (!empty($videos)) {
			if ($include_src_video) {
				foreach ($videos as $video) {
					$video->video_src = $this->getVideoSourceById($video->id);
				}
			}
			return $videos;
		} else {
			return array();
		}
	}

	public function searchVideos( $searchKey = '' ) {
		$url = $this->cmsBaseUrl . $this->searchVideos;
		$url = str_replace('{account_id}', $this->accountId, $url);
		$url = str_replace('{text}', $searchKey, $url);
		return $this->request($url);
	}

	public function getHomeVideo() {
		return $this->getVideoSourceById($this->homeVideoId);
	}

	public function getIntroVideo() {
		return $this->getVideosByTag($this->videoIntroTag);
	}

	public function getWelcomeVideo() {
		return $this->getVideoSourceById($this->welcomeVideoId);
	}

	public function getRelatedVideos( $id ) {
		global $wpdb;

		return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_related_videos WHERE primary_video = '$id'" );
	}

	public function getGradeLevels( $slug = null ) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}js_grade_level WHERE is_active = 1 ";

		if ( !empty($slug) ) {
			$sql .= "AND group_slug = '" . $slug . "' ";
		}

		$sql .= "ORDER BY level_order ASC";

		return $wpdb->get_results( $sql, 'ARRAY_A' );
	}

	public function getLevel( $id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "
			. "WHERE level_id = " . $id . " "
			. "AND is_active = 1" );
	}

	public function getLevelBySlug( $slug ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_grade_level "
			. "WHERE group_slug = '" . $slug . "' "
			. "AND is_active = 1" );
	}

	public function getSubjectsBySlugAndId( $slug, $level ) {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}js_subjects "
			. "WHERE subject_id IN "
			. "		(SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id IN ";
		if ( !empty($level) ) {
			$sql .= "			(SELECT level_id FROM {$wpdb->prefix}js_grade_level "
				. "			WHERE is_active = 1 AND group_slug = '" . $slug . "' AND level_id = " . $level . ") ) ";
		} else {
			$sql .= "			(SELECT level_id FROM {$wpdb->prefix}js_grade_level "
				. "			WHERE is_active = 1 AND group_slug = '" . $slug . "') ) ";
		}
		$sql .= "AND is_active = 1 ";

		$sql .= "ORDER BY subject_name ASC";

		return $wpdb->get_results( $sql , 'ARRAY_A' );
	}

	public function getSubjectsById( $level ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}js_subjects "
			. "WHERE subject_id IN "
			. "	(SELECT subject_id FROM {$wpdb->prefix}js_subject_levels WHERE level_id = " . $level. ") "
			. "AND is_active = 1 "
			. "ORDER BY subject_name ASC" , 'ARRAY_A' );
	}

	public function getSubjectById( $id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}js_subjects "
			. "WHERE subject_id = " . $id );
	}

	public function getTopics( $id ) {
		global $wpdb;
		return $wpdb->get_results( "SELECT topic.*, topic_subject.topic_subject_id, topic_subject.subject_id FROM {$wpdb->prefix}js_topics AS topic "
			. "JOIN {$wpdb->prefix}js_topic_subjects AS topic_subject ON topic.topic_id = topic_subject.topic_id "
			. "WHERE topic_subject.subject_id = " . $id . " "
			. "AND topic.is_active = 1 ORDER BY topic.topic_name ASC", 'ARRAY_A' );
	}

	public function getTopicById( $id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT topic.*, topic_subject.topic_subject_id, topic_subject.subject_id FROM {$wpdb->prefix}js_topics AS topic "
			. "JOIN {$wpdb->prefix}js_topic_subjects AS topic_subject ON topic.topic_id = topic_subject.topic_id "
			. "WHERE topic.topic_id = " . $id, 'ARRAY_A' );
	}

	private function request($url) {
		try {
			$response = wp_remote_get($url, $this->getRequestArguments());
			if ( !is_wp_error($response) && $response['response']['code'] == 200 ) {
				return json_decode($response['body']);
			} else {
				return null;
			}
		} catch (HttpException $ex){
			return $ex;
		}
	}

	public function getRequestArguments() {
		// Save access token with timestamp (using "expires_in") since it is valid for 5mins
		// Check timestamp with current time
		// If expired token based on saved timestamp, request for new access token
		$accessToken = get_option( 'brightcove_cms_api_access_token' );
		$accessTokenExpiration = get_option( 'brightcove_cms_api_access_token_expiration' );
		if ( !empty($accessToken) && !empty($accessTokenExpiration) && (int)$accessTokenExpiration > time() ) {
			return array( 
	            'headers' => array(
	                'Authorization' => 'Bearer ' . $accessToken, 
	                'Content-Type' => 'application/json'
	            )
        	);
		} else {
			$authString = $this->options['js_topic_manager_brightcove_client_id'] . ':' . $this->options['js_topic_manager_brightcove_client_secret'];
			try {
				$authArgs = array( 
		            'headers' => array(
		                'Authorization' => 'Basic ' . base64_encode($authString), 
		                'Content-Type' => 'application/x-www-form-urlencoded'
		            ),
		            'body' => 'grant_type=client_credentials'
	        	);
				$response = wp_remote_post($this->authUrl, $authArgs);
				if ($response['response']['code'] == 200) {
					$data = json_decode($response['body']);
					$token = $data->access_token;
					$expiration = time() + $data->expires_in;
					update_option( 'brightcove_cms_api_access_token', $token );
					update_option( 'brightcove_cms_api_access_token_expiration', $expiration );
					return array( 
			            'headers' => array(
			                'Authorization' => 'Bearer ' . $token, 
			                'Content-Type' => 'application/json'
			            )
		        	);
				} else {
					return null;
				}
			} catch (HttpException $ex) {
				return null;
			}
		}
	}
}
