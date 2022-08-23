<?php

namespace Entase\Plugins\WP\Utilities;

class Ajax
{
    public static function StatusOK($data=[])
    {
        die(json_encode(array_merge(['status' => 'ok'], $data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public static function StatusERR($msg='', $data=[])
    {
        die(json_encode(array_merge(['status' => 'err', 'msg' => $msg], $data), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}