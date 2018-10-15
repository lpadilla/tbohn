myApp.directive('ngCompleteEnter', ['$http', ngCompleteEnter]);


function ngCompleteEnter($http){
  var directive = {
    restrict: 'EA',
    controller: completeEnterController,
    link: linkFunc
  };

  return directive;

  function linkFunc(scope,el){

    scope.suggestions=[];
    scope.selectedIndex=-1; //currently selected suggestion index

    //load data
    scope.search = function(key_data){
      scope.dataSearch = scope[key_data];
      document.getElementById('suggestions').style.display = 'block';
      $http.get('/tboapi/complete-enterprises?_format=json&autocomplete='+scope[key_data]).success(function (data) {
        scope.suggestions = data;
        scope.selectedIndex = -1;
        //validate if user not select the option add the id for validate
        scope.suggestions.forEach(function(value, index){
          if(scope[key_data].toUpperCase() == value.name || scope[key_data].toLowerCase() ==  value.name){
            scope.enter_value = value.id;
            scope.suggestions = [];
          }
        })
      });
    };

    //events to automplete
    scope.checkKeyDown = function(event, field){
      if(event.keyCode===40){//down key, increment selectedIndex
        event.preventDefault();
        if(scope.selectedIndex+1 !== scope.suggestions.length){
          scope.selectedIndex++;
        }
      }
      else if(event.keyCode===38){ //up key, decrement selectedIndex
        event.preventDefault();
        if(scope.selectedIndex-1 !== -1){
          scope.selectedIndex--;
        }
      }
      else if(event.keyCode===13){ //enter pressed
        scope.resultClicked(scope.selectedIndex, field);
      }
      else {
        scope.suggestions=[];
      }
    };

    scope.resultClicked=function(index, field){
      scope[field] = scope.suggestions[index].name;
      scope.enter_value = scope.suggestions[index].id;
      scope.suggestions=[];
    };

    scope.closeSuggestions = function () {
      document.getElementById('suggestions').style.display = 'none';
    };

  }
}
completeEnterController.$inject = ['$scope', '$http'];
function completeEnterController ($scope, $http){
}
