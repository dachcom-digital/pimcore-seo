<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace SeoBundle\MetaData\Extractor\ThirdParty\News;

use NewsBundle\Generator\HeadMetaGeneratorInterface;
use NewsBundle\Model\EntryInterface;
use SeoBundle\MetaData\Extractor\ExtractorInterface;
use SeoBundle\Model\SeoMetaDataInterface;

final class EntryMetaExtractor implements ExtractorInterface
{
    private HeadMetaGeneratorInterface $headMetaGenerator;

    public function __construct(HeadMetaGeneratorInterface $headMetaGenerator)
    {
        $this->headMetaGenerator = $headMetaGenerator;
    }

    public function supports(mixed $element): bool
    {
        return $element instanceof EntryInterface;
    }

    public function updateMetadata(mixed $element, ?string $locale, SeoMetaDataInterface $seoMetadata): void
    {
        $seoMetadata->setMetaDescription($this->headMetaGenerator->generateDescription($element));
        $seoMetadata->setTitle($this->headMetaGenerator->generateTitle($element));

        foreach ($this->headMetaGenerator->generateMeta($element) as $property => $content) {
            if (!empty($content)) {
                $seoMetadata->addExtraProperty($property, $content);
            }
        }
    }
}
