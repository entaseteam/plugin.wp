<?php

namespace Entase\SDK\Endpoints;

class Productions extends \Entase\SDK\Endpoint
{
    public function __construct($client)
    {
        parent::__construct($client);
        $this->endpointURL = 'productions';
    }
}