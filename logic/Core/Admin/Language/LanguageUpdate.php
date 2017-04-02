<?php

namespace Logic\Core\Admin\Language;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\BaseLogic;
use Logic\Core\Form\Language as Form;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;

class LanguageUpdate extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    /** @var Form */
    protected $form;
    
    public function __construct(ITranslator $translator, EntityManager $em, FlagCodes $flagCodes)
    {
        parent::__construct($translator);

        $this->em = $em;
        $this->form = new Form($this->em, $flagCodes->getFlagCodeOptions());
    }

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

    /**
     * @return Form|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     * @return LanguageUpdate
     */
    public function setForm(Form $form = null)
    {
        $this->form = $form;
        return $this;
    }
}