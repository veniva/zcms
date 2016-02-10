<?php

namespace Application\Service\Factory;


use Application\Model\Entity\Lang;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\Common\Collections\Criteria;

class Language implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get('entity-manager');
        $languageEntity = $serviceLocator->get('lang-entity');

        $criteria = new Criteria();
        $criteria->where($criteria->expr()->gt('status', 0))->orderBy(['status' => Criteria::DESC]);
        $language = new \Application\Service\Invokable\Language();
        $activeLanguages = $entityManager->getRepository(get_class($languageEntity))->matching($criteria);
        $language->setActiveLanguages($activeLanguages);
        $defaultLanguage = $entityManager->getRepository(get_class($languageEntity))->findOneByStatus(Lang::STATUS_DEFAULT);
        $defaultLanguage = $defaultLanguage ?: new Lang();
        $language->setDefaultLanguage($defaultLanguage);

        return $language;
    }
}