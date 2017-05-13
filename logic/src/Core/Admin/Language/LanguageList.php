<?php

namespace Logic\Core\Admin\Language;

use Doctrine\ORM\EntityManager;
use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Veniva\Lbs\BaseLogic;
use Veniva\Lbs\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Lang;

class LanguageList extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    
    public function __construct(ITranslator $translator, EntityManager $em)
    {
        parent::__construct($translator);
        
        $this->em = $em;
    }

    public function getList(int $pageNumber)
    {
        $languagesPaginated = $this->em->getRepository(Lang::class)->getLanguagesPaginated();
        $languagesPaginated->setCurrentPageNumber($pageNumber);

        $i = 0;
        $languages = [];
        foreach($languagesPaginated as $language){
            $languages[$i]['id'] = $language->getId();
            $languages[$i]['isoCode'] = $language->getIsoCode();
            $languages[$i]['isDefault'] = $language->isDefault();
            $languages[$i]['name'] = $language->getName();
            $languages[$i]['statusName'] = $language->getStatusName();
            $i++;
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'title' => $this->translator->translate('Languages'),
            'langs_paginated' => $languagesPaginated,
            'lang_data' => $languages
        ]);
    }
}