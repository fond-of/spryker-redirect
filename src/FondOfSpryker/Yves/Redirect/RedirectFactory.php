<?php

namespace FondOfSpryker\Yves\Redirect;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use FondOfSpryker\Yves\Redirect\ResourceCreator\RedirectInternalResourceCreator;
use FondOfSpryker\Yves\Redirect\ResourceCreator\RedirectResourceCreator;
use Sinergi\BrowserDetector\Language;
use Spryker\Client\Session\SessionClientInterface;
use Spryker\Client\Store\StoreClientInterface;
use Spryker\Yves\Kernel\AbstractFactory;

class RedirectFactory extends AbstractFactory
{
    /**
     * @return \FondOfSpryker\Yves\Redirect\ResourceCreator\RedirectResourceCreator
     */
    public function createRedirectResourceCreator(): RedirectResourceCreator
    {
        return new RedirectResourceCreator();
    }

    /**
     * @return \FondOfSpryker\Yves\Redirect\ResourceCreator\RedirectInternalResourceCreator
     */
    public function createRedirectInternalResourceCreator(): RedirectInternalResourceCreator
    {
        return new RedirectInternalResourceCreator();
    }

    /**
     * @return \Jaybizzle\CrawlerDetect\CrawlerDetect
     */
    public function createCrawlerDetect(): CrawlerDetect
    {
        return new CrawlerDetect();
    }

    /**
     * @return \Sinergi\BrowserDetector\Language
     */
    public function createBrowserDetectorLanguage(): Language
    {
        return new Language();
    }

    /**
     * @throws
     *
     * @return \Spryker\Client\Store\StoreClientInterface
     */
    public function getStoreClient(): StoreClientInterface
    {
        return $this->getProvidedDependency(RedirectDependencyProvider::CLIENT_STORE);
    }

    /**
     * @throws
     *
     * @return \Spryker\Client\Session\SessionClientInterface
     */
    public function getSessionClient(): SessionClientInterface
    {
        return $this->getProvidedDependency(RedirectDependencyProvider::SESSION_STORE);
    }
}
