<?php

namespace Logic\Core\Admin\Language;

use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;

class LanguageUpdate extends LanguageBase
{
    public function showForm(int $id)
    {
        if($id === 0){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $language = $this->em->find(Lang::class, ['id' => $id]);
        if(!$language){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }
        
        $this->form->bind($language);

        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $this->form,
            'language' => $language
        ]);
    }
}