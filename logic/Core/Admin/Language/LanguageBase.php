<?php

namespace Logic\Core\Admin\Language;

use Logic\Core\BaseLogic;
use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Services\FlagCodes;
use Logic\Core\Form\Language as Form;

abstract class LanguageBase extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    /** @var Form */
    protected $form;

    public function __construct(ITranslator $translator, EntityManager $em, FlagCodes $flagCodes, Form $form = null)
    {
        parent::__construct($translator);

        $this->em = $em;
        $this->form = !$form ? new Form($this->em, $flagCodes->getFlagCodeOptions()) : $form;
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