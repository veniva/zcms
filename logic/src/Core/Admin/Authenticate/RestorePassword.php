<?php

namespace Logic\Core\Admin\Authenticate;

use Doctrine\ORM\EntityManager;
use Logic\Core\Adapters\Interfaces\ITranslator;
use Logic\Core\Admin\Form\RestorePasswordForm;
use Logic\Core\Adapters\Interfaces\ISendMail;
use Logic\Core\BaseLogic;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\PasswordResets;
use Logic\Core\Model\Entity\User;
use Logic\Core\Result;
use Zend\Form;
use Zend\InputFilter;
use Logic\Core\Stdlib;
use Zend\Mail;

class RestorePassword extends BaseLogic
{
    const ERR_NOT_FOUND = 'restore.not-found';
    const ERR_NOT_ALLOWED = 'restore.not-allowed';
    const ERR_SEND_MAIL = 'restore.mail-not-sent';

    protected $form;
    /** @var ITranslator  */
    protected $translator;

    public function __construct(RestorePasswordForm $form, ITranslator $translator)
    {
        parent::__construct($translator);

        $this->form = $form;
        $this->translator = $translator;
    }

    public function getAction() :Form\Form
    {
        return $this->form;
    }

    public function postAction(array $data, EntityManager $em) :Result
    {
        $form = $this->form;
        $form->setData($data);
        if($form->isValid()){
            $email = $form->get('email')->getValue();//get the filtered value

            //Check if the email is present in the DB
            $user = $em->getRepository(User::class)->findOneByEmail($email);
            if(!$user){
                return $this->result(self::ERR_NOT_FOUND, 'The email entered is not present in our database');
            }

            $allowed = $this->isAllowed($user);
            if(!$allowed){
                return $this->result(self::ERR_NOT_ALLOWED, 'The user with email %s does not have administrative privileges', [
                    'email' => $email
                ]);
            }

            return $this->result(StatusCodes::SUCCESS, null, [
                'email' => $email
            ]);
        }

        return $this->result(StatusCodes::ERR_INVALID_FORM, StatusMessages::ERR_INVALID_FORM_MSG, [
            'form' => $form
        ]);
    }

    public function isAllowed(User $user): bool
    {
        return $user->getRole() <= $user::USER_ADMIN;
    }

    /**
     * @param EntityManager $em
     * @param ISendMail $sendMail
     * @param array $data keys are [email, token, no-reply, message]
     * @return Result
     */
    public function persistAndSendEmail(EntityManager $em, ISendMail $sendMail, array $data): Result
    {
        $passwordResetsEntity = new PasswordResets($data['email'], $data['token']);
        $em->getRepository(PasswordResets::class)->deleteOldRequests();
        $em->persist($passwordResetsEntity);
        $em->flush();

        try{
            $sendMail->send($data['no-reply'], $data['email'], $data['subject'], $data['message']);
        }catch(\Exception $ex){
            return $this->result(self::ERR_SEND_MAIL, 'Error sending the email message. Please try again');
        }
        return $this->result(StatusCodes::SUCCESS, 'A link was generated and sent to %s', [
            'email' => $data['email']
        ]);
    }

    public function form() :Form\Form
    {
        $email = new Form\Element\Email('email');
        $email->setLabel('Registered email');
        $email->setAttribute('required', 'required');

        $form = new Form\Form('password_forgotten');
        $form->add($email);

        $emailInput = new InputFilter\Input('email');
        $emailInput->getFilterChain()->attachByName('StringTrim');
        $emailInput->getValidatorChain()->attachByName('EmailAddress');

        $inputFilter = new InputFilter\InputFilter();
        $inputFilter->add($emailInput);
        $form->setInputFilter($inputFilter);

        return $form;
    }
}