<?php

namespace SeoBundle\MetaData\Integrator;

interface XliffAwareIntegratorInterface
{
    public function validateBeforeXliffExport(string $elementType, int $elementId, array $data, string $locale): array;

    public function validateBeforeXliffImport(string $elementType, int $elementId, array $data, string $locale): ?array;
}
