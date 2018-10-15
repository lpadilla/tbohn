myApp.service('multipleInvoices', function () {
  var stringValue = 'test string value';
  var objectValue = {
    data: 'test object value'
  };

  return {
    getString: function () {
      return stringValue;
    },
    setString: function (value) {
      stringValue = value;
    },
    getType: function () {
      return stringType;
    },
    setType: function (value) {
      stringType = value;
    },
    setObject: function (value) {
      objectValue.data = value;
    },
    getObject: function () {
      return objectValue;
    },
    getOrigin: function () {
      return originValue;
    },
    setOrigin: function (value) {
      originValue = value;
    },
    getTotal: function () {
      return total_pending;
    },
    setTotal: function (value) {
      total_pending = value;
    }
  }
});