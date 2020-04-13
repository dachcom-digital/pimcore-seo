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
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var MetaDataProviderInterface
     */
    protected $metaDataProvider;

    /**
     * @var RequestHelper
     */
    protected $requestHelper;

    /**
     * @var PimcoreContextResolver
     */
    protected $pimcoreContextResolver;

    /**
     * @var DocumentResolver
     */
    protected $documentResolverService;

    /**
     * @param array                     $configuration
     * @param MetaDataProviderInterface $metaDataProvider
     * @param RequestHelper             $requestHelper
     * @param PimcoreContextResolver    $contextResolver
     * @param DocumentResolver          $documentResolverService
     */
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

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -255]
        ];
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
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

        $this->metaDataProvider->updateSeoElement($document);
    }
}
