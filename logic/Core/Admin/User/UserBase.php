<?php

namespace Logic\Core\Admin\User;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\BaseLogic;
use Logic\Core\Model\Entity\User;
use Logic\Core\Admin\Form\User as UserForm;

class UserBase extends BaseLogic
{
    /** @var EntityManager */
    protected $em;
    /** @var User */
    protected $loggedInUser;

    protected $form;

    public function __construct(ITranslator $translator, EntityManager $em, User $loggedInUser, UserForm $form = null)
    {
        parent::__construct($translator);

        $this->em = $em;
        $this->loggedInUser = $loggedInUser;

        $this->form = $form ?: new UserForm($loggedInUser, $em);
    }

    /**
     * @return UserForm|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param UserForm|null $form
     * @return UserUpdate
     */
    public function setForm($form): UserUpdate
    {
        $this->form = $form;
        return $this;
    }
}