<?php

namespace Entase\SDK;

use \IteratorAggregate;
use \Traversable;
use \ArrayIterator;

class ObjectCollection extends ObjectBase implements IteratorAggregate
{
    public $data;
    public $cursor;

    private $_client;
    private $_startPaging;

    public function __construct($data=[], $cursor=null)
    {
        $this->data = $data;
        $this->cursor = $cursor;
        $this->_startPaging = false;

        $p = '::';
        $this->$p = 'ObjectCollection';
    }

    public function getIterator() : Traversable {
        return new ArrayIterator($this->data);
    }

    public function SetClient($client)
    {
        if ($client instanceof Client)
            $this->_client = $client;
    }

    

    public function HasMore()
    {
        if (!$this->_startPaging) 
        {
            $this->_startPaging = true;
            return true;
        }

        if ($this->cursor == null || !$this->cursor->hasMore)
            return false;

        try 
        {
            $collection = $this->_client->GET($this->cursor->nextURL);
            $this->data = $collection->data ?? [];
            $this->cursor = $collection->cursor ?? null;

            return true;
        }
        catch (\Exception $ex) 
        {
            return false;
        }
    }
}