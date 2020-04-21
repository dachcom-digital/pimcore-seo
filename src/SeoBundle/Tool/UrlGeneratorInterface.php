<?php

namespace SeoBundle\Tool;

interface UrlGeneratorInterface
{
    /**
     * @param mixed $element
     *
     * @return string|null
     */
    public function generate($element);

    /**
     * @return string
     */
    public function getCurrentSchemeAndHost();
}
