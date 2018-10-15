myApp.directive('ngEditUserInfo', ['$http', ngEditUserInfo]);


function ngEditUserInfo($http) {
  var directive = {
    restrict: 'EA',
    controller: EditUserInfoController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    // var config = drupalSettings.b2bBlock[scope.uuid];
    var config = '';
    retrieveInformation(scope, config, el);
    var orderName = 0;
    var orderAdmin = 0;

    scope.apiIsLoading = function () {
      return $http.pendingRequests.length > 0;
    };
    scope.$watch(scope.apiIsLoading, function (v) {
      if (v == false) {
        jQuery(el).parents("section").fadeIn(400);
        /* if (scope.summary.error) {
         jQuery("div.actions", el).hide();
         }*/
      }
    });

    scope.formatPhone = function () {
      response = validateFormatPhone(scope.phoneNumber);
      scope.phoneNumber = response['phone'];
      scope.phoneStatus = response['status'];

      if (scope.phoneStatus == false) {
        jQuery('#edit-cel-number').removeClass('valid-phone');
        jQuery('#edit-cel-number').removeClass('valid');
        jQuery('#edit-cel-number').removeClass('error');
        jQuery('#edit-cel-number').removeClass('invalid-phone');
        jQuery('#edit-cel-number').addClass('invalid-phone');
        jQuery('#edit-cel-number').addClass('error');
      } else {
        jQuery('#edit-cel-number').removeClass('valid-phone');
        jQuery('#edit-cel-number').removeClass('valid');
        jQuery('#edit-cel-number').removeClass('error');
        jQuery('#edit-cel-number').removeClass('invalid-phone');
        jQuery('#edit-cel-number').addClass('valid-phone');
        jQuery('#edit-cel-number').addClass('valid');
      }
      scope.validateFormUser();
    }

    scope.validateFormUser = function () {
      var name_status = false;
      var doc_type_status = false;
      var doc_number_status = false;

      if (typeof scope.userName !== 'undefined' && scope.userName.length > 0) {
        name_status = true;
      }

      if (typeof scope.documentType !== 'undefined' && scope.documentType.length > 0) {
        doc_type_status = true;
      }

      if (typeof scope.documentNumber !== 'undefined' && scope.documentNumber.length > 0) {
        doc_number_status = true;
      }

      if (name_status && doc_type_status && doc_number_status && scope.phoneStatus) {
        jQuery('#submit-edit-user').parent().removeClass('disabled');
      } else {
        jQuery('#submit-edit-user').parent().addClass('disabled');
      }
    }

    //inicializar selects
    window.onload = function () {
      scope.formatPhone();
      jQuery('#edit-cel-number').scope().$apply();
    }

    function retrieveInformation(scope, config, el) {
    }
  }
}

EditUserInfoController.$inject = ['$scope', '$http'];

function EditUserInfoController($scope, $http) {
}