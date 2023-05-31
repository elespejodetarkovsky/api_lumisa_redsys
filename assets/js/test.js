let tratar      = document.getElementById('tratar');
let iniciar     = document.getElementById('iniciar');
let order;
let token;
let threeDServerTransID;
let dsMethodUrl;
let threeDSInfo;
let protocolVersion;
let autorizationGet = '';

import axios from "axios";

function validaciones() {
    //Insertar validaciones…
    console.log('Aqui se pueden realizar validaciones previas a la generación del token');
    return true;
}

tratar.addEventListener('click', function()
{
    axios.get(autorizationGet)
        .then(function (response) {
            if (response.data)
            {
                console.log(response.data);
            }
        })
        .catch(function (error) {
            console.log(error);
        });
});

iniciar.addEventListener('click', function() {
    axios.get('/api/iniciarPeticion/' + token + '/' + order + '/7878' + '/' + 'carritoId' )
        .then(function (response) {

            threeDServerTransID = response.data.threeDServerTransID;
            dsMethodUrl         = response.data.dsMethodUrl ?? null;
            threeDSInfo         = response.data.threeDSInfo;
            protocolVersion     = response.data.protocolVersion;

            autorizationGet     = '/api/autorizacion/' + token + '/' + order + '/7878/carritoId/' + threeDServerTransID; // + '/' + dsMethodUrl;

            console.log(autorizationGet)

        })
        .catch(function (error) {
            console.log(error);
        });
});

window.addEventListener("message", function receiveMessage(event) {
    //se almacena en el input el token generado, o error en caso de que ocurra

    storeIdOper(event, "token", "errorCode", validaciones);
    token = document.getElementById('token').value;
    //enlace.href = '/api/autorizacion/' + token + '/' + order + '/7895/iii';
    //iniciar.href = '/api/iniciarPeticion/' + token + '/' + order + '/7895/iii';
    console.log('order: ')
    console.log( order );
    console.log('token: ');
    console.log( token );

});

function pedido() {

    //genero un numero en base a date unix único
    console.log('num_order: ');
    order = Math.floor(Date.now() / 1000);

    //como es tan estricto con el tema de los string hago la conversión
    return order.toString();
}


getCardInput('card-number', '', '', '');
getExpirationInput('card-expiration', '', '');
getCVVInput('cvv', '', '');
getPayButton('boton', '', 'Texto botón pago', '999008881', '1', pedido());