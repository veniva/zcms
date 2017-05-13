<?php

namespace Logic\Core\Admin\Language;

use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;

class LanguageCreate extends LanguageBase
{
    public function showForm()
    {
        $language = new Lang();
        $this->form->bind($language);

        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'language' => $language
        ]);
    }
    
    public function create(array $data)
    {
        $language = new Lang();
        
        $this->form->bind($language);
        $this->form->setData($data);
        
        if ($this->form->isValid()) {
            
            $result = $this->getHelpers()->getDefaultLanguage();
            if ($result->status !== StatusCodes::SUCCESS) {
                return $result;
            }
            $defaultLanguage = $result->get('default_language');
            
            $defaultLanguageId = $defaultLanguage->getId();
            $this->getHelpers()->fillDefaultContent($language, $defaultLanguageId);
            
            //if the new language is set to be the new default language, then change the current default language's status into "active"
            if (isset($data['status']) && Lang::isLanguageDefault($data['status'])) {
                $defaultLanguage->setStatus(Lang::STATUS_ACTIVE);
                $this->em->persist($defaultLanguage);
            }
            
            $this->em->persist($language);
            $this->em->flush();
            
            return $this->result(StatusCodes::SUCCESS, 'The language has been inserted successfully');
        }
        
        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $this->form,
            'language' => $language
        ]);
    }
}