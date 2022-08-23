<?php

namespace Entase\SDK;

class Endpoint
{
    public $client = null;
    public $endpointURL = '';

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function GetAll($data=null)
    {
        return $this->client->GET($this->endpointURL, $data);
    }

    public function GetByID($id)
    {
        return $this->client->GET($this->endpointURL.'/'.$id);
    }

}