# fritz-api


https://avm.de/fileadmin/user_upload/Global/Service/Schnittstellen/AHA-HTTP-Interface.pdf

info zur bitmask
https://www.heise.de/select/ct/2016/7/1459414791794586


```
$devices = FritzHome::fetchDevices();
$devices = FritzHome::fetchDevices('LightBulb');
$devices = FritzHome::fetchDevices('LightBulb', 'SmartPlug', 'Thermostat');
```