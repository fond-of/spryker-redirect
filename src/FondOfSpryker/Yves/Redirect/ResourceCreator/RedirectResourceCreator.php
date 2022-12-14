<?php

namespace FondOfSpryker\Yves\Redirect\ResourceCreator;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\Application\Communication\ControllerServiceBuilder;
use Spryker\Yves\Kernel\BundleControllerAction;
use Spryker\Yves\Kernel\ClassResolver\Controller\ControllerResolver;
use Spryker\Yves\Kernel\Controller\BundleControllerActionRouteNameResolver;

class RedirectResourceCreator
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'redirect';
    }

    /**
     * @param \Spryker\Service\Container\ContainerInterface $application
     * @param array $data
     *
     * @return array
     */
    public function createResource(ContainerInterface $application, array $data)
    {
        $bundleControllerAction = new BundleControllerAction('Redirect', 'Redirect', 'redirect');
        $routeNameResolver = new BundleControllerActionRouteNameResolver($bundleControllerAction);
        $controllerResolver = new ControllerResolver();
        $controllerServiceBuilder = new ControllerServiceBuilder();
        $service = $controllerServiceBuilder->createServiceForController($application, $bundleControllerAction, $controllerResolver, $routeNameResolver);

        return [
            '_controller' => $service,
            '_route' => $routeNameResolver->resolve(),
            'meta' => $data,
        ];
    }
}
