<?php

namespace DachcomBundle\Test\Util;

use Pimcore\Model\Document;
use Pimcore\Tests\Util\TestHelper;

class SearchHelper
{
    public static function cleanUp()
    {
        TestHelper::cleanUp();

        // also delete all sub documents.
        $docList = new Document\Listing();
        $docList->setCondition('id != 1');
        $docList->setUnpublished(true);

        foreach ($docList->getDocuments() as $document) {
            \Codeception\Util\Debug::debug('[SEARCH] Deleting document: ' . $document->getKey());
            $document->delete();
        }

        // remove all sites (pimcore < 5.6)
        $db = \Pimcore\Db::get();
        $availableSites = $db->fetchAll('SELECT * FROM sites');
        if (is_array($availableSites)) {
            foreach ($availableSites as $availableSite) {
                $db->delete('sites', ['id' => $availableSite['id']]);
            }
        }
    }
}
