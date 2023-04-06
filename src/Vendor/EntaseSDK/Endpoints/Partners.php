<?php

namespace Entase\SDK\Endpoints;

class Partners extends \Entase\SDK\Endpoint
{
    public function __construct($client)
    {
        parent::__construct($client);
        $this->endpointURL = 'partners';
    }

    public function GetAll($data=null)
    {
        throw new \Entase\SDK\Exceptions\Request('GetAll method not supported for Partners endpoint.');
    }
    public function Me()
    {
        return $this->client->GET($this->endpointURL.'/me');
    }
}