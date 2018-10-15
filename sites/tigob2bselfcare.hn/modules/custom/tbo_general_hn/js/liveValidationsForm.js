/**
 * @file
 * Custom scripts for theme.
 */

//validacion de numero celular
function validateFormatPhone(number) {
  number = number.replace(/\s/g, '');
  number = number.replace(/\D/g, '');
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 0) {
      aux_number = '(' + number[i];
    } else if (i == 3) {
      aux_number = aux_number + ') ' + number[i];
    } else if (i == 6) {
      aux_number = aux_number + '-' + number[i];
    } else {
      aux_number = aux_number + number[i];
    }
  }

  number = aux_number;
  var status = true;

  if (number.length > 14) {
    number = number.substring(0, 14);
  }

  if (number.length < 14 || number.substring(1, 2) != '3' || number.length > 14) {
    status = false;
  }

  response = {
    phone: number,
    status: status,
  };
  return response;
};

//validación tarjeta de credito, algoritmo de Luhn
function validateCreditCard(value) {
  if (/[^0-9-\s]+/.test(value)) return false;

  var nCheck = 0, nDigit = 0, bEven = false;
  value = value.replace(/\D/g, "");

  for (var n = value.length - 1; n >= 0; n--) {
    var cDigit = value.charAt(n),
      nDigit = parseInt(cDigit, 10);

    if (bEven) {
      if ((nDigit *= 2) > 9) nDigit -= 9;
    }

    nCheck += nDigit;
    bEven = !bEven;
  }

  return (nCheck % 10) == 0;
}

//validacion de numero celular
function validateFormatMonto(number) {

    number = number.replace(/\D/g, '');
    status_p = (number.length > 0) ? true : false;

    response = {
        monto: number,
        status: status_p,
    };
    return response;

};