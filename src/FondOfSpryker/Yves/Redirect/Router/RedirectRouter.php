<?php

namespace FondOfSpryker\Yves\Redirect\Router;

use Spryker\Yves\Application\Routing\AbstractRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $currentStore = $this->getFactory()->getStoreClient()->getCurrentStore();
        foreach ($currentStore->getAvailableLocaleIsoCodes() as $shortLocale => $isoLocale) {
            if (self::LOCALE_BASE_ROUTE_NAME_PREFIX . $shortLocale == $name) {
                return '/' . mb_substr($name, -2);
            }
        }

        throw new RouteNotFoundException();
    }

    /**
     * @throws
     *
     * @param string $pathinfo
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
            return $this->internalRedirectWithLocale();
        }

        // exclude api/error routes from redirects to locale and trailing slash
        if ($this->hasExceptions($pathinfo)) {
            throw new ResourceNotFoundException();
        }

        // redirect to start page if locale is not given
        if ($this->hasValidLocalePrefix($pathinfo) === false) {
            $notFound = new ResourceNotFoundException();
            throw new NotFoundHttpException(sprintf('"%s" route not found', $pathinfo), $notFound);
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
        $explodePath = explode('/', $pathinfo);
        if (empty($explodePath) || empty($explodePath[1])) {
            return false;
        }

        if (strlen($explodePath[1]) != 2) {
            return false;
        }

        $currentStore = $this->getFactory()->getStoreClient()->getCurrentStore();
        foreach ($currentStore->getAvailableLocaleIsoCodes() as $shortLocale => $isoLocale) {
            if ($shortLocale != $explodePath[1]) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function redirectWithoutTrailingSlash(): array
    {
        $data = [
            'to_url' => substr($this->getRequest()->getUri(), 0, -1),
            'status' => 301,
        ];

        return $this->getFactory()->createRedirectResourceCreator()->createResource($this->getApplication(), $data);
    }

    /**
     * @return string[]
     */
    protected function internalRedirectWithLocale(): array
    {
        $data = [
            'path' => self::LOCALE_BASE_ROUTE_NAME_PREFIX . $this->createUrlLocalePartFromBrowserLanguage(),
            'parameters' => [],
            'status' => 301,
        ];

        return $this->getFactory()->createRedirectInternalResourceCreator()->createResource($this->getApplication(), $data);
    }

    /**
     * @return string
     */
    protected function createUrlLocalePartFromBrowserLanguage(): string
    {
        // default locale
        $newLocale = $this->getDefaultLocale();

        // detect browser locale
        $browserLanguageDetection = $this->getFactory()->createBrowserDetectorLanguage()->getLanguage();
        $currentStore = $this->getFactory()->getStoreClient()->getCurrentStore();

        // if browser language detection
        if (array_key_exists($browserLanguageDetection, $currentStore->getAvailableLocaleIsoCodes())) {
            $newLocale = $browserLanguageDetection;
        }

        return $newLocale;
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
     * @return string
     */
    protected function getDefaultLocale(): string
    {
        return mb_substr($this->getApplication()['locale'], 0, 2);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest(): Request
    {
        return $this->getApplication()['request'];
    }
}
