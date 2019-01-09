<?php

namespace Crud\ReusableBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Crud\ReusableBundle\DependencyInjection\CrudReusableExtension;

class CrudReusableBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CrudReusableExtension();
    }
}