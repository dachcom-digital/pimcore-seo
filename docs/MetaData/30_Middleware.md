# SEO MetaData Middleware
Sometimes an extractor may need some more tools to create meta data.
For example, the [Schema Bundle](https://github.com/dachcom-digital/pimcore-schema) allows you to use the fancy graph builder while creating schema blocks!

## Adding Middleware

```yaml
# app/config/services.yml
services:

    AppBundle\Seo\Middleware\MyCustomAdapter:
        tags:
            - { name: seo.meta_data.middleware.adapter, identifier: my_custom_adapter }
```

```php
<?php

namespace AppBundle\Seo\Middleware;

use SeoBundle\Middleware\MiddlewareAdapterInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class MyCustomAdapter implements MiddlewareAdapterInterface
{
    /**
     * @var string
     */
    protected $string;

    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        $this->string = 'Important string!';
    }

    /**
     * {@inheritDoc}
     */
    public function getTaskArguments(): array
    {
        return [$this->string];
    }

    /**
     * {@inheritDoc}
     */
    public function onFinish(SeoMetaDataInterface $seoMetadata)
    {
        // nothing to do by default
        // in some extended usages you may want to finalize and add some data to the SeoMetaData object after all extractors have been dispatched.
    }
}
```

## Usage
In your extractor you could use it like this:

```php
<?php

namespace AppBundle\MetaData\Extractor;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class MyCustomExtractor implements ExtractorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($element)
    {
        return $element instanceof MyClass;
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function updateMetaData($element, ?string $locale, SeoMetaDataInterface $seoMetadata)
    {
        $graphMiddleware = $seoMetadata->getMiddleware('my_custom_adapter');

        $graphMiddleware->addTask(function (SeoMetaDataInterface $seoMetadata, string $string) {
            $seoMetadata->setTitle($string); // output: "Important string"
        });
    }
}
```