/**
 * @file
 * Implements query technical_support directive.
 */

myApp.directive('ngNotification', ['$http', ngNotification]);

function ngNotification($http) {

  var directive = {
    restrict: 'EA',
    controller: NotificationController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    // Declare variables.
    scope.config_notifications = drupalSettings.notificationBlock[scope.uuid_data_ng_notification];
    scope[scope.config_notifications['uuid']] = scope.config_notifications;
    scope.quantity_notification = scope.config_notifications['quantity_notification'];
    scope.open_modal_verified = scope.config_notifications['open_modal_verified'];
    scope.loadModal = 0;
    scope.without_verified = 1;
    scope.show_mesagge_notification = Drupal.t("Ha ocurrido un error, por favor intente de nuevo más tarde");

    // Get data.
    //retrieveInformation(scope, scope.config, el);

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };

    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        if (scope.TechnicalSupportList.error) {
          jQuery("div.actions", el).hide();
        }

        jQuery('.messages .close').on('click', function () {
          jQuery('.main-top .messages').hide();
        });

        if (scope.loadModal == 0) {
          if (scope.open_modal_verified) {
            scope.loadModal = 1;
            jQuery("#verified_account_init").trigger("click");
          }
        }
      }
    });

  }

  // Function por retrieve information in fixed.
  function retrieveInformation(scope, config, el) {
    scope.result = config.config_type;

    if (scope.resources.indexOf(config.url) == -1) {
      // Add key for this display.
      var parameters = {};
      parameters['config_columns'] = config.config_columns;
      var config_data = {
        params: parameters,
        headers: {'Accept': 'application/json'}
      };
      $http.get(config.url, config_data)
      .then(function (resp) {
        if (resp.data.error) {
          scope.show_mesagge_data_technical = resp.data.message;
          scope.queryTechnicalSupportMessage();
        }
        else if (resp.data[0] && resp.data[0] == 'empty') {
          scope.data_empty_rest = true;
        }
        else {
          // Set result.
          scope.queryTechnicalSupportAll = resp.data.fixed;

          // Order date.
          scope.orderDate();

          // Assign result.
          scope.queryTechnicalSupport['data'] = scope.queryTechnicalSupportAll;

          scope.queryTechnicalSupport = resp.data;
          scope.alldata = resp.data;
          scope.alldata_backup = resp.data;

          // Scroll.
          scope.technical_support = scope.scroll();
          scope.auxScroll = resp.data;

          // Load filters.
          scope.loadFilters();
        }
        jQuery(el).parents("section").fadeIn('slow');
      }, function () {
        console.log("Error obteniendo los datos");
      });
    }
  }
}

// Controller.
NotificationController.$inject = ['$scope', '$http'];

function NotificationController($scope, $http) {
  // Init vars.
  if (typeof $scope.TechnicalSupportList == 'undefined') {
    $scope.TechnicalSupportList = "";
    $scope.TechnicalSupportList.error = false;
  }

  if (typeof $scope.resources == 'undefined') {
    $scope.resources = [];
  }

  var package = {};

  // Add config to url.
  var config_data = {
    params: package,
    headers: {'Accept': 'application/json'}
  };

  // Function to save audit log.
  $scope.sendNotification = function (uuid, notification_id, send_verified, counter, $event) {
    if (send_verified == 1) {
      $event.preventDefault();
    }

    config = $scope.config_notifications = drupalSettings.notificationBlock[$scope.uuid_data_ng_notification];
    var params = {
      'notification_id': notification_id,
      'send_verified': send_verified
    };

    if ($scope.resources.indexOf(config.url) == -1) {
      $http.get('/rest/session/token').then(function (resp) {
        $http({
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': resp.data
          },
          data: params,
          url: config.url
        }).then(function successCallback(response) {
          if (response.data === "OK") {
            // Validate if remove notification.
            if (uuid == 0 && notification_id == 0) {
              // No action. Save audit log.
            }
            else if (send_verified == 0) {
              $scope.hiddenNotification(counter);
            }
            else {
              jQuery("#last_send_verified").trigger("click");
              $scope.without_verified = 0;
            }
          }
          else if (response.data.error) {
            $scope.show_mesagge_notification = response.data.message;
            $scope.notificationAlertMessage();
          }
        }, function errorCallback(response) {
          $scope.notificationAlertMessage();
        });
      });
    }
  }

  /**
   * Hidden notification.
   *
   * @param counter
   */
  $scope.hiddenNotification = function (counter) {
    jQuery('#notification_' + counter).hide();
    $scope.quantity_notification = $scope.quantity_notification - 1;
  }

  /**
   * Show error message.
   */
  $scope.notificationAlertMessage = function () {
    jQuery(".block-notification .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_notification + '</p></div>');
    $html_mensaje = jQuery('.block-notification .messages-only').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger" role="contentinfo" aria-label="">' + $html_mensaje + '</div>');

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });
  }

}

/* Ocultar y mostrar div */
jQuery(document).ready(function(){
	jQuery('#notification-show').on('click',function(){
		jQuery('#not_dropdown').toggle();
	});

		jQuery("#not_dropdown").hover(function(){
			jQuery(this).show();
		}, function () {
			jQuery(this).hide();
		});


});

