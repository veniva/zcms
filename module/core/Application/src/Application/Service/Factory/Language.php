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
        $languageClassName = get_class($languageEntity);

        $activeLanguages = $entityManager->getRepository($languageClassName)->matching($criteria);
        $language->setActiveLanguages($activeLanguages);

        $defaultLanguage = $entityManager->getRepository($languageClassName)->findOneByStatus(Lang::STATUS_DEFAULT);
        $defaultLanguage = $defaultLanguage ?: new Lang();
        $language->setDefaultLanguage($defaultLanguage);

        $request = $serviceLocator->get('Request');
        $router = $serviceLocator->get('Router');
        $match = $router->match($request);
        if($match){
            $matchedLangIso = $match->getParam('lang', $defaultLanguage->getIsoCode());
            if($matchedLangIso)
                $currentLanguage = $entityManager->getRepository($languageClassName)->findOneByIsoCode($matchedLangIso);
        }

        $currentLanguage = isset($currentLanguage) ? $currentLanguage : new Lang();
        $language->setCurrentLanguage($currentLanguage);


        return $language;
    }
}