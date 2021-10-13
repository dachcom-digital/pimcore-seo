<?php

namespace SeoBundle\Queue;

interface QueueDataProcessorInterface
{
    public function process(array $options): void;
}
