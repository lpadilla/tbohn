/**
 * @file
 * Custom scripts for theme.
 */

// Validacion de numero celular.
function validateFormatPhone(number) {
  number = number.replace(/\s/g, '');
  number = number.replace(/\D/g, '');
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 0) {
      aux_number = '(' + number[i];
    }
else if (i == 3) {
      aux_number = aux_number + ') ' + number[i];
    }
else if (i == 6) {
      aux_number = aux_number + '-' + number[i];
    }
else {
      aux_number = aux_number + number[i];
    }
  }

  number = aux_number;
  var status = true;
  var statusInit = true;

  if (number.length > 14) {
    number = number.substring(0, 14);
  }

  if (number.length < 14 || number.substring(1, 2) != '3' || number.length > 14) {

    status = false;
  }
	if ( number.substring(1, 2) != '3' ) {
  		statusInit = false;
	}

  response = {
    phone: number,
    status: status,
		status2: statusInit,
  };
  return response;
};

// Validation contract.
function validateFormatFixed(number){
	  number = number.replace(/\s/g, '');
    var aux_number = '';

    for (i = 0; i < number.length; i++) {
        if (i == 0) {
            aux_number = number[i];
        }
else {
            aux_number = aux_number + number[i];
        }
    }

    number = aux_number;
    var status = true;

    if (number.length > 30) {
        number = number.substring(0, 30);
    }

    if (number.length < 4 || number.length > 30) {
        status = false;
    }

    response = {
        phone: number,
        status: status,
    };
    return response;
};

// validación tarjeta de credito, algoritmo de Luhn.
function validateCreditCard(value) {
  if (/[^0-9-\s]+/.test(value)) {
return false;
  }

  var nCheck = 0, nDigit = 0, bEven = false;
  value = value.replace(/\D/g, "");

  for (var n = value.length - 1; n >= 0; n--) {
    var cDigit = value.charAt(n),
      nDigit = parseInt(cDigit, 10);

    if (bEven) {
      if ((nDigit *= 2) > 9) {
nDigit -= 9;
      }
    }

    nCheck += nDigit;
    bEven = !bEven;
  }

  return (nCheck % 10) == 0;
}

// Validacion de numero celular.
function validateFormatMonto(number) {

    number = number.replace(/\D/g, '');
    status_p = (number.length > 0) ? true : false;

    response = {
        monto: number,
        status: status_p,
    };
    return response;

};

// Validacion de numero celular por configuracion global.
function validateFormatCellPhone(number, format) {
  number = number.replace(/\s/g, '');
  number = number.replace(/\D/g, '');
  var aux_number = '';
  var type = true;
  var status = true;
  var statusInit = true;
  var length = number.length;

  // Validate mobile.
  if (number.substring(0, 1) == '3') {
    if (format == 1) {
      aux_number = validateFormatCellPhoneFormat1(number);
    }
    else if (format == 2) {
      aux_number = validateFormatCellPhoneFormat2(number);
    }
    else if (format == 3) {
      aux_number = validateFormatCellPhoneFormat3(number);
    }
    else if (format == 4) {
      aux_number = validateFormatCellPhoneFormat4(number);
    }
    type = 'mobile';
  }
  else if (number.substring(0, 1) == '5') {
    aux_number = validateFormatFixedPhone(number);
    type = 'fixed';
  }
  else {
    status = false;
    statusInit = false;
  }

  number = aux_number;

  if (type == 'mobile' && (format == 1 || format == 4) && length > 10) {
    number = number.substring(0, 14);
  }

  if (type == 'mobile' && (format == 2 || format == 3) && length > 10) {
    number = number.substring(0, 12);
  }

  if (type == 'fixed' && length > 10) {
    number = number.substring(0, 17);
  }

  if ((type == 'mobile' && length < 10) || (type == 'fixed' && length < 10)) {
    status = false;
  }

  response = {
    phone: number,
    status: status,
    status2: statusInit,
    length: length,
    type: type
  };
  return response;
};

// Validacion de numero celular format '1' => '(301) 100 1010',
function validateFormatCellPhoneFormat1(number) {
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 0) {
      aux_number = '(' + number[i];
    }
    else if (i == 3) {
      aux_number = aux_number + ') ' + number[i];
    }
    else if (i == 6) {
      aux_number = aux_number + ' ' + number[i];
    }
    else {
      aux_number = aux_number + number[i];
    }
  }

  return aux_number;
};

// Validacion de numero celular format '2' => '301 100 1010',
//  '3' => '301-100-1010',
//  '4' => '(301) 100-1010',.
function validateFormatCellPhoneFormat2(number) {
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 3 || i == 6) {
      aux_number = aux_number + ' ' + number[i];
    }
    else {
      aux_number = aux_number + number[i];
    }
  }

  return aux_number;
};

// Validacion de numero celular format '3' => '301-100-1010',
function validateFormatCellPhoneFormat3(number) {
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 3 || i == 6) {
      aux_number = aux_number + '-' + number[i];
    }
    else {
      aux_number = aux_number + number[i];
    }
  }

  return aux_number;
};

// Validacion de numero celular format '4' => '(301) 100-1010'.
function validateFormatCellPhoneFormat4(number) {
  var aux_number = '';

  for (i = 0; i < number.length; i++) {
    if (i == 0) {
      aux_number = '(' + number[i];
    }
    else if (i == 3) {
      aux_number = aux_number + ') ' + number[i];
    }
    else if (i == 6) {
      aux_number = aux_number + '-' + number[i];
    }
    else {
      aux_number = aux_number + number[i];
    }
  }

  return aux_number;
};

// Validacion de numero fijo.
function validateFormatFixedPhone(number) {
  var aux_number = '';
  var length = number.length;
  for (i = 0; i < length; i++) {
    if (i == 0) {
      aux_number = '(' + number[i];
    }
    else if (i == 1) {
      aux_number = aux_number + number[i] + ')';
    }
    else if (i == 2) {
      aux_number =  aux_number + ' (' + number[i];
    }
    else if (i == 3) {
      aux_number =  aux_number + ') ' + number[i];
    }
    else if (i == 6) {
      aux_number = aux_number + '-' + number[i];
    }
    else {
      aux_number = aux_number + number[i];
    }
  }

  return aux_number;
};