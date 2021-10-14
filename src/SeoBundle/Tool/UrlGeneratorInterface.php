<?php

namespace SeoBundle\Tool;

interface UrlGeneratorInterface
{
    public function generate(mixed $element, array $options = []): ?string;

    public function getCurrentSchemeAndHost(): string;
}
