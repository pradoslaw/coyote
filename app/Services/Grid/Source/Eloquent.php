<?php

namespace Coyote\Services\Grid\Source;

class Eloquent implements SourceInterface
{
    protected $source;
    
    public function __construct($source)
    {
        $this->source = $source;
    }
    
    public function paginate()
    {
        //
    }
}
