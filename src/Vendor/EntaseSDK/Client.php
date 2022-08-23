<?php

namespace Entase\SDK;


class Client
{
    public $productions;
    public $events;

    private $_secretKey;

    public function __construct($secretKey)
    {
        $this->_secretKey = $secretKey;
        $this->productions = new Endpoints\Productions($this);
        $this->events = new Endpoints\Events($this);
        $this->photos = new Endpoints\Photos($this);
    }

    public function GET($endpoint, $data=null)
    {
        return $this->_Query($endpoint, $data, 'get');
    }

    public function POST($endpoint, $data=null)
    {
        return $this->_Query($endpoint, $data, 'post');
    }

    private function _Query($endpoint, $data, $method)
    {
        $url = strpos($endpoint, 'https://') !== false ? $endpoint : Env::$APIURL.$endpoint;
        if ($method == 'get' && $data != null)
        {
            $glue = strpos($endpoint, '?') !== false ? '&' : '?';
            $url .= $glue.self::_Arr2HTTP($data);
        }
        
        $ch = curl_init($url);
        if ($method == 'post')
        {
            curl_setopt( $ch, CURLOPT_POST, 1);
            if ($data != null)
                curl_setopt( $ch, CURLOPT_POSTFIELDS, self::_Arr2HTTP($data));
        }

        if (Env::$DebugMode)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }
        
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt( $ch, CURLOPT_HEADER, 1);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt( $ch, CURLOPT_FAILONERROR, 0);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$this->_secretKey]);

        $response = curl_exec( $ch );
        $result = json_decode($response);
        $error = curl_errno($ch) ? curl_error($ch) : null;
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error != null) throw new Exceptions\CURL($error);
        else if ($httpCode != 200)
        {
            $msg = $result->msg ?? 'General request error.';
            $errCode = $result->code ?? '';
            throw new Exceptions\Request($msg.($errCode != '' ? ' Code: '.$errCode : ''), $httpCode);
        }
        else if (($result->status ?? '') != 'ok')
        {
            $msg = $result->msg ?? 'General API error.';
            $errCode = $result->code ?? '';
            throw new Exceptions\API($msg.($errCode != '' ? ' Code: '.$errCode : ''), $httpCode);
        }

        $resource = $result->resource ?? null;
        if ($resource != null && ((array)$resource)['::'] == 'ObjectCollection')
        {
            $collection = ObjectCollection::Cast($resource);
            $collection->SetClient($this);
            return $collection;
        }
        else return $resource;
    }

    private function _Arr2HTTP($arr, $wrap=null)
    {
        $str = '';
        $wrapPrefix = $wrap ?? '';
        foreach ($arr as $key => $value)
        {
            if (is_array($value))
                $str .= self::_Arr2HTTP($value, $wrapPrefix != '' ? $wrapPrefix.'['.$key.']' : $key.'[');
            else $str .= $wrapPrefix.$key.($wrapPrefix != '' ? ']' : '').'='.urlencode($value).'&';
        }

        return $str;
    }
}