<?php

namespace SeoBundle\Tool;

interface UrlGeneratorInterface
{
    /**
     * @param mixed $element
     * @param array $options
     *
     * @return string|null
     */
    public function generate($element, array $options = []);

    /**
     * @return string
     */
    public function getCurrentSchemeAndHost();
}
