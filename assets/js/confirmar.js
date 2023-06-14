import axios from "axios";

const confirmar = document.getElementById("confirmar");

confirmar.addEventListener('click', function (){

    console.log( window.confirmationPayLoad );

    let config = {
        method: 'post',
        maxBodyLength: Infinity,
        url: '/api/confirmacion_autorizacion',
        headers: {
            'Content-Type': 'application/json'
        },
        data : window.confirmationPayLoad
    }
    axios.request(config)
        .then(function (response) {
            //evaluo si es challenge para redireccionar
            if ( response.data )
            {

                console.log( response.data );

            } else {
                console.log( response.data );
            }
        })
        .catch(function ( error ) {
            console.log( error );
        });
});
