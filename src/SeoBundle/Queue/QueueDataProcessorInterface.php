<?php

namespace SeoBundle\Queue;

interface QueueDataProcessorInterface
{
    /**
     * @param array $options
     */
    public function process(array $options);
}
