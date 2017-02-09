<?php

namespace Logic\Core\Admin\Authenticate;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Adapters\Interfaces\Http\IRequest;
use Logic\Core\Admin\Form\User as UserForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Lang;
use Logic\Core\Model\Entity\User;
use Admin\Form\Language;

class Register
{
    const ERR_USER_EXISTS = 1;
    const ERR_INVALID_FORM = 2;
    
    /** @var EntityManagerInterface */
    protected $em;

    /** @var User */
    protected $user;

    /** @var  array */
    protected $flagCodeOptions;
    
    public function __construct(EntityManagerInterface $em, User $user, IRequest $request, array $flagCodeOptions)
    {
        $this->em = $em;
        $this->user = $user;
        $this->flagCodeOptions = $flagCodeOptions;
    }
    
    public function getAction()
    {
        //check if user already exists
        if($this->hasUsers()){
            return [
                'status' => self::ERR_USER_EXISTS
            ];
        }

        return [
            'status' => StatusCodes::SUCCESS,
            'form' => $this->form(),
        ];
    }

    public function postAction(IRequest $request)
    {
        //check if user already exists
        if($this->hasUsers()){
            return [
                'status' => self::ERR_USER_EXISTS
            ];
        }
        
        $user = $this->user;
        $form = $this->form();
        $form->setData($request->getPost());
        $form->getInputFilter()->get('role')->setRequired(false);
        if($form->isValid()){
            $newPassword = $form->getInputFilter()->get('password_fields')->get('password')->getValue();
            if($newPassword)
                $user->setUpass($form->getInputFilter()->get('password_fields')->get('password')->getValue());
            $user->setRegDate();
            $user->setRole(User::USER_SUPER_ADMIN);
            $this->em->persist($user);

            $lang = new Lang();
            $lang->setIsoCode($form->getInputFilter()->getInputs()['isoCode']->getValue());
            $lang->setName($form->getInputFilter()->getInputs()['language_name']->getValue());
            $lang->setStatus($lang::STATUS_DEFAULT);
            $this->em->persist($lang);

            $this->em->flush();

            return [
                'status' => StatusCodes::SUCCESS,
                'message' => 'The user has been added successfully. Please log below.',
                'lang_iso' => $lang->getIsoCode()
            ];
        }

        return [
            'status' => self::ERR_INVALID_FORM,
            'form' => $form
        ];
    }
    
    protected function form()
    {
        $form = new UserForm($this->user, $this->em);
        $form->get('submit')->setValue('Submit');

        //add language name + select flag
        $languageForm = new Language($this->em, $this->flagCodeOptions);
        $form->add($languageForm->get('isoCode'));
        $languageName = $languageForm->get('name');
        $languageName->setName('language_name');
        $form->add($languageName);
        $form->getInputFilter()->add($languageForm->getInputFilter()->get('isoCode'));
        $languageNameInputFilter = $languageForm->getInputFilter()->get('name');
        $languageNameInputFilter->setName($languageName->getName());
        $form->getInputFilter()->add($languageNameInputFilter);
        
        return $form;
    }

    protected function hasUsers(): bool
    {
        return (bool)$this->em->getRepository(User::class)->countAdminUsers();
    }
}