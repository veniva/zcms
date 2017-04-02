<?php

namespace Logic\Core\Admin\Language;

use Logic\Core\Interfaces\StatusCodes;
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
}