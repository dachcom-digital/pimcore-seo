# SEO MetaData Middleware
Sometimes an extractor may need some more tools to create meta data.
For example, the [Schema Bundle](https://github.com/dachcom-digital/pimcore-schema) allows you to use the fancy graph builder while creating schema blocks!

## Adding Middleware

```yaml
# app/config/services.yml
services:

    App\Seo\Middleware\MyCustomAdapter:
        tags:
            - { name: seo.meta_data.middleware.adapter, identifier: my_custom_adapter }
```

```php
<?php

namespace App\Seo\Middleware;

use SeoBundle\Middleware\MiddlewareAdapterInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class MyCustomAdapter implements MiddlewareAdapterInterface
{
    protected string $string;

    public function boot(): void
    {
        $this->string = 'Important string!';
    }

    public function getTaskArguments(): array
    {
        return [$this->string];
    }

    public function onFinish(SeoMetaDataInterface $seoMetadata): void
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

namespace App\MetaData\Extractor;

use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

class MyCustomExtractor implements ExtractorInterface
{
    public function supports(mixed $element): bool
    {
        return $element instanceof MyClass;
    }

    public function updateMetaData(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        $middleware = $seoMetadata->getMiddleware('my_custom_adapter');
        $middleware->addTask(static function (SeoMetaDataInterface $seoMetadata, string $string) {
            $seoMetadata->setTitle($string); // output: "Important string"
        });
    }
}
```