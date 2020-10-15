<?php

namespace SeoBundle\Tool;

use Pimcore\Bundle\AdminBundle\Security\User\TokenStorageUserResolver;
use Pimcore\Model\DataObject;
use Pimcore\Model\User;
use Pimcore\Tool;

class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var TokenStorageUserResolver
     */
    protected $userResolver;

    /**
     * @param TokenStorageUserResolver $userResolver
     */
    public function __construct(TokenStorageUserResolver $userResolver)
    {
        $this->userResolver = $userResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllowedLocalesForObject(?DataObject\AbstractObject $object)
    {
        if (!$object instanceof DataObject\AbstractObject) {
            return Tool::getValidLanguages();
        }

        $user = $this->userResolver->getUser();
        if (!$user instanceof User) {
            return Tool::getValidLanguages();
        }

        if ($user->isAdmin()) {
            return Tool::getValidLanguages();
        }

        $allowedView = DataObject\Service::getLanguagePermissions($object, $user, 'lView');
        $allowedEdit = DataObject\Service::getLanguagePermissions($object, $user, 'lEdit');

        if ($allowedEdit === null) {
            return Tool::getValidLanguages();
        }

        return array_keys($allowedEdit);
    }
}
