myApp.factory('batchEndpoints', function() {
  return {
    init: '/tigoapi_batch_rest_resource?_format=json',
    step: '/tigoapi/batch/',
    result: '/tigoapi/batch/get_mobile_info/{{batch_key}}/2?_format=json'
  };
});


myApp.service('tokenService', function($http, $q) {
  var deferred = $q.defer();

  this.getToken = function () {
    return $http.get('/session/token')
      .then(function (response) {
        // promise is fulfilled
        deferred.resolve(response.data);
        // promise is returned
        return deferred.promise;
      }, function (response) {
        // the following line rejects the promise
        deferred.reject(response);
        // promise is returned
        return deferred.promise;
      });
  };
});

myApp.factory("dataCollector", function(){
  var steps = [];
  var data = [];

  var interfaz = {
    setSteps: function(datatosave){
      steps = datatosave;
    },
    getSteps: function(){
      return steps;
    },
    addData: function(datatosave){
      data = datatosave;
    },
    getData: function () {
      return data;
    },
  };
  return interfaz;
});

myApp.run(['$rootScope',function($rootScope) {
  $rootScope.apiBatchData= {};
}]);

myApp.factory("apiBatch", function($http, $q, tokenService, batchEndpoints, dataCollector){

  var apiBatch = function(batch_name, search_key){
    this.batch_name = batch_name;
    this.search_key = search_key;
    this.batch_setting = '';
  };

  apiBatch.prototype.init = function () {
    var self = this;

    tokenService.getToken().then(
      function(token){
        var req = {
          method: 'POST',
          url: batchEndpoints.init,
          headers: {
            'X-CSRF-Token': token
          },
          data: {batch_name: self.batch_name, key: self.search_key}
        };
        $http(req)
          .then(
            function (response) {
              self.batch_setting = response.data;
							if (response.data.error) {
								self.result(response.data);
							} else {
								self.batch_hash = self.batch_setting.response.hash;
								self.prepare();
              }
            },
            function (response) {
              self.batch_setting = response.data;
            }
          );
      },
      function(error){
        console.log(error.statusText);
      }
    );
  };

  apiBatch.prototype.prepare = function () {
    var self = this;
    var steps = [];
    angular.forEach(self.batch_setting.response.steps, function(value, key){
      //console.log(value);
      var url = $http.get(batchEndpoints.step + self.batch_name + "/" + self.batch_setting.response.hash + "/" + (key) + "?_format=json");
      steps.push(url);
      /*
       $http.get(url)
       .then(
       function(step_response){
       console.log(step_response);
       angular.forEach(step_response.data.response.steps, function(step, key_step){
       if(step.status == 'pending'){
       return;
       }
       })
       console.log("Termino");
       },
       function(response){
       console.log(error.statusText);
       }
       );
       */
    })
    //console.log(steps);
    self.calls = steps;
    self.process();
  }

  apiBatch.prototype.process = function () {
    var self = this;
    $q.all(self.calls).then(function(results) {
      self.result('');
    }, function (error) {
      console.log(error.statusText);
    });

  }

  apiBatch.prototype.result = function (error) {
    if (error != '') {
			dataCollector.addData(error);
    } else {
			var self = this;
			$http.get('/tigoapi/batch/'+self.batch_hash+'/'+self.batch_name+'?_format=json').
			then(function(response){
				dataCollector.addData(response.data);
				//$rootScope.apiBatchData = response.data
				//console.log(response);
			})
    }
  };

  return apiBatch;

});
