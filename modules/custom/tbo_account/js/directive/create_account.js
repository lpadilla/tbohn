/**
 * @file
 * Implements directive ngCreateAccount.
 */

myApp.directive('ngCreateAccount', ['$http', ngCreateAccount]);

function ngCreateAccount($http) {
  return directive = {
    restrict: 'EA',
    link: linkFunc
  };

  function linkFunc(scope, el, attr, ctrl) {
    scope.document_type = drupalSettings.docType;
    scope.service_type = drupalSettings.serviceType;

    // Validar número de documento.
    scope.validateTypeNumber = function () {
      if (scope.document_type != 'nit') {
        jQuery('.field-document-number label').text(scope.label_doctype);
      }
      else {
        jQuery('.field-document-number label').text(scope.label_doctype_nit);
      }
    };

    scope.$watch('document_number', function (document_number) {
      var reg_ex = /[^0-9]+/g;

      if (scope.document_number !== undefined && scope.document_number !== '') {
        scope.document_number = document_number.toString().replace(new RegExp(reg_ex), '');
      }
    });

    scope.$watch('referent_payment', function (referent_payment) {
      var reg_ex = /[^a-zA-Z0-9-]+/g;

      if (scope.referent_payment !== undefined && scope.referent_payment !== '') {
        scope.referent_payment = referent_payment.toString().replace(new RegExp(reg_ex), '');
      }
    });

    scope.$watch('contract_number', function (contract_number) {
      var reg_ex = /[^a-zA-Z0-9-]+/g;

      if (scope.contract_number !== undefined && scope.contract_number !== '') {
        scope.contract_number = contract_number.toString().replace(new RegExp(reg_ex), '');
      }
    });

    /**
     * Validar formulario
     */
    scope.validateForm = function () {
      var status = false;
      if (scope.document_number !== undefined && scope.document_number !== '') {
        if ((scope.referent_payment !== undefined && scope.referent_payment !== '' && scope.service_type == 'mobile')
          || (scope.contract_number !== undefined && scope.contract_number !== '' && scope.service_type == 'fixed')) {
          status = true;
        }
      }

      if (status) {
        jQuery('.form-create-account .buttons-wrapper i').removeAttr('disabled');
      }
      else {
        jQuery('.form-create-account .buttons-wrapper i').attr('disabled','disabled');
      }
    };

    scope.validateForm();
  }
}
