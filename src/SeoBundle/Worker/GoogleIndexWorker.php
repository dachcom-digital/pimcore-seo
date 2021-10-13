<?php

namespace SeoBundle\Worker;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Pimcore\Model\Tool\TmpStore;
use Psr\Http\Message\RequestInterface;
use SeoBundle\Model\QueueEntryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GoogleIndexWorker implements IndexWorkerInterface
{
    public const TMP_STORE_MINUTE_QUOTA_KEY = 'google_index_worker_minute_quota';
    public const TMP_STORE_DAILY_QUOTA_KEY = 'google_index_worker_daily_quota';

    protected array $configuration;

    public function canProcess(): string|bool
    {
        $dailyQuota = TmpStore::get(self::TMP_STORE_DAILY_QUOTA_KEY);

        if (!$dailyQuota instanceof TmpStore) {
            return true;
        }

        $data = $dailyQuota->getData();
        $maxRequests = $data['quota'];

        if ($maxRequests > 0) {
            return true;
        }

        if ($data['log'] === false) {
            return false;
        }

        $lifeTime = $this->generateLifeTimeExceed();

        $data['log'] = false;
        $dailyQuota->setData($data);
        $dailyQuota->update($lifeTime);

        return sprintf(
            'Limit of daily requests (%d) until %s reached.',
            $this->configuration['push_requests_per_day'],
            Carbon::createFromTimestamp(time() + $lifeTime)->format('d.m.Y H:i:s')
        );
    }

    /**
     * {@inheritdoc}
     *
     * Handle Quotas: (@see https://developers.google.com/search/apis/indexing-api/v3/quota-pricing)
     *
     * We're using batched submission here (@see https://developers.google.com/search/apis/indexing-api/v3/using-api#batching)
     * So we need to chunk the entries into blocks á 40 entries max.
     *
     * Default Quotas:
     *  - Push requests per day:    200
     *  - Push request per minute:  600
     */
    public function process(array $queueEntries, array $resultCallBack): void
    {
        $client = $this->getClient();

        $batch = new \Google_Http_Batch($client, false, 'https://indexing.googleapis.com');
        $service = new \Google_Service_Indexing($client);

        $dailyQuota = TmpStore::get(self::TMP_STORE_DAILY_QUOTA_KEY);

        if ($dailyQuota instanceof TmpStore) {
            $data = $dailyQuota->getData();
            $maxRequests = $data['quota'];
        } else {
            $maxRequests = $this->configuration['push_requests_per_day'];
            $data = ['log' => true, 'quota' => $maxRequests];
        }

        $chunkedEntries = array_chunk($queueEntries, 40, false);

        foreach ($chunkedEntries as $entriesBlock) {
            if ($maxRequests <= 0) {
                break;
            }

            /** @var QueueEntryInterface $queueEntry */
            foreach ($entriesBlock as $queueEntry) {
                if ($maxRequests <= 0) {
                    break;
                }

                $postBody = new \Google_Service_Indexing_UrlNotification();
                $postBody->setType($this->getUrlType($queueEntry->getType()));
                $postBody->setUrl($queueEntry->getDataUrl());

                /** @var RequestInterface $request */
                $request = $service->urlNotifications->publish($postBody);

                $batch->add($request, $queueEntry->getUUid());

                $maxRequests--;
            }

            $results = $batch->execute();
            $this->parseResults($results, $entriesBlock, $resultCallBack);

            // throttle batch requests
            sleep(1);
        }

        $data['quota'] = $maxRequests < 0 ? 0 : $maxRequests;

        TmpStore::set(self::TMP_STORE_DAILY_QUOTA_KEY, $data, null, $this->generateLifeTimeExceed());
    }

    /**
     * Daily quotas are refreshed at midnight Pacific Standard Time.
     *
     * @see https://developers.google.com/analytics/devguides/config/mgmt/v3/limits-quotas
     */
    protected function generateLifeTimeExceed(): int
    {
        $quoteLifetime = Carbon::now('PST')->endOfDay();
        $localeQuoteLifeTime = Carbon::createFromTimestamp($quoteLifetime->getTimestamp(), CarbonTimeZone::create());

        return $localeQuoteLifeTime->diffInRealSeconds(Carbon::now());
    }

    protected function parseResults(array $results, array $processedQueueEntries, array $callable): void
    {
        foreach ($results as $queueEntryResponseId => $result) {
            $queueEntryId = str_replace('response-', '', $queueEntryResponseId);
            $this->parseResult($result, $queueEntryId, $processedQueueEntries, $callable);
        }
    }

    protected function parseResult(mixed $result, string $queueEntryId, array $processedQueueEntries, array $callable): void
    {
        $linkedQueueEntry = array_reduce($processedQueueEntries, function ($result, QueueEntryInterface $item) use ($queueEntryId) {
            return $item->getUuid() === $queueEntryId ? $item : $result;
        });

        if ($result instanceof \Google_Service_Exception) {
            $workerResponse = $this->buildErrorResponse($result, $linkedQueueEntry);
        } elseif ($result instanceof \Google_Service_Indexing_PublishUrlNotificationResponse) {
            $workerResponse = $this->buildSuccessResponse($result, $linkedQueueEntry);
        } else {
            $workerResponse = $this->buildUnknownResponse($result, $linkedQueueEntry);
        }

        // call queue manager to handle response!
        call_user_func_array($callable, [$workerResponse]);
    }

    protected function buildErrorResponse(\Google_Service_Exception $response, QueueEntryInterface $linkedQueueEntry): WorkerResponse
    {
        $formattedMessages = [];
        $formattedStatus = (int) $response->getCode();

        foreach ($response->getErrors() as $error) {
            $formattedMessages[] = $error['message'];
        }

        $formattedMessage = join(', ', $formattedMessages);

        return new WorkerResponse($formattedStatus, $formattedMessage, false, $linkedQueueEntry, $response);
    }

    protected function buildSuccessResponse(\Google_Service_Indexing_PublishUrlNotificationResponse $response, QueueEntryInterface $linkedQueueEntry): WorkerResponse
    {
        $formattedMessageMeta = [];

        $notificationMetaData = $response->getUrlNotificationMetadata();
        $latestUpdateInfo = $notificationMetaData->getLatestUpdate();
        $latestRemoveInfo = $notificationMetaData->getLatestRemove();

        if ($latestUpdateInfo instanceof \Google_Service_Indexing_UrlNotification) {
            $formattedMessageMeta[] = sprintf('Latest Update Info: %s %s', $latestUpdateInfo->getType(), $latestUpdateInfo->getNotifyTime());
        }
        if ($latestRemoveInfo instanceof \Google_Service_Indexing_UrlNotification) {
            $formattedMessageMeta[] = sprintf('Latest Remove Info: %s %s', $latestRemoveInfo->getType(), $latestRemoveInfo->getNotifyTime());
        }

        $formattedMessage = sprintf('Url "%s" successfully submitted to index. %s', $notificationMetaData->getUrl(), join(', ', $formattedMessageMeta));

        return new WorkerResponse(200, $formattedMessage, true, $linkedQueueEntry, $response);
    }

    protected function buildUnknownResponse(mixed $response, QueueEntryInterface $linkedQueueEntry): WorkerResponse
    {
        return new WorkerResponse(500, 'Unknown Error', true, $linkedQueueEntry, $response);
    }

    protected function getUrlType(string $type): string
    {
        if ($type === IndexWorkerInterface::TYPE_UPDATE) {
            return 'URL_UPDATED';
        }

        if ($type === IndexWorkerInterface::TYPE_ADD) {
            return 'URL_UPDATED';
        }

        if ($type === IndexWorkerInterface::TYPE_DELETE) {
            return 'URL_DELETED';
        }

        return 'INVALID_TYPE';
    }

    /**
     * @throws \Google\Exception
     */
    protected function getClient(): \Google_Client
    {
        $configPath = sprintf('%s/%s', PIMCORE_PROJECT_ROOT, ltrim($this->configuration['auth_config'], '/'));

        $client = new \Google_Client();
        $client->setScopes(\Google_Service_Indexing::INDEXING);
        $client->setAuthConfig($configPath);
        $client->setUseBatch(true);

        return $client;
    }

    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'push_requests_per_day'    => 200,
            'push_requests_per_minute' => 600,
            'auth_config'              => null,
        ]);

        $resolver->setAllowedTypes('push_requests_per_day', 'int');
        $resolver->setAllowedTypes('push_requests_per_minute', 'int');
        $resolver->setAllowedTypes('auth_config', 'string');
        $resolver->setRequired(['push_requests_per_day', 'push_requests_per_minute', 'auth_config']);
    }
}
