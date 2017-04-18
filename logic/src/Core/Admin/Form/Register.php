<?php

namespace Logic\Core\Admin\Form;

use Doctrine\ORM\EntityManager;
use Logic\Core\Model\Entity\User as UserEntity;
use Logic\Core\Form\Language;

class Register extends User
{
    public function __construct(UserEntity $loggedInUser, EntityManager $entityManager, array $flagCodeOptions)
    {
        parent::__construct($loggedInUser, $entityManager);

        $this->get('submit')->setValue('Submit');

        //add language name + select flag
        $languageForm = new Language($entityManager, $flagCodeOptions);
        $this->add($languageForm->get('isoCode'));
        $languageName = $languageForm->get('name');
        $languageName->setName('language_name');
        $this->add($languageName);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'filters' => [
                ['name' => 'StripTags'],
                ['name' => 'StringTrim'],
            ]
        ], 'isoCode');
        $languageNameInputFilter = $languageForm->getInputFilter()->get('name');
        $languageNameInputFilter->setName($languageName->getName());
        $inputFilter->add($languageNameInputFilter);
        $inputFilter->get('role')->setRequired(false);
    }
}