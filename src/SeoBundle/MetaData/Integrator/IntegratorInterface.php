<?php

namespace SeoBundle\MetaData\Integrator;

use SeoBundle\Model\SeoMetaDataInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface IntegratorInterface
{
    public function setConfiguration(array $configuration): void;

    public static function configureOptions(OptionsResolver $resolver): void;

    public function getBackendConfiguration(mixed $element): array;

    public function validateBeforeBackend(string $elementType, int $elementId, array $data): array;

    public function validateBeforePersist(string $elementType, int $elementId, array $data, ?array $previousData = null): ?array;

    public function getPreviewParameter(mixed $element, ?string $template, array $data): array;

    public function updateMetaData(mixed $element, array $data, ?string $locale, SeoMetaDataInterface $seoMetadata): void;
}
