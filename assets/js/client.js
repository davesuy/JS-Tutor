(function( $ ) {



  function bindSubjectTopicChange(e) {

    e.preventDefault();

    var el = $('#video-library .ui-accordion input[name="subject-topic"]:checked');

    if (el && el.length > 0) {

      $('#video-library a.btn-next').addClass('active');

    } else {

      $('#video-library a.btn-next').removeClass('active');

    }

  }



  function bindSubjectTopicAccordion(e) {

    if (e.target && e.target.type === 'radio') return;

    var topicsEl = $(this).next();

    if ( $(topicsEl).is(':visible') ) {

      $(topicsEl).slideUp();

    } else {

      $(topicsEl).slideDown();

    }

  }



  $(window).resize(function(){

      $("#accordion").accordion("refresh");

  });



  // Initialize accordion

  $('#accordion').accordion({ 

    heightStyle: 'content',

    collapsible: true,

    icons: false,

    active: 0

  });



  // Subject topics accordion

  $('.subject-topic-accordion > .subject').bind('click', bindSubjectTopicAccordion);



  // Remove checked radio option

  $("#video-library .ui-accordion input[type=radio]").prop('checked', false);



  // Update subjects based on selected grade level

  $('#video-library .ui-accordion select[name=grade_level]').on('change', function() {

    var options = {

        type: 'GET',

        url: js_topic_manager.ajax_url,

        data: {

          action: 'js_topic_manager_client_load_subjects_list',

          level_id: parseInt(this.value)

        }

      };



    $(this).addClass('disable');

    $(this).next().addClass('disable');

    $(this).parent().next().addClass('disable');



    $.ajax(options).done(function (response) {

      if (response != null && response !=0 && response.length > 0) {

        var el = $('#video-library .ui-accordion select[name=grade_level]');

        el.parent().parent().next().replaceWith(response);

        el.removeClass('disable');

        el.next().removeClass('disable');

        setTimeout(function() {

          $('#video-library .ui-accordion input[name="subject-topic"]').on('change', bindSubjectTopicChange);

          $('.subject-topic-accordion > .subject').unbind('click', bindSubjectTopicAccordion);

          $('.subject-topic-accordion > .subject').bind('click', bindSubjectTopicAccordion);

        });

      }

    });

  });



  // Update topics based on selected grade level

  $('#video-library select[name=topics_level]').on('change', function() {

    // Hide topics selector

    $('#video-library select[name=topics_selector]').parent().hide();

    // Remove previously loaded topics

    $("#video-library.topics .filters").nextUntil('.btn-next').remove();



    var subjectEl = $('#video-library select[name=topics_subject]'),

      topicEl = $('#video-library select[name=topics_selector]'),

      options = {

        type: 'GET',

        url: js_topic_manager.ajax_url,

        data: {

          action: 'js_topic_manager_client_load_subjects',

          level_id: parseInt(this.value)

        }

      };



    if (!this.value) {

      subjectEl.parent().hide();

      return;

    } else {

      subjectEl.parent().show();

    }



    // Disable subject dropdown while loading subjects

    subjectEl.addClass('disable');

    topicEl.hide();

    $('#video-library .ui-accordion').hide();

    $('#video-library p.placeholder').hide();

    $('#video-library a.btn-next').hide();



    $.ajax(options).done(function (response) {

      if (response != null && response !=0 && response.length > 0) {

        subjectEl.replaceWith(response);

        setTimeout(function() {

          handleSubjectEvent();

        }, 100);

      }

    });

  });



  // Update topics based on selected subject

  var handleSubjectEvent = function() {

    $('#video-library select[name=topics_subject]').bind('change', function() {

      // Remove previously loaded topics

      $("#video-library.topics .filters").nextUntil('.btn-next').remove();

      // Handle event

      var topicEl = $('#video-library select[name=topics_selector]');

      topicEl.addClass('disable');

      $.ajax({

          type: 'GET',

          url: js_topic_manager.ajax_url,

          data: {

            action: 'js_topic_manager_client_load_topics',

            subject_id: parseInt(this.value)

          }

        }).done(function (response) {

        if (response != null && response !=0 && response.length > 0) {

          topicEl.parent().show();

          topicEl.replaceWith(response);

          setTimeout(function() {

            handleTopicEvent();

          }, 100);

        }

      });

    });

  };

  handleSubjectEvent();



  var handleTopicEvent = function() {

    // Update videos based on selected topic

    $('#video-library select[name=topics_selector]').on('change', function() {

      // Remove previously loaded topics

      $("#video-library.topics .filters").nextAll().remove();

      if (this.value) {

        // Handle event

        var level = $('#video-library select[name=topics_level]').val(),

          subject = $('#video-library select[name=topics_subject]').val(),

          search = '?l=' + level + '&j=' + subject;

        if (this.value) search += ('&t=' + this.value);

        document.location.search = search;

      }

    });

  };

  handleTopicEvent();



  // Enable subjects list 'next' button if a subject is selected

  $('#video-library .ui-accordion input[name="subject-topic"]').on('change', bindSubjectTopicChange);



  // Enable topics list 'next' button if a video is selected

  $('#video-library .ui-accordion input[name=video]').on('change', function() {

    $('#video-library .ui-accordion li').removeClass('active');

    $(this.closest('li')).addClass('active');



    var el = $('#video-library .ui-accordion input[name=video]:checked');

    if (el && el.length > 0) {

      $('#video-library a.btn-next').addClass('active');

    } else {

      $('#video-library a.btn-next').removeClass('active');

    }

  });



  // Go to appriate screen and reload

  $('#video-library a.btn-next').on('click', function() {

    var checkEl = $('#video-library .ui-accordion input[name="subject-topic"]:checked'),

      video = $('#video-library .ui-accordion input[name=video]:checked');



    if (checkEl && checkEl.length > 0) {

      var level = $(checkEl).attr('level'),

        search = '?l=' + level;



      if ($(checkEl).hasClass('subject')) {

        search += '&j=' + $(checkEl).val();

      } else if ($(checkEl).hasClass('topic')) {

        search += '&j=' + $(checkEl).attr('subject') + '&t=' + $(checkEl).val();

      }

      document.location.search = search;

    } else if (video && video.length > 0) {

      var path = window.location.origin + '/video/?v=' + $(video).val();

      window.location.href = path;

    } else {

      document.location.search = '';

    }

  });



  // Go to appriate screen and reload

  $('#video-library-navigation a.btn-back').on('click', function() {

    window.history.back()

  });



  // Video read more animation

  $('#video-library a.read-more').on('click', function() {

    var contentEl = $(this).parent().next();

    if ($(this).hasClass('active')) {

      contentEl.slideUp();

      $(this).removeClass('active');

    } else {

      contentEl.slideDown();

      $(this).addClass('active');

    }

  });



})( jQuery );