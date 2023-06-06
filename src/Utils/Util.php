<?php

namespace App\Utils;

class Util
{
    static public function makeMerchantEmv3ds(bool $threeDsCompInd, string $threDSServerTransId,
                                              string $order, string $protocolVersion): array
    {
        return array(RESTConstants::$REQUEST_MERCHANT_EMV3DS_THREEDSINFO => RESTConstants::$REQUEST_MERCHANT_EMV3DS_AUTHENTICACIONDATA,
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_PROTOCOLVERSION => $protocolVersion,
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_ACCEPT_HEADER_VALUE,
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT => RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_USER_AGENT_VALUE,
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_JAVA_ENABLE => 'false',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_JAVASCRIPT_ENABLE => 'false',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_LANGUAGE => 'ES-es',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_COLORDEPTH => '24',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_HEIGHT => '1250',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_SCREEN_WIDTH => '1320',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_BROWSER_TZ => '52',
            RESTConstants::$REQUEST_MERCHANT_EMV3DS_NOTIFICATIONURL => 'https://127.0.0.1:8000/api/notificacionURL/'.$order,
            "threeDSServerTransID" => $threDSServerTransId,
            "threeDSCompInd" => $threeDsCompInd ? "Y":"N"); //N en caso de no recibir la url threeDSMethodURL null
    }
}