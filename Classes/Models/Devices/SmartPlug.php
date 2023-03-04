<?php

namespace Kuhschnappel\FritzApi\Models\Devices;

use Kuhschnappel\FritzApi\Api;
use Kuhschnappel\FritzApi\Models\Device;
use Kuhschnappel\FritzApi\Models\Mixins\DeviceDefaults;
use Kuhschnappel\FritzApi\Models\Mixins\DevicePower;
use Kuhschnappel\FritzApi\Models\Mixins\DeviceTemperature;


// FRITZ!DECT 210
class SmartPlug extends Device
{
    use DeviceDefaults;
    use DevicePower;
    use DeviceTemperature;



    /**
     * @var array $powermeter
     * <power>Wert in 0,001 W (aktuelle Leistung, wird etwa alle 2 Minuten aktualisiert)
     * <energy>Wert in 1.0 Wh (absoluter Verbrauch seit Inbetriebnahme)
     * <voltage>Wert in 0,001 V (aktuelle Spannung, wird etwa alle 2 Minuten aktualisiert)
     */
    public $powermeter;

    /**
     * @var array $switch
     *
     */
    private $switch;


    /**
     * @var array $simpleonoff
     *
     */
    private $simpleonoff;

    /**
     * @var array $temperature
     *
     */
    public $temperature;

//    public function __construct($cfg)
//    {
//
//        parent::__construct($cfg);
//        SimpleXMLElement Object
//        (
//            [@attributes] => Array
//            (
//                [identifier] => 11657 0419500
//                [id] => 17
//                [functionbitmask] => 35712
//                [fwversion] => 04.17
//                [manufacturer] => AVM
//                [productname] => FRITZ!DECT 210
//            )
//            [present] => 1
//            [txbusy] => 0
//            [name] => Lara Fernseher
//            [switch] => SimpleXMLElement Object
//            (
//                [state] => 1
//                [mode] => manuell
//                [lock] => 0
//                [devicelock] => 0
//            )
//            [simpleonoff] => SimpleXMLElement Object
//            (
//                [state] => 1
//            )
//            [powermeter] => SimpleXMLElement Object
//            (
//                [voltage] => 233853
//                [power] => 1270
//                [energy] => 54345
//            )
//            [temperature] => SimpleXMLElement Object
//            (
//                [celsius] => 205
//                [offset] => 0
//            )
//        )

// var_dump($cfg);

//
//        $this->setSwitch([
//            'state' => (int)$cfg->switch->state,
//            'mode' => (string)$cfg->switch->mode,
//            'lock' => (string)$cfg->switch->lock,
//            'devicelock' => (string)$cfg->switch->devicelock
//        ]);
//
//        $this->setSimpleonoff([
//            'state' => (int)$cfg->simpleonoff->state
//        ]);
//
//        $this->setPowermeter([
//            'voltage' => (string)$cfg->powermeter->voltage,
//            'power' => (string)$cfg->powermeter->power,
//            'energy' => (string)$cfg->powermeter->energy
//        ]);
//
//
//    }

    /**
     * @return array
     */
    public function getPowermeter()
    {
        return $this->powermeter;
    }

    /**
     * @param array $powermeter
     */
    public function setPowermeter($powermeter)
    {
        $this->powermeter = $powermeter;
    }


    /**
     * @return array
     */
    public function getSwitch()
    {
        return $this->switch;
    }

    /**
     * @param array $switch
     */
    public function setSwitch($switch)
    {
        $this->switch = $switch;
    }

    /**
     * @return array
     */
    public function getSimpleonoff()
    {
        return $this->simpleonoff;
    }

    /**
     * @param array $simpleonoff
     */
    public function setSimpleonoff($simpleonoff)
    {
        $this->simpleonoff = $simpleonoff;
    }


    /**
     * @return boolean
     */
    public function isPower($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->switch->state = (string)Api::switchCmd('getswitchstate', ['ain' => $this->getIdentifier()]);

        return (string)$this->fritzDeviceInfos->switch->state;
    }

    public function toggle()
    {
        $this->fritzDeviceInfos->switch->state = Api::switchCmd('setswitchtoggle', ['ain' => $this->getIdentifier()]);
    }

    public function powerOff()
    {
        //TODO: check if is plugged in (present)
        $this->fritzDeviceInfos->switch->state = Api::switchCmd('setswitchoff', ['ain' => $this->getIdentifier()]);
    }

    public function powerOn()
    {
        //TODO: check if is plugged in (present)
        $this->fritzDeviceInfos->switch->state = Api::switchCmd('setswitchon', ['ain' => $this->getIdentifier()]);
    }


    /**
     * @return int Leistung in mW, "inval" wenn unbekannt
     * Ermittelt aktuell über die Steckdose entnommene Leistung
     */
    public function getPower($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->powermeter->power = (string)Api::switchCmd('getswitchpower', ['ain' => $this->getIdentifier()]);

//        return bcdiv($this->fritzDeviceInfos->powermeter->power, 1000, 3);
        return (int)$this->fritzDeviceInfos->powermeter->power;
    }

    /**
     * @return int Energie in Wh, "inval" wenn unbekannt
     * Liefert die über die Steckdose entnommene Ernergiemenge seit Erstinbetriebnahme oder Zurücksetzen der Energiestatistik
     */
    public function getConsumption($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->powermeter->energy = (string)Api::switchCmd('getswitchenergy', ['ain' => $this->getIdentifier()]);

        return (int)$this->fritzDeviceInfos->powermeter->energy;
    }

    /**
     * @return int Spannung in mVolt
     * Liefert die in der Steckdose anliegende Spannung
     * @todo implement caching
     */
    public function getVoltage($cached = false)
    {
//        if (!$cached)
//            refresh content with getdeviceinfos

        return (int)$this->fritzDeviceInfos->powermeter->voltage;
//        return bcdiv($this->fritzDeviceInfos->powermeter->energy, 1000, 3);
    }




}