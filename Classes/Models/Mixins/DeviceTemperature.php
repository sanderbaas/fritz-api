<?php

namespace Kuhschnappel\FritzApi\Models\Mixins;

use Kuhschnappel\FritzApi\Api;


trait DeviceTemperature
{


    /**
     * @param bool $cached get from cache
     * @return int Letzte Temperaturinformation des Aktors in °C * 10
     * Temperatur-Wert in 0,1 °C, negative und positive Werte möglich Bsp. „200“ bedeutet 20°C
     */
    public function getRoomTemperature($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->temperature->celsius = Api::switchCmd('gettemperature', ['ain' => $this->getIdentifier()]);

        return (int)$this->fritzDeviceInfos->temperature->celsius;

    }


}