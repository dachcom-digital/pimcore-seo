<?php

namespace SeoBundle\Tool;

use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGenerator implements UrlGeneratorInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($element)
    {
        if ($element instanceof Page) {
            return $this->generateForDocument($element);
        }

        if ($element instanceof DataObject\Concrete) {
            return $this->generateForObject($element);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentSchemeAndHost()
    {
        $scheme = $this->requestStack->getMasterRequest()->getScheme();
        $host = $this->requestStack->getMasterRequest()->getHost();

        return sprintf('%s://%s', $scheme, $host);
    }

    /**
     * @param Page $document
     *
     * @return string|null
     */
    protected function generateForDocument(Page $document)
    {
        $url = null;

        try {
            $url = $document->getUrl();
        } catch (\Exception $e) {
            return null;
        }

        return $url;
    }

    /**
     * @param DataObject\Concrete $object
     *
     * @return string|null
     */
    protected function generateForObject(DataObject\Concrete $object)
    {
        $linkGenerator = $object->getClass()->getLinkGenerator();
        if ($linkGenerator instanceof DataObject\ClassDefinition\LinkGeneratorInterface) {
            $link = $linkGenerator->generate($object, []);
            if (strpos($link, 'http') === false) {
                $link = sprintf('%s/%s', $this->getCurrentSchemeAndHost(), ltrim($link, '/'));
            }

            return $link;
        }

        return null;
    }
}
