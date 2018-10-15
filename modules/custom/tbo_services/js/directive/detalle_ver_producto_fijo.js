/**
 * @file
 * Implements directive to fixed product.
 */

myApp.directive('ngDetalleVerProductoFijo', ['$http', ngDetalleVerProductoFijo]);

function ngDetalleVerProductoFijo($http) {

  var directive = {
    restrict: 'EA',
    controller: detalleVerProductoFijoController,
    link: linkFunc
  }

  return directive;

  function linkFunc(scope, el, attr, ctrl) {
    var config = drupalSettings.b2bBlock[scope.uuid_data_ng_detalle_ver_producto_fijo];
    scope.product_id_product_fixed = config['product_id'];
    scope.detalle_uuid = scope.uuid;

    retrieveInformation(scope, config, el);
  }

  function retrieveInformation(scope, config, el) {
    $http({
      'method': 'GET',
      'url': config.url,
      'params': {isData: 1}
    }).then(function (response) {
      if (response.data.error) {
        scope.show_mesagge_data_detalle_ver_producto_fijo = response.data.message;
        scope.alertas_servicios_detalle_ver_producto_fijo();
      }
      else {
        // Card $var's.
        scope.plan = response.data.card.plan;
        scope.title_detalle = response.data.card.category;
        scope.address = response.data.card.address;
        scope.status = response.data.card.status;
        scope.contract = response.data.card.contractId;
        scope.service_type = response.data.card.serviceType;

        // Details $var's.
        var details = response.data.details;
        scope.id_device = details.id;
        scope.serial = details.serial;
        scope.equipment = details.equipo;
        scope.date = details.date;

        // Set class for state.
        if (scope.status == 'Activo') {
          scope.status_class = 'activo';
        }
        else if (scope.status == 'Inactivo') {
          scope.status_class = 'inactivo';
        }
        else {
          scope.status_class = 'suspendido';
        }

        // Set class for block image.
        scope.class_img = scope.title_detalle.toLowerCase().replace(' ', '-').replace(/í/g, 'i').replace(/ó/g, 'o');
      }
    });
  }

}

detalleVerProductoFijoController.$inject = ['$scope', '$http'];

function detalleVerProductoFijoController($scope, $http) {
  $scope.saveLog = function () {
    var config = drupalSettings.b2bBlock[$scope.detalle_uuid];
    $http({
      'method': 'GET',
      'url': config.url,
      'params': {detailLog: 1}
    });
  };

  // Show message service.
  $scope.alertas_servicios_detalle_ver_producto_fijo = function () {
    jQuery(".block-detalle-ver-producto-fijo .messages-only .text-alert").append('<div class="txt-message"><p>' + $scope.show_mesagge_data_detalle_ver_producto_fijo + '</p></div>');
    $html_mensaje = jQuery('.block-detalle-ver-producto-fijo .messages-only ').html();
    jQuery('.main-top').append('<div class="messages clearfix messages--danger alert alert-danger">' + $html_mensaje + '</div>');

    jQuery(".block-detalle-ver-producto-fijo .messages-only .text-alert .txt-message").remove();

    jQuery('.messages .close').on('click', function () {
      jQuery('.messages').hide();
    });
  }

}
