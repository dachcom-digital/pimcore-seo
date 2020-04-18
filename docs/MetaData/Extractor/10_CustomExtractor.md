# Custom Extractor
Integrating a custom extractor is very easy.

```yaml
AppBundle\MetaData\Extractor\DescriptionExtractor:
    tags:
        - {name: seo.meta_data.extractor, identifier: my_object_description }
```

```php
<?php

namespace AppBundle\MetaData\Extractor;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class DescriptionExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof MyObject;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetaData($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
    {
        $seoMetadata->setMetaDescription($element->getDescription());
    }
}
```