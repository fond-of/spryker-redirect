<?php

namespace FondOfSpryker\Yves\Redirect\Router;

use Spryker\Shared\Kernel\Store;
use Spryker\Yves\Application\Routing\AbstractRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * @method \FondOfSpryker\Yves\Redirect\RedirectFactory getFactory()
 */
class RedirectRouter extends AbstractRouter
{
    private const LOCALE_BASE_ROUTE_NAME_PREFIX = 'locale.switch.';

    /**
     * @inheritdoc
     * @throws \Symfony\Component\Routing\Exception\RouteNotFoundException
     */
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        throw new RouteNotFoundException();
    }

    /**
     * @param string $pathinfo
     *
     * @throws \Symfony\Component\Routing\Exception\ResourceNotFoundException
     *
     * @return string[]
     */
    public function match($pathinfo): array
    {
        // ignore crawler
        if ($this->isCrawler()) {
            throw new ResourceNotFoundException();
        }

        // redirect to start page if locale is not given on base route
        if ($this->isBaseRoute($pathinfo)) {
            return $this->redirectWithLocale();
        }

        // exclude api/error routes from redirects to locale and trailing slash
        if ($this->hasExceptions($pathinfo)) {
            throw new ResourceNotFoundException();
        }

        // redirect to start page if locale is not given
        if ($this->hasValidLocalePrefix($pathinfo) === false) {
            return $this->redirectWithLocale($pathinfo);
        }

        // if path has trailing slash, cut the slash and redirect to this url.
        if ($this->hasTrailingSlash($pathinfo)) {
            return $this->redirectWithoutTrailingSlash();
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param string $pathinfo
     *
     * @return bool
     */
    protected function hasExceptions(string $pathinfo): bool
    {
        $startingRouteExceptions = ['/payone', '/error', '/feed', '/_profiler', '/form'];
        foreach ($startingRouteExceptions as $startingRouteException) {
            if ($this->pathStartsWith($pathinfo, $startingRouteException)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pathinfo
     * @param string $startingPart
     *
     * @return bool
     */
    protected function pathStartsWith(string $pathinfo, string $startingPart): bool
    {
        return strpos($pathinfo, $startingPart) === 0;
    }

    /**
     * @param string $pathinfo
     *
     * @return bool
     */
    protected function isBaseRoute(string $pathinfo): bool
    {
        return $pathinfo === '/';
    }

    /**
     * @param string $pathinfo
     *
     * @return bool
     */
    protected function hasValidLocalePrefix(string $pathinfo): bool
    {
        $explodePath = explode('/', trim($pathinfo, '/'));
        if (count($explodePath) == 0) {
            return false;
        }

        return $this->isLocaleAvailableInCurrentStore($explodePath[0]);
    }

    /**
     * @return string[]
     */
    protected function redirectWithoutTrailingSlash(): array
    {
        $uri = substr($this->getRequest()->getSchemeAndHttpHost() . $this->getRequest()->getPathInfo(), 0, -1);
        $uri = $this->appendQueryStringToUri($uri);

        return $this->createRedirect($uri);
    }

    /**
     * @param string $fallbackRoutePrefixLocale
     *
     * @return string
     */
    protected function getDefaultStoreRouteLocalePrefix(string $fallbackRoutePrefixLocale = 'en'): string
    {
        $storeLocales = Store::getInstance()->getLocales();
        if (!is_array($storeLocales)) {
            return $fallbackRoutePrefixLocale;
        }

        $storeLocaleRoutePrefixes = array_keys($storeLocales);
        if (!is_array($storeLocaleRoutePrefixes) || empty($storeLocaleRoutePrefixes)) {
            return $storeLocaleRoutePrefixes;
        }

        return array_shift($storeLocaleRoutePrefixes);
    }

    /**
     * @param string $additionalPath
     *
     * @return string[]
     */
    protected function redirectWithLocale(string $additionalPath = ''): array
    {
        $defaultLocale = $this->getDefaultStoreRouteLocalePrefix();
        $uri = $this->getRequest()->getSchemeAndHttpHost() . '/' . $this->getUriLocale($defaultLocale);
        $uri = $this->appendQueryStringToUri($uri . $additionalPath);

        return $this->createRedirect($uri);
    }

    /**
     * @param string $toUri
     * @param int $statusCode
     *
     * @return string[]
     */
    protected function createRedirect(string $toUri, int $statusCode = 301): array
    {
        $data = ['to_url' => $toUri, 'status' => $statusCode];
        return $this->getFactory()->createRedirectResourceCreator()->createResource($this->getApplication(), $data);
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    protected function appendQueryStringToUri(string $uri): string
    {
        $queryString = $this->getRequest()->getQueryString();
        if (is_string($queryString) && strlen($queryString) > 0) {
            return $uri . '?' . $queryString;
        }

        return $uri;
    }

    /**
     * @param string $defaultLocale
     *
     * @return string
     */
    protected function getUriLocale(string $defaultLocale = 'en'): string
    {
        $browserLocale = $this->detectBrowserLocale();
        if ($this->isLocaleAvailableInCurrentStore($browserLocale)) {
            return $browserLocale;
        }

        return $defaultLocale;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    protected function isLocaleAvailableInCurrentStore(string $locale): bool
    {
        return array_key_exists($locale, $this->getFactory()->getStoreClient()->getCurrentStore()->getAvailableLocaleIsoCodes());
    }

    /**
     * @return null|string
     */
    protected function detectBrowserLocale(): ?string
    {
        return $this->getFactory()->createBrowserDetectorLanguage()->getLanguage();
    }

    /**
     * @return bool
     */
    protected function isCrawler(): bool
    {
        return $this->getFactory()->createCrawlerDetect()->isCrawler();
    }

    /**
     * @param string $pathinfo
     *
     * @return bool
     */
    protected function hasTrailingSlash(string $pathinfo): bool
    {
        return substr($pathinfo, -1) == '/';
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest(): Request
    {
        return $this->getApplication()['request'];
    }
}
