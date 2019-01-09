<?php

namespace Wolfmatrix\RestApiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wolfmatrix\RestApiBundle\DependencyInjection\WolfmatrixRestApiExtension;

class WolfmatrixRestApiBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new WolfmatrixRestApiExtension();
    }
}