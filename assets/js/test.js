let tratar      = document.getElementById('tratar');
let iniciar     = document.getElementById('iniciar');
let order;
let token;
let threeDServerTransID;
let dsMethodUrl;
let threeDSInfo;
let protocolVersion;
let autorizationGet = '';
let autorizacionPayLoad;
let iniciarGet;
let challenge;

import axios from "axios";

function validaciones() {
    //Insertar validaciones…
    console.log('Aqui se pueden realizar validaciones previas a la generación del token');
    return true;
}

tratar.addEventListener('click', function()
{
    let config = {
        method: 'post',
        maxBodyLength: Infinity,
        url: '/api/autorizacion',
        headers: {
            'Content-Type': 'application/json'
        },
        data : autorizacionPayLoad
    }
    axios.request(config)
        .then(function (response) {
            //evaluo si es challenge para redireccionar
            if ( response.data.challenge )
            {
                console.log(response.data.outDsEmv3DS.acsURL);
                location.href ='api/challenge/' + btoa(encodeURIComponent(response.data.outDsEmv3DS.acsURL)) + '/'
                + response.data.outDsEmv3DS.creq;

            } else {
                console.log(response.data);
            }
        })
        .catch(function (error) {
            console.log(error);
        });
});

iniciar.addEventListener('click', function() {

    //let iniciarGet = '/api/iniciarPeticion/' + token + '/' + order + '/7878' + '/' + 'carritoId';

    axios.get( iniciarGet )
        .then(function (response) {

            autorizacionPayLoad = {
                token: token,
                amount: '7878',
                order: order.toString(),
                idCarrito: 'carritoId',
                dsServerTransId: response.data.threeDServerTransID,
                protocolVersion: response.data.protocolVersion,
                dsMethodUrl: response.data.threeDSMethodURL
            };


            autorizationGet     = '/api/autorizacion/' + token + '/' + order + '/7878/carritoId/' + threeDServerTransID  + '/' + protocolVersion + '/' + dsMethodUrl;

            console.log(autorizationGet)
            console.log(autorizacionPayLoad);

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
    iniciarGet = '/api/iniciarPeticion/' + token + '/' + order + '/7878/iii';
    console.log(iniciarGet);

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