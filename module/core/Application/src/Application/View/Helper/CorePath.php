<?php

namespace Application\View\Helper;


use Zend\View\Helper\AbstractHelper;

class CorePath extends AbstractHelper
{
    public function __invoke($file = null)
    {
        $file = 'core/'.ltrim($file, '/');
        return rtrim($this->getView()->basePath($file), '/');
    }
}