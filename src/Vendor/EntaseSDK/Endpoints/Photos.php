<?php

namespace Entase\SDK\Endpoints;

class Photos extends \Entase\SDK\Endpoint
{
    public function __construct($client)
    {
        parent::__construct($client);
        $this->endpointURL = 'photos';
    }

    public function GetByObject($objref, $data=null)
    {
        return $this->client->GET($this->endpointURL.'/object/'.$objref, $data);
    }
}