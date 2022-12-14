<?php

namespace FondOfSpryker\Yves\Redirect\Controller;

use Spryker\Shared\Storage\StorageConstants;
use Spryker\Yves\Kernel\Controller\AbstractController;

class RedirectController extends AbstractController
{
    /**
     * @var string
     */
    public const STORAGE_CACHE_STRATEGY = StorageConstants::STORAGE_CACHE_STRATEGY_INACTIVE;

    /**
     * @param array $meta
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectAction($meta)
    {
        return $this->redirectResponseExternal(
            $meta['to_url'],
            $meta['status'],
        );
    }

    /**
     * @param array $meta
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectInternalAction($meta)
    {
        return $this->redirectResponseInternal(
            $meta['path'],
            $meta['parameters'],
            $meta['status'],
        );
    }
}
