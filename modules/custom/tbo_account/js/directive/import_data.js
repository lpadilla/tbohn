myApp.directive('ngImportData', ['$http', ngImportLog]);


function ngImportLog($http) {
  var directive = {
    restrict: 'EA',
    controller: ImportLogController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope, el, attr, ctrl) {

    var config = drupalSettings.b2bBlock[scope.uuid];
    //console.log(config);
    retrieveInformation(scope, config, el);

  }

  function retrieveInformation(scope, config, el) {

    var parameters = {};

    var data = {
      params: parameters,
      headers: {'Accept': 'application/json'}
    };

    $http.get(config.url, data).then(
        function (response) {
          scope.record_success = response.data.record_success;
          scope.record_fail = response.data.record_fail;

          scope.import = response.data.finish;
          if (scope.import == 1) {
            jQuery('#getLogImport').removeClass('disabled');
          }

          if(scope.import == 0) {
            jQuery('.caret').addClass('disabled');
            jQuery('.select-dropdown').attr('disabled', 'disabled');
          }
        }, function () {
          console.log('Error obteniendo datos');
        }
    );
  }
}

ImportLogController.$inject = ['$scope', '$http', '$location'];

function ImportLogController($scope, $http, $location) {
  $scope.file_upload_name = "";
  $scope.isValidFileExt = function() {
    var i_parent = jQuery('#edit-submit-data').parent('i');
    if($scope.file_upload_name.substr(-4) === '.csv') {
      i_parent.removeClass('disabled');
    }
    else{
      i_parent.addClass('disabled');
    }
    return $scope.file_upload_name.substr(-4) === '.csv';
  };


  $scope.downloadLogDetails = function (typeFile) {

    if (typeFile != "" && typeFile !== undefined) {
      var url = $location.protocol() + '://' + $location.host() + "/tbo-account/export-log?type-file=" + typeFile;
      window.open(url, "_blank");
    }
  };

 }