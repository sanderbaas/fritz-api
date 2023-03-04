<?php

namespace Kuhschnappel\FritzApi\Models\Mixins;

use Kuhschnappel\FritzApi\Api;

trait DeviceDefaults
{


    /**
     * @return boolean
     */
    public function isConnected($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->present = (string)Api::switchCmd('getswitchpresent', ['ain' => $this->getIdentifier()]);

        return (string)$this->fritzDeviceInfos->present;
    }

    /**
     * @return string
     */
    public function getName($cached = false)
    {
        if (!$cached)
            $this->fritzDeviceInfos->name = (string)Api::switchCmd('getswitchname', ['ain' => $this->getIdentifier()]);

        return (string)$this->fritzDeviceInfos->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        //TODO: check rights - 400er error
        Api::switchCmd('setswitchname', ['ain' => $this->getIdentifier(), 'name' => $name]);
        $this->fritzDeviceInfos->name = $name;
    }

    /**
     * @var string type to verify
     * @return boolean
     */
    public function isType($type)
    {
        if ($this->getType() == $type)
            return true;

        return false;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return end(explode('\\',get_class($this)));
    }

    /**
     * @param mixed measurements string or array
     * @return string
     * @todo implement caching
     */
    public function getStats($measurements = ['temperature', 'voltage', 'power', 'energy', 'humidity'])
    {
        $response = Api::switchCmd('getbasicdevicestats', ['ain' => $this->getIdentifier()]);

        $xml = simplexml_load_string($response);

        $measurementsArr = $measurements;
        if (!is_array($measurements))
            $measurementsArr = [$measurements];
        $statArr = [];
        foreach($measurementsArr as $measurement) {
            if (isset($xml->$measurement)) {
                $statArr[$measurement] = [];
                $datetimeNow = new \DateTime("UTC");
                $datetimeMidnight = new \DateTime(date_format($datetimeNow, 'Y-m-d')."UTC");
                $secondsSinceMidnight = $datetimeNow->getTimestamp() - $datetimeMidnight->getTimeStamp();


                //getdetailed stats if more than one
                $detailedStats = null;
                foreach($xml->$measurement->stats as $stat) {
                    if (!$detailedStats)
                        $detailedStats = $stat;
                    elseif ((int)$stat->attributes()->grid < (int)$detailedStats->attributes()->grid)
                        $detailedStats = $stat;
                }

                $intervalSeconds = (int)$detailedStats->attributes()->grid;
                $íntervalStart = date_interval_create_from_date_string(floor($secondsSinceMidnight/$intervalSeconds)*$intervalSeconds . ' seconds');
                $íntervalGrid = date_interval_create_from_date_string($intervalSeconds . ' seconds');
                $values = explode(',', (string)$detailedStats);

                $datetime = clone $datetimeMidnight;
                date_add($datetime, $íntervalStart);

                foreach ($values as $value) {
                    if($value == '-') $value = null;
                    if($measurement=='power')
                        $value = (int)$value * 10;
                    $statArr[$measurement][date_format($datetime, 'Y-m-d H:i:s')] = $value;
                    date_sub($datetime, $íntervalGrid);
                }

            }
        }
        if(!is_array($measurements) && count($statArr)==1)
            return $statArr[$measurements];

        return $statArr;

    }







}