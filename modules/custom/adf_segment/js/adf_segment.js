(function ($) {

  Drupal.behaviors.adf_segment_events = {

    attach: function (context, settings) {

      //Enable cross domain
      var userLogin = false;
      if (drupalSettings.user.uid !== 0) {
        userLogin = true;
      }

      /* Make the first page call to load the integrations. If
       you'd like to manually name or tag the page, edit or
       move this call however you'd like.*/
      analytics.page({userLogin: userLogin});

      // Segment User
      var user = drupalSettings.adf_segment.user;
      userLogin = user.userLogin;
      delete user.userLogin;

      if (userLogin === true) {
        var userId = user.userId;
        delete user.userId;
        // We register the user that is visiting the page
        analytics.identify(userId, user);
      }

      var userChangeCurrentAccount = user.userChangeCurrentAccount;
      delete user.userChangeCurrentAccount;

      // An option is created to send identify custom
      if (user.custom) {
        analytics.identify(user.userId, user.custom);
      }

      /**
       * Get custom click track
       */
      $(context).on('click', '.segment-click', function () {
        addTrack($(this));
      });

      /**
       * Send track after load page, if your properties need angular or other framework data use segment-load
       * class and data-segment-load attribute
       */
      $('.segment-send').each(function () {
        addTrack($(this));
      });

      /**
       * Listen and get custom loaded track
       */
      var el = $('.segment-load');
      Array.prototype.forEach.call(el, function (el) {
        var disconect = false;
        var element = el;
        var observer = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            if (mutation.attributeName == 'data-segment-load') {
              var target = mutation.target.attributes;

              if(target.getNamedItem('data-segment-event')) {
                var event = (target.getNamedItem('data-segment-event') !== null && target.getNamedItem('data-segment-event') !== undefined) ? target.getNamedItem('data-segment-event').value : '';
                var properties = (target.getNamedItem('data-segment-properties') !== null && target.getNamedItem('data-segment-properties') !== undefined) ? target.getNamedItem('data-segment-properties').value : '';
                var options = (target.getNamedItem('data-segment-options') !== null && target.getNamedItem('data-segment-options') !== undefined) ? target.getNamedItem('data-segment-options').value : '';
                var callback = (target.getNamedItem('data-segment-callback') !== null && target.getNamedItem('data-segment-callback') !== undefined) ? target.getNamedItem('data-segment-callback').value : '';

                sendSegment(event, properties, options, callback);
                disconect = true;
              }

              if(target.getNamedItem('data-segment-ident')) {
                var ident = (target.getNamedItem('data-segment-ident') !== null && target.getNamedItem('data-segment-ident') !== undefined) ? target.getNamedItem('data-segment-ident').value : '';
                var trait = (target.getNamedItem('data-segment-trait') !== null && target.getNamedItem('data-segment-trait') !== undefined) ? target.getNamedItem('data-segment-trait').value : '';
                var options_ident = (target.getNamedItem('data-segment-options-ident') !== null && target.getNamedItem('data-segment-options-ident') !== undefined) ? target.getNamedItem('data-segment-options-ident').value : '';
                var callback_ident = (target.getNamedItem('data-segment-callback-ident') !== null && target.getNamedItem('data-segment-callback-ident') !== undefined) ? target.getNamedItem('data-segment-callback-ident').value : '';

                sendSegment(null, trait, options_ident, callback_ident, ident);
              }

            }
          });
        });

        observer.observe(element, {attributes: true});

        //Stop watching
        if (disconect !== false) {
          observer.disconnect();
        }
      });

      /**
       * Getting the event name, properties and options of element
       *
       * @param element the element containing the data
       */
      function addTrack(element) {
        var condition = element.attr('data-segment-condition');

        if (condition == 0 || condition === undefined && element.attr('data-segment-event')) {
          var eventName = element.attr('data-segment-event');
          var properties = element.attr('data-segment-properties');
          var options = element.attr('data-segment-options');
          var callback = element.attr('data-segment-callback');

          sendSegment(eventName, properties, options, callback);
        }

        if(condition == 0 || condition === undefined && element.attr('data-segment-ident')) {
          var ident = element.attr('data-segment-options');
          var trait = element.attr('data-segment-trait');
          var options_ident = element.attr('data-segment-options-ident');
          var callback_ident = element.attr('data-segment-callback-ident');

          sendSegment(ident, trait, options_ident, callback_ident);
        }

        if(element.attr('data-segment-event-alt') !== undefined) {
          var options_alt = element.attr('data-segment-options-alt');
          var properties_alt = element.attr('data-segment-properties-alt');
          var eventName_alt = element.attr('data-segment-event-alt');
          var callback_alt = element.attr('data-segment-callback-alt');

          sendSegment(eventName_alt, properties_alt, options_alt, callback_alt);
        }
      }

      /**
       * Send the data for a track to segment
       *
       * @param eventName the name of event to add the track
       * @param properties the properties to add the track
       * @param options the optons to add the track
       * @param callback the callback function to add the track
       */
      function sendSegment(eventName, properties, options, callback, ident) {
        var segmentProperties = {};
        var segmentOptions = {};
        var segmentCallback = "";

        if (undefined !== properties && '' !== properties && properties != null) {
          segmentProperties = JSON.parse(properties);
        }
        if (undefined !== options && '' !== options && options != null) {
          segmentOptions = JSON.parse(options);
        }
        if (undefined !== callback && '' !== callback && callback != null) {
          segmentCallback = callback;
        }

        if (eventName !== '' && undefined !== eventName && eventName != null) {
          analytics.track(eventName, segmentProperties, segmentOptions, segmentCallback);
        }

        if(ident !== '' && undefined !== ident && ident != null) {
          analytics.identify(ident, segmentProperties, segmentOptions, segmentCallback);
        }
      }

    }
  };
})(jQuery);