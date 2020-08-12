<?php

namespace SeoBundle\Tool;

use Pimcore\Model\Asset;
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
    public function generate($element, array $options = [])
    {
        if ($element instanceof Page) {
            return $this->generateForDocument($element, $options);
        } elseif ($element instanceof Asset) {
            return $this->generateForAsset($element, $options);
        } elseif ($element instanceof DataObject\Concrete) {
            return $this->generateForObject($element, $options);
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
     * @param Page  $document
     * @param array $options
     *
     * @return string|null
     */
    protected function generateForDocument(Page $document, array $options)
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
     * @param array               $options
     *
     * @return string|null
     */
    protected function generateForObject(DataObject\Concrete $object, array $options)
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

    /**
     * @param Asset $asset
     * @param array $options
     *
     * @return string|null
     */
    protected function generateForAsset(Asset $asset, array $options)
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

        if (strpos($imagePath, 'http') !== false) {
            return $imagePath;
        }

        return sprintf('%s/%s', $this->getCurrentSchemeAndHost(), ltrim($imagePath, '/'));
    }
}
