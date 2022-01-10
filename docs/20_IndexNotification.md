# Index Notification Section
This bundle allows you to submit your updated/removed documents/objects to a given index - a so called `worker`.
Currently, we only support [google](./IndexNotification/Worker/01_GoogleWorker.md) via the google index API.

To use this feature, you need to enable it and you also need to add your own resource processor.

## Enable Index Notifications

```yaml
seo:
    index_provider_configuration:
        enabled_worker:
            -   worker_name: google_index
                worker_config:
                    auth_config: config/pimcore/google-api-private-key.json
```

## Enable Pimcore Element Watcher
It's a good idea to enable the `pimcore_element_watcher` by default. 
It will auto-submit changed/delete objects/documents to all available processors.
The object/documents will be added to a queue automatically (but only if a processor has been found). 
 
```yaml
seo:
    index_provider_configuration:
        pimcore_element_watcher:
            enabled: true
```
,
## Register Service

```yaml
App\Seo\ResourceProcessor\MyProcessor:
    tags:
        - { name: seo.index.resource_processor, identifier: my_processor }
```

## Service

````php
<?php

namespace App\Seo\ResourceProcessor;

use Pimcore\Model\DataObject\Concrete;
use SeoBundle\Model\QueueEntryInterface;
use SeoBundle\Worker\WorkerResponseInterface;
use SeoBundle\ResourceProcessor\ResourceProcessorInterface;
use SeoBundle\Exception\WorkerResponseInterceptException;

class MyProcessor implements ResourceProcessorInterface
{
    /**
     * {@inheritDoc}
     */
    public function supportsWorker(string $workerIdentifier)
    {
        return in_array($workerIdentifier, ['google_index']);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsResource($resource)
    {
        if (!$resource instanceof \Pimcore\Model\DataObject\MyObject) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function generateQueueContext($resource)
    {
        $queueContext = [];

        if (!$resource instanceof Concrete) {
            return [];
        }

        // if you have multiple languages you need to add your object multiple times!
        foreach (['en', 'de'] as $locale) {
            $queueContext[] = ['locale' => $locale];
        }

        return $queueContext;
    }

    /**
     * {@inheritDoc}
     */
    public function processQueueEntry(QueueEntryInterface $queueEntry, string $workerIdentifier, array $context, $resource)
    {
        $dataUrl = null;
        $locale = $context['locale'];
        
        $linkGenerator = $this->fetchMyObjectLinkGenerator();
        $link = $linkGenerator->generate($resource, ['locale' => $locale]);

        $queueEntry->setDataType('object');
        $queueEntry->setDataId($resource->getId());
        $queueEntry->setDataUrl($link);

        return $queueEntry;
    }

    /**
     * {@inheritDoc}
     */
    public function processWorkerResponse(WorkerResponseInterface $workerResponse)
    {
        // There are already some basic logs in your application logger!
        // @todo: add custom "nice" log to a specific output...

        // throw intercepted response exception.
        // this will stop SEO Bundle from logging default data to application logger.
        // if you want to keep the logging, just remove this exception.
 
        throw new WorkerResponseInterceptException();
    }
}
````