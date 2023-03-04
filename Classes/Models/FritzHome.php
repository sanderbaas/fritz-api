<?php


namespace Kuhschnappel\FritzApi\Models;

use Kuhschnappel\FritzApi\Api;

use Kuhschnappel\FritzApi\Models\Devices\SmartPlug;
use Kuhschnappel\FritzApi\Models\Devices\Thermostat;
use Kuhschnappel\FritzApi\Models\Devices\LightBulb;

class FritzHome
{

    const DEVICES = [
        'LightBulb' => 'Kuhschnappel\\FritzApi\\Models\\Devices\\LightBulb',
        'SmartPlug' => 'Kuhschnappel\\FritzApi\\Models\\Devices\\SmartPlug',
        'Thermostat' => 'Kuhschnappel\\FritzApi\\Models\\Devices\\Thermostat',
    ];

    /**
     *
     * @var array
     */
    public static $devices = [];

    public static function addDevice($cfg)
    {
        $ain = (string)$cfg->attributes()->identifier;


        try {
            switch ($cfg->attributes()->productname) {
                case 'FRITZ!DECT 500':
                    return self::$devices[$ain] = new LightBulb($cfg);
                    break;
                case 'FRITZ!DECT 210':
                    return self::$devices[$ain] = new SmartPlug($cfg);
                    break;
                case 'FRITZ!DECT 301':
                    return self::$devices[$ain] = new Thermostat($cfg);
                    break;
                default:
                    Api::$logger->warning('Unknown device, not implemented yet -> ' . $cfg->attributes()->productname);
                    break;
            }

        } catch (\Exception $e) {
            Api::$logger->warning('DeviceInit -> ' . $e->getMessage());
        }

    }

    public static function fetchDevices()
    {

        $response = Api::switchCmd('getdevicelistinfos');
        $xml = simplexml_load_string($response);

        foreach ($xml->device as $dev)
            self::addDevice($dev);

        return call_user_func_array('self::getDevices', func_get_args());
    }

    public static function fetchDevice($ain)
    {
        if ($response = Api::switchCmd('getdeviceinfos', ['ain' => $ain]))
            return self::addDevice(simplexml_load_string($response));

        Api::$logger->error('Device with Ain ' . $ain . ' not found in Fritz!Box');

    }

    /*
     * @var mixed device object
     * @return mixed
     */
    public static function getDevices()
    {
        $filterDeviceModels = array_intersect_key(self::DEVICES, array_flip(func_get_args()));
        if (empty($filterDeviceModels) || !count($filterDeviceModels))
            return self::$devices;

        $retArr = [];
        foreach (self::$devices as $device)
            if (in_array(get_class($device), $filterDeviceModels))
                $retArr[] = $device;

        return $retArr;;
    }

    /*
     * @var string ain identifier
     * @return mixed
     */
    public static function getDevice($ain)
    {
        if(isset(self::$devices[$ain]))
            return self::$devices[$ain];

    }

}