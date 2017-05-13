<?php

namespace Logic\Core\Admin\Language;

use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;
use Veniva\Lbs\Result;

class LanguageUpdate extends LanguageBase
{
    public function showForm(int $id): Result
    {
        $result = $this->checkIdIsValid($id);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $result;
        }
        $language = $result->get('language');
        
        $this->form->bind($language);

        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'language' => $language
        ]);
    }
    
    public function update(int $id, array $data): Result
    {
        $result = $this->checkIdIsValid($id);
        if ($result->status !== StatusCodes::SUCCESS) {
            return $result;
        }
        $language = $result->get('language');
        if($language->isDefault()) unset($data['status']);//ensures no status can be changed if lang is default

        $this->form->bind($language);
        $this->form->setData($data);

        if ($this->form->isValid($language->isDefault())) {
            //if the new language is set to be the new default language, then change the current default language's status into "active"
            if (isset($data['status']) && Lang::isLanguageDefault($data['status'])) {
                $result = $this->getHelpers()->getDefaultLanguage();
                if ($result->status !== StatusCodes::SUCCESS) {
                    return $result;
                }
                $defaultLanguage = $result->get('default_language');

                $defaultLanguage->setStatus(Lang::STATUS_ACTIVE);
                $this->em->persist($defaultLanguage);
            }
            
            $this->em->persist($language);
            $this->em->flush();

            return $this->result(StatusCodes::SUCCESS, 'The language has been updated successfully');
        }

        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $this->form,
            'language' => $language
        ]);
    }
    
    public function checkIdIsValid(int $id): Result
    {
        if ($id === 0) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        $language = $this->em->find(Lang::class, ['id' => $id]);
        if (!$language) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'language' => $language
        ]);
    }
}