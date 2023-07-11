let tratar          = document.getElementById('tratar');
let iniciar         = document.getElementById('iniciar');
let devolucion      = document.getElementById('devolucion');
let formChallenge   = document.getElementById('frmChallenge');
let formChallenge1  = document.getElementById('frmChallenge1');
let creq            = document.getElementById('creq');
let pareq           = document.getElementById('PaReq');
let md              = document.getElementById('MD');
let termURL         = document.getElementById('TermUrl');
let outIframe       = document.getElementById('redsysIframe');


let order;
let token;
let threeDServerTransID;
let dsMethodUrl;
let threeDSInfo;
let protocolVersion;
let autorizationGet = '';
let autorizacionPayLoad;
let iniciarGet;


import axios from "axios";

function validaciones() {
    //Insertar validaciones…
    console.log('Aqui se pueden realizar validaciones previas a la generación del token');
    return true;
}

devolucion.addEventListener('click', function (){

    let config = {
        method: 'post',
        maxBodyLength: Infinity,
        url: '/api/devolucion',
        headers: {
            'Content-Type': 'application/json'
        },
        data : {
            idOper: 'aa8ad012005d64e91e26f2472c8d975145315bbd',
            order: '1686820655',
            amount: '7878'
        }
    }
    axios.request(config)
        .then(function (response) {
            //evaluo si es challenge para redireccionar
            if ( response.data )
            {

                if ( response.data )
                {
                    //existe por tanto es un challenge asumo que es 2.x.0
                    console.log( response.data );

                } else
                {
                    console.log({ 'transaction' : response.data });
                }

            } else {
                console.log(response.data);
            }
        })
        .catch(function (error) {
            console.log(error);
        });

});

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
            if ( response.data )
            {

                if ( response.data.outDsEmv3DS )
                {
                    //version 2
                    if ( response.data.version !== '1' )
                    {

                        console.log( response.data.outDsEmv3DS );
                        formChallenge.action    = response.data.outDsEmv3DS.acsURL;
                        creq.value              = response.data.outDsEmv3DS.creq;
                        //formChallenge.submit();
                        outIframe.src = '/api/challengeTest/' + '2' + '/' + btoa(response.data.outDsEmv3DS.acsURL) + '/' + response.data.outDsEmv3DS.creq;
                        outIframe.style.visibility = 'visible';

                    } else {

                        console.log( response.data.outDsEmv3DS );
                        formChallenge1.action    = response.data.outDsEmv3DS.acsURL;
                        pareq.value              = response.data.outDsEmv3DS.pareq;
                        md.value                 = response.data.outDsEmv3DS.md;
                        termURL.value            = response.data.outDsEmv3DS.termUrl;

                        //formChallenge1.submit();

                    }

                } else
                {
                    console.log({ 'transaction' : response.data });
                }

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
                orderId: order.toString(),
                dsServerTransId: response.data.threeDServerTransID,
                protocolVersion: response.data.protocolVersion,
                dsMethodUrl: response.data.threeDSMethodURL
            };


            autorizationGet     = '/api/autorizacion/' + token + '/' + order + '/7878/' + threeDServerTransID  + '/' + protocolVersion + '/' + dsMethodUrl;

            console.log(autorizationGet)
            console.log(autorizacionPayLoad);
            console.log(response.data);

            if ( !!response.data.threeDSMethodURL )
            {
                //realizo la redireccion a 3dmethod url
                //location.href = '/api/threeDsMethodTestForm/' + response.data.threeDSMethodData + '/' + btoa(response.data.threeDSMethodURL)
                //window.open('/api/threeDsMethodTestForm/' + response.data.threeDSMethodData + '/' + btoa(response.data.threeDSMethodURL), '_blank')
                outIframe.src = '/api/threeDsMethodForm/' + response.data.threeDSMethodData + '/' + btoa(response.data.threeDSMethodURL);
                outIframe.style.visibility = 'visible';

            }

        })
        .catch(function (error) {
            console.log(error);
        });
});

window.addEventListener("message", function receiveMessage(event) {
    //se almacena en el input el token generado, o error en caso de que ocurra

    console.log('evento:');
    console.log(event);

    storeIdOper(event, "token", "errorCode", validaciones);
    token = document.getElementById('token').value;

    iniciarGet = '/api/iniciarPeticion/' + token + '/' + order + '/7878';
    console.log(iniciarGet);

});

function pedido() {

    //genero un numero en base a date unix único
    //console.log('num_order: ');
    order = Math.floor(Date.now() / 1000);

    //como es tan estricto con el tema de los string hago la conversión
    return order.toString();
}


getCardInput('card-number', '', '', '');
getExpirationInput('card-expiration', '', '');
getCVVInput('cvv', '', '');
getPayButton('boton', '', 'Texto botón pago', '097739635', '1', pedido());