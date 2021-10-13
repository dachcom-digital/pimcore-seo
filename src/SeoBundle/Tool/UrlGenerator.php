<?php

namespace SeoBundle\Tool;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document\Page;
use Symfony\Component\HttpFoundation\RequestStack;

class UrlGenerator implements UrlGeneratorInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function generate($element, array $options = []): ?string
    {
        if ($element instanceof Page) {
            return $this->generateForDocument($element, $options);
        }

        if ($element instanceof Asset) {
            return $this->generateForAsset($element, $options);
        }

        if ($element instanceof DataObject\Concrete) {
            return $this->generateForObject($element, $options);
        }

        return null;
    }

    public function getCurrentSchemeAndHost(): string
    {
        $scheme = $this->requestStack->getMainRequest()->getScheme();
        $host = $this->requestStack->getMainRequest()->getHost();

        return sprintf('%s://%s', $scheme, $host);
    }

    protected function generateForDocument(Page $document, array $options): ?string
    {
        try {
            $url = $document->getUrl();
        } catch (\Exception $e) {
            return null;
        }

        return $url;
    }

    protected function generateForObject(DataObject\Concrete $object, array $options): ?string
    {
        $linkGenerator = $object->getClass()->getLinkGenerator();
        if ($linkGenerator instanceof DataObject\ClassDefinition\LinkGeneratorInterface) {
            $link = $linkGenerator->generate($object, []);
            if (!str_contains($link, 'http')) {
                $link = sprintf('%s/%s', $this->getCurrentSchemeAndHost(), ltrim($link, '/'));
            }

            return $link;
        }

        return null;
    }

    protected function generateForAsset(Asset $asset, array $options): ?string
    {
        if (!$asset instanceof Asset\Image) {
            return null;
        }

        if (!isset($options['thumbnail']) || empty($options['thumbnail'])) {
            return null;
        }

        $thumbnail = $asset->getThumbnail($options['thumbnail']);
        if (!$thumbnail instanceof Asset\Image\Thumbnail) {
            return null;
        }

        $imagePath = $thumbnail->getPath(false);
        if (is_null($imagePath)) {
            return null;
        }

        if (str_contains($imagePath, 'http')) {
            return $imagePath;
        }

        return sprintf('%s/%s', $this->getCurrentSchemeAndHost(), ltrim($imagePath, '/'));
    }
}
