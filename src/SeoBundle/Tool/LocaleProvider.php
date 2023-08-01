<?php

namespace SeoBundle\Tool;

use Pimcore\Model\DataObject;
use Pimcore\Model\User;
use Pimcore\Security\User\TokenStorageUserResolver;
use Pimcore\Tool;

class LocaleProvider implements LocaleProviderInterface
{
    protected TokenStorageUserResolver $userResolver;

    public function __construct(TokenStorageUserResolver $userResolver)
    {
        $this->userResolver = $userResolver;
    }

    public function getAllowedLocalesForObject(?DataObject\AbstractObject $object): array
    {
        $user = $this->userResolver->getUser();
        if (!$user instanceof User) {
            return Tool::getValidLanguages();
        }

        if (!$object instanceof DataObject\AbstractObject) {
            return $this->sortLocalesByUserDefinition($user, Tool::getValidLanguages());
        }

        if ($user->isAdmin()) {
            return $this->sortLocalesByUserDefinition($user, Tool::getValidLanguages());
        }

        $allowedView = DataObject\Service::getLanguagePermissions($object, $user, 'lView');
        $allowedEdit = DataObject\Service::getLanguagePermissions($object, $user, 'lEdit');

        if ($allowedEdit === null) {
            return $this->sortLocalesByUserDefinition($user, Tool::getValidLanguages());
        }

        return $this->sortLocalesByUserDefinition($user, array_keys($allowedEdit));
    }

    protected function sortLocalesByUserDefinition(User $user, array $locales): array
    {
        $contentLanguages = $user->getContentLanguages();

        if (!is_array($contentLanguages) || count($contentLanguages) === 0) {
            return $locales;
        }

        $orderIdKeys = array_flip($contentLanguages);

        usort($locales, static function ($l1, $l2) use ($orderIdKeys) {

            if (!isset($orderIdKeys[$l1], $orderIdKeys[$l2])) {
                return 0;
            }

            return strcmp($orderIdKeys[$l1], $orderIdKeys[$l2]);
        });

        return $locales;
    }
}
