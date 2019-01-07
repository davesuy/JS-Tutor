(function( $ ) {
	'use strict';

  function getParameterByName(name) {
    name = name.replace(/[\[\]]/g, "\\$&");
    var url = window.location.href,
      regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }

  /**
   * Education Levels, Subjects, topics
   */

	$('.wrap.js-topic-manager #topic-levels select').on('change', function() {
		var subjectEl = $('.wrap.js-topic-manager #topic-subjects select'),
			options = 
        {
          type: 'GET',
          url: js_topic_manager.ajax_url,
          data: {
            action: 'js_topic_manager_load_subjects',
            level_ids: $(this).val()
          }
        };

        // Disable element while loading values
        subjectEl.addClass('disable');

        $.ajax(options).done(function (response) {
          if (response != null && response !=0 && response.length > 0) {
            subjectEl.replaceWith(response);
          }
        });
	});

  // Update selected education level in subjects list page
  $('.wrap.js-topic-manager select[name=subject_level]').on('change', function() {
    document.location.search = ( '?page=js_subject&level=' + this.value );
  });

  // Update selected education level in topics list page
  $('.wrap.js-topic-manager select[name=topic_level]').on('change', function() {
    document.location.search = ( '?page=js_topics&level=' + this.value );
  });

  // Update selected subject in topics list page
  $('.wrap.js-topic-manager select[name=topic_subject]').on('change', function() {
    var level = getParameterByName('level');
    if (!level) {
      level = $('.wrap.js-topic-manager select[name=topic_level]').val();
    }
    document.location.search = ( '?page=js_topics&level=' + level + '&subject=' + this.value );
  });

  /**
   * Related Videos
   */

  // Load videos under grade level, subject, topic by BrightCove tag
  var loadRelatedVideosTimeout,
    resetRelatedVideoSelectors = function() {
      $('.related-videos .primary-video').html('');
      $('.related-videos .related-video-1').html('');
      $('.related-videos .related-video-2').html('');
      $('.related-videos .related-video-3').html('');
    },
    enableDisableVideoSelectors = function(enable) {
      if (enable) {
        $('.related-videos .primary-video').removeClass('disabled');
        $('.related-videos .related-video-1').removeClass('disabled');
        $('.related-videos .related-video-2').removeClass('disabled');
        $('.related-videos .related-video-3').removeClass('disabled');
      } else {
        $('.related-videos .primary-video').addClass('disabled');
        $('.related-videos .related-video-1').addClass('disabled');
        $('.related-videos .related-video-2').addClass('disabled');
        $('.related-videos .related-video-3').addClass('disabled');
      }
    };
  var loadRelatedVideosList = function() {
    // Reset video selectors
    resetRelatedVideoSelectors();
    enableDisableVideoSelectors(false);

    if (loadRelatedVideosTimeout) {
      clearTimeout(loadRelatedVideosTimeout);
    }

    loadRelatedVideosTimeout = setTimeout(function() {
      var gradeLevel = $('.wrap.js-topic-manager .related-videos .grade-level').val(),
        subject = $('.wrap.js-topic-manager .related-videos .subject').val(),
        topic = $('.wrap.js-topic-manager .related-videos .topic').val();

      if (gradeLevel && subject && topic) {
        // Grade level tags
        var tagRequest = [
          { type: 'js_grade_level', id: parseInt(gradeLevel) },
          { type: 'js_subjects', id: parseInt(subject) },
          { type: 'js_topics', id: parseInt(topic) }
        ];
        // Send request
        if (tagRequest.length > 0) {
          $.ajax({
            type: 'GET',
            url: js_topic_manager.ajax_url,
            data: {
              action: 'js_topic_manager_admin_get_videos',
              data: tagRequest
            }
          }).done(function (videos) {
            videos = JSON.parse(videos);
            var primaryVideos = '<option value="">---Select Video---</option>',
              relatedVideos = '<option value="">---Select Video---</option>';
            if (videos && videos.primary && videos.primary.length > 0) {
              for (var i = 0; i < videos.primary.length; i++) {
                var v = videos.primary[i];
                primaryVideos += '<option value="' + v.id + '">' + v.name + '</option>';
              }
            }
            if (videos && videos.related && videos.related.length > 0) {
              for (var i = 0; i < videos.related.length; i++) {
                var v = videos.related[i];
                relatedVideos += '<option value="' + v.id + '">' + v.name + '</option>';
              }
            }
            $('.related-videos .primary-video').html(primaryVideos);
            $('.related-videos .related-video-1').html(relatedVideos);
            $('.related-videos .related-video-2').html(relatedVideos);
            $('.related-videos .related-video-3').html(relatedVideos);
            enableDisableVideoSelectors(true);
          });
        }
      } else {
        enableDisableVideoSelectors(true);
      }
    }, 1000);
  };

  $('.wrap.js-topic-manager .related-videos .grade-level').bind('change', function() {
    // Reset subjects dropdown
    $('.wrap.js-topic-manager .related-videos .subject').addClass('disabled');
    $('.wrap.js-topic-manager .related-videos .subject').html('');
    $('.wrap.js-topic-manager .related-videos .topic').addClass('disabled');
    $('.wrap.js-topic-manager .related-videos .topic').html('');
    // Sanity check
    var gradeLevel = $(this).val();
    if (gradeLevel) {
      // Load subjects based on selected grade level
      $.ajax({
        type: 'GET',
        url: js_topic_manager.ajax_url,
        data: {
          action: 'js_topic_manager_admin_load_subjects_select',
          level_id: gradeLevel
        }
      }).done(function (subjects) {
        if (subjects) {
          $('.wrap.js-topic-manager .related-videos .subject').html(subjects);
        }
        $('.wrap.js-topic-manager .related-videos .subject').removeClass('disabled');
        $('.wrap.js-topic-manager .related-videos .subject').trigger('change');
      });
    } else {
      $('.wrap.js-topic-manager .related-videos .subject').removeClass('disabled');
      $('.wrap.js-topic-manager .related-videos .topic').removeClass('disabled');
    }
  });

  $('.wrap.js-topic-manager .related-videos .subject').bind('change', function() {
    // Reset topics dropdown
    $('.wrap.js-topic-manager .related-videos .topic').addClass('disabled');
    $('.wrap.js-topic-manager .related-videos .topic').html('');
    var subject = $(this).val();
    if (subject) {
      // Load topics based on selected grade level
      $.ajax({
        type: 'GET',
        url: js_topic_manager.ajax_url,
        data: {
          action: 'js_topic_manager_admin_load_topics_select',
          subject_id: subject
        }
      }).done(function (topics) {
        if (topics) {
          $('.wrap.js-topic-manager .related-videos .topic').html(topics);
        }
        $('.wrap.js-topic-manager .related-videos .topic').removeClass('disabled');
        $('.wrap.js-topic-manager .related-videos .topic').trigger('change');
      });
    } else {
      $('.wrap.js-topic-manager .related-videos .topic').removeClass('disabled');
    }
  });

  $('.wrap.js-topic-manager .related-videos .topic').bind('change', function() {
    if ($(this).val()) {
      loadRelatedVideosList();
    } else {
      resetRelatedVideoSelectors();
    }
  });

  var selectVideo = function(el) {
      var video = $(el).val();
      if (video) {
        var videoName = $(el).find('option:selected').text().trim(),
          videoId = $(el).val();
        $(el).next('.selected-video').find('span.label').text(videoName);
        $(el).next('.selected-video').attr('id', videoId);
        $(el).next('.selected-video').show();
        $(el).hide();
      }
    },
    deselectVideo = function(el) {
      $(el).prev().text('');
      $(el).parent().attr('id', '');
      $(el).parent().hide();
      $(el).parent().prev().val('');
      $(el).parent().prev().show();
    };

  $('.related-videos .primary-video, .related-videos .related-video-1, .related-videos .related-video-2, .related-videos .related-video-3')
  .bind('change', function() {
    selectVideo(this);
  });

  $('.related-videos .related-video-1').bind('change', function() {
    selectVideo(this);
  });

  $('.related-videos .related-video-2').bind('change', function() {
    selectVideo(this);
  });

  $('.related-videos .related-video-3').bind('change', function() {
    selectVideo(this);
  });

  $('.related-videos .selected-video .remove').click(function() {
    deselectVideo(this);
  });

  // Request to save related videos
  $('.wrap.js-topic-manager .related-videos .save-related-video').click(function() {
    var primaryVideo = $('.related-videos .primary-video').next().attr('id'),
      primaryVideoName = $('.related-videos .primary-video').next().find('.label').text().trim(),
      relatedVideo1 = $('.related-videos .related-video-1').next().attr('id'),
      relatedVideo1Name = $('.related-videos .related-video-1').next().find('.label').text().trim(),
      relatedVideo2 = $('.related-videos .related-video-2').next().attr('id'),
      relatedVideo2Name = $('.related-videos .related-video-2').next().find('.label').text().trim(),
      relatedVideo3 = $('.related-videos .related-video-3').next().attr('id'),
      relatedVideo3Name = $('.related-videos .related-video-3').next().find('.label').text().trim();
    if (!primaryVideo) {
      alert('No primary video is set.');
      return;
    }
    if (!relatedVideo1 && !relatedVideo2 && !relatedVideo3) {
      alert('No related videos are set.');
      return;
    }
    var data = {
      primary_video: primaryVideo,
      primary_video_name: primaryVideoName,
      related_video_1: relatedVideo1,
      related_video_1_name: relatedVideo1Name,
      related_video_2: relatedVideo2,
      related_video_2_name: relatedVideo2Name,
      related_video_3: relatedVideo3,
      related_video_3_name: relatedVideo3Name
    };
    $.ajax({
      type: 'POST',
      url: js_topic_manager.ajax_url,
      data: {
        action: 'js_topic_manager_admin_save_related_videos',
        data: data
      }
    }).done(function (result) {
      result = JSON.parse(result);
      if (typeof(result) === 'object' && result.error === 'already_exists') {
        alert('Related videos for selected primary video is already set.');
      } else {
        window.location.reload();
      }
    });
  });

  // Delete related video
  $('.wrap.js-topic-manager .related-videos a.delete-video').click(function(e) {
    e.preventDefault();
    var confirmDelete = confirm('Are you sure you want to delete saved related video?');
    if (confirmDelete) {
      var id = $(this).closest('tr').attr('id');
      $.ajax({
        type: 'POST',
        url: js_topic_manager.ajax_url,
        data: {
          action: 'js_topic_manager_admin_delete_related_video',
          data: id
        }
      }).done(function () {
        window.location.reload();
      });
    }
  });


})( jQuery );