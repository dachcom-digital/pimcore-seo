<?php

namespace SeoBundle\EventListener;

use Pimcore\Http\RequestHelper;
use Pimcore\Http\Request\Resolver\DocumentResolver;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\Document\Page;
use SeoBundle\MetaData\MetaDataProviderInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoMetaDataAttachListener implements EventSubscriberInterface
{
    protected array $configuration;
    protected MetaDataProviderInterface $metaDataProvider;
    protected RequestHelper $requestHelper;
    protected PimcoreContextResolver $pimcoreContextResolver;
    protected DocumentResolver $documentResolverService;

    public function __construct(
        array $configuration,
        MetaDataProviderInterface $metaDataProvider,
        RequestHelper $requestHelper,
        PimcoreContextResolver $contextResolver,
        DocumentResolver $documentResolverService
    ) {
        $this->configuration = $configuration;
        $this->metaDataProvider = $metaDataProvider;
        $this->requestHelper = $requestHelper;
        $this->pimcoreContextResolver = $contextResolver;
        $this->documentResolverService = $documentResolverService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -255]
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (php_sapi_name() === 'cli') {
            return;
        }

        if ($this->configuration['auto_detect_documents'] === false) {
            return;
        }

        if ($event->isMasterRequest() === false) {
            return;
        }

        if ($this->pimcoreContextResolver->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN)) {
            return;
        }

        if (!$this->pimcoreContextResolver->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_DEFAULT)) {
            return;
        }

        if ($this->requestHelper->isFrontendRequestByAdmin($request)) {
            return;
        }

        $document = $this->documentResolverService->getDocument($request);
        if (!$document instanceof Page) {
            return;
        }

        $this->metaDataProvider->updateSeoElement($document, $request->getLocale());
    }
}
