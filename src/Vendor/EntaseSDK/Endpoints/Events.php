<?php

namespace Entase\SDK\Endpoints;

class Events extends \Entase\SDK\Endpoint
{
    public function __construct($client)
    {
        parent::__construct($client);
        $this->endpointURL = 'events';
    }
}