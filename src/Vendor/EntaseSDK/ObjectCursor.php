<?php

namespace Entase\SDK;

class ObjectCursor extends ObjectBase
{
    public $nextURL;
    public $hasMore;

    public function __construct($nextURL=null, $hasMore=false)
    {
        $this->nextURL = $nextURL;
        $this->hasMore = $hasMore;

        $p = '::';
        $this->$p = 'ObjectCursor';
    }

    public static function ParseArray()
    {
        
    }
}