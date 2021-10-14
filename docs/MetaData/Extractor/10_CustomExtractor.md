# Custom Extractor
Integrating a custom extractor is very easy.

```yaml
App\MetaData\Extractor\DescriptionExtractor:
    tags:
        - {name: seo.meta_data.extractor, identifier: my_object_description }
```

```php
<?php

namespace App\MetaData\Extractor;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class DescriptionExtractor implements ExtractorInterface
{
    public function supports(mixed $element): void
    {
        return $element instanceof MyObject;
    }

    public function updateMetaData($element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        $seoMetadata->setMetaDescription($element->getDescription());
    }
}
```