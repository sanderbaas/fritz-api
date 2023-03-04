<?php

namespace Kuhschnappel\FritzApi\Models;

class Device
{

    /**
     * @var Object SimpleXMLElement
     */
    public $fritzDeviceInfos;



    public function __construct($xml = null) {
        if ($xml)
            $this->setDefaultsFromResponse($xml);
    }


    public function setDefaultsFromResponse($xml) {
        if ((string)$xml->attributes()->functionbitmask == '1')
            throw new \Exception('Device not initialized due to low functionbitmask (1): ' .
                json_encode([
                    'productname' => (string)$xml->attributes()->productname,
                    'name' => (string)$xml->name,
                    'identifier' => (string)$xml->attributes()->identifier
                ])
            );

        $this->fritzDeviceInfos = $xml;
    }


    /**
     * @return string
     */
    public function getIdentifier()
    {
        return (string)$this->fritzDeviceInfos->attributes()->identifier;
    }

    /**
     * @return string
     */
    public function getProductname()
    {
        return (string)$this->fritzDeviceInfos->attributes()->productname;
    }

    /**
     * @return string
     */
    public function getFwversion()
    {
        return (string)$this->fritzDeviceInfos->attributes()->fwversion;
    }



}
