<?php

namespace Application\Service\Factory;

use Logic\Core\Model\Entity\Lang;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Doctrine\Common\Collections\Criteria;

class Language implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('entity-manager');
        $languageEntity = $container->get('lang-entity');
        
        $criteria = new Criteria();
        $criteria->where($criteria->expr()->gt('status', 0))->orderBy(['status' => Criteria::DESC]);
        $language = new \Logic\Core\Services\Language();
        $languageClassName = get_class($languageEntity);

        $activeLanguages = $entityManager->getRepository($languageClassName)->matching($criteria);
        $language->setActiveLanguages($activeLanguages);

        $defaultLanguage = $entityManager->getRepository($languageClassName)->findOneByStatus(Lang::STATUS_DEFAULT);
        $defaultLanguage = $defaultLanguage ?: new Lang();
        $language->setDefaultLanguage($defaultLanguage);

        $request = $container->get('Request');
        $router = $container->get('Router');
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