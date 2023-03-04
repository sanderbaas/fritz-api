<?php

namespace Kuhschnappel\FritzApi\Models\Devices;

use Kuhschnappel\FritzApi\Api;
use Kuhschnappel\FritzApi\Models\Device;
use Kuhschnappel\FritzApi\Models\Mixins\DeviceDefaults;
use Kuhschnappel\FritzApi\Models\Mixins\DeviceTemperature;


// FRITZ!DECT 301
class Thermostat extends Device
{
    use DeviceDefaults;
    use DeviceTemperature;



//    const type = 'Thermostat';


//    public function __construct($cfg)
//    {
//
//        parent::__construct($cfg);


        // SimpleXMLElement Object
        // (
        // 		[@attributes] => Array
        // 				(
        // 						[identifier] => 09995 0535352
        // 						[id] => 16
        // 						[functionbitmask] => 320
        // 						[fwversion] => 04.95
        // 						[manufacturer] => AVM
        // 						[productname] => FRITZ!DECT 301
        // 				)

        // 		[present] => 1
        // 		[txbusy] => 0
        // 		[name] => Heizung

        // 		[battery] => 80
        // 		[batterylow] => 0
        // 		[temperature] => SimpleXMLElement Object
        // 				(
        // 						[celsius] => 200
        // 						[offset] => 0
        // 				)

        // 		[hkr] => SimpleXMLElement Object
        // 				(
        // 						[tist] => 40
        // 						[tsoll] => 40
        // 						[absenk] => 32
        // 						[komfort] => 40
        // 						[lock] => 0
        // 						[devicelock] => 0
        // 						[errorcode] => 0
        // 						[windowopenactiv] => 0
        // 						[windowopenactiveendtime] => 0
        // 						[boostactive] => 0
        // 						[boostactiveendtime] => 0
        // 						[batterylow] => 0
        // 						[battery] => 80
        // 						[nextchange] => SimpleXMLElement Object
        // 								(
        // 										[endperiod] => 1642534200
        // 										[tchange] => 32
        // 								)

        // 						[summeractive] => 0
        // 						[holidayactive] => 0
        // 				)

        // )

//        print_r($cfg);


//    }


    /**
     * @var float target temperature
     *
     */
    private static $temperatureTarget;

    /**
     * @param bool $cached get from cache
     * @return int Für HKR aktuell eingestellte Solltemperatur (0 => aus, 1 => max) in °C * 10
     * Temperatur-Wert in 0,5 °C, Wertebereich: 16 – 56 8 bis 28°C, 16 <= 8°C, 17 = 8,5°C...... 56 >= 28°C 254 = ON , 253 = OFF
     */
    public function getTemperatureTarget($cached = false)
    {
        if (!$cached || !isset($this->temperatureTarget))
            $this->temperatureTarget = Api::switchCmd('gethkrtsoll', ['ain' => $this->getIdentifier()]);

        switch ($this->temperatureTarget)
        {
            case 254: //max
                return 1;
                break;
            case 253: //off
                return 0;
                break;
            default:
//                return bcdiv($this->temperatureTarget, 2, 1);
                return $this->temperatureTarget;
                break;
        }

    }


    /**
     * @var float target temperature Temperatur-Wert in 0,5 °C, Wertebereich: 16 – 56 8 bis 28°C, 16 <= 8°C, 17 = 8,5°C...... 56 >= 28°C 254 = ON , 253 = OFF
     * @return boolean result
     * @todo check if device is present
     */
    public function setTemperatureTarget($temperature)
    {

        switch ($temperature)
        {
            case 1: //max
                $this->temperatureTarget = 254;
                break;
            case 0: //off
                $this->temperatureTarget = 253;
                break;
            default:
                $tmp = bcmul($temperature, 2);
                if (in_array($tmp, range(16,56)))
                    $this->temperatureTarget = bcmul($temperature, 2);
                else {
                    Api::$logger->error('Target temperature ' . $temperature . ' not in valid range of 8 - 28, use 0 to put off or 1 to put on max');
                    return false;
                }
                break;
        }
        Api::switchCmd('sethkrtsoll', ['ain' => $this->getIdentifier(), 'param' => $this->temperatureTarget]);

        return true;

    }


}