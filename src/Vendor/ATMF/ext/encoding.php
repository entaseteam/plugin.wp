<?php

namespace ATMF\CoreExtensions;

class JsonEncode implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $obj = $args[0] ?? null;
        if ($obj != null)
            return json_encode($obj);
        else
            return '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('json_encode', new JsonEncode());

class JsonEncodeBase64 extends JsonEncode
{
    public function Get($args)
    {
        $str = parent::Get($args);
        return base64_encode($str);
    }


}
\ATMF\Extensions::Register('json_encode_base64', new JsonEncodeBase64());

class Base64Encode implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $str = $args[0] ?? '';
        if ($str != '')
            return base64_encode($str);
        else
            return '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('base64_encode', new Base64Encode());