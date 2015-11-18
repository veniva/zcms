<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

use Application\Model\Entity\User as UserEntity;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods;

class User extends Form
{
    protected $nameMaxLength = 15;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var UserEntity
     */
    protected $loggedInUser;

    /**
     * User constructor.
     * @param UserEntity $loggedInUser
     * @param array $entityManager
     */
    public function __construct($loggedInUser, $entityManager)
    {
        $this->loggedInUser = $loggedInUser;
        $this->entityManager = $entityManager;

        parent::__construct('user');
        $this->setObject($loggedInUser)->
        setHydrator(new ClassMethods(false));

        $this->add(array(
            'name' => 'uname',
            'options' => array(
                'label' => 'User name'
            ),
            'attributes' => array(
                'maxlength' => $this->nameMaxLength,
            )
        ));

        $this->add(array(
            'name' => 'password',
            'options' => array(
                'label' => 'Password'
            ),
            'attributes' => array(
                'pattern' => '.{0}|.{'.UserEntity::PASS_MIN_LENGTH.','.UserEntity::PASS_MAX_LENGTH.'}',
            )
        ));

        $this->add(array(
            'name' => 'password_repeat',
            'options' => array(
                'label' => 'Password repeat'
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'type' => 'Email',
            'options' => array(
                'label' => 'Email'
            )
        ));

        $this->add(array(
            'name' => 'role',
            'type' => 'Select',
            'options' => array(
                'label' => 'Role',
                'value_options' => $loggedInUser->getAllowedRoleOptions()
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        $inputFilter = $this->getInputFilter();

        $inputFilter->add(array(
            'filters'    => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => $this->nameMaxLength,
                    ),
                )
            ),
        ), 'uname');

        $inputFilter->add(array(
            'filters'    => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'Admin\Validator\Password',
                )
            ),
        ), 'password');

        $inputFilter->add(array(
            'filters'    => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name'    => 'identical',
                    'options' => array(
                        'token'    => 'password',
                        'messages' => array('notSame' => 'The "Verify Password" field must match the "Password" field')
                    )
                )
            ),
            'required' => false,
        ), 'password_repeat');
    }

    public function isValid($action = null, $currentUserName = null, $currentEmail = null)
    {
        if($action == 'edit'){
            $this->getInputFilter()->get('password')->setRequired(false);
        }
        if(!empty($this->get('password')->getValue()))
            $this->getInputFilter()->get('password_repeat')->setRequired(true);

        $userEntityClassName = get_class($this->loggedInUser);

        //region attach NoRecordExists validator to the user name
        $field = 'uname';
        $validatorOptions = array(
            'entityClass' => $userEntityClassName,
            'field' => $field,
        );
        if($currentUserName)
            $validatorOptions['exclude'][] = array('field' => $field, 'value' => $currentUserName);

        $validator = new \Application\Validator\Doctrine\NoRecordExists($this->entityManager, $validatorOptions);
        $this->getInputFilter()->get($field)->getValidatorChain()->attach($validator);
        //endregion

        //region attach NoRecordExists validator to the email
        $field = 'email';
        $validatorOptions = array(
            'entityClass' => $userEntityClassName,
            'field' => $field,
        );
        if($currentEmail)
            $validatorOptions['exclude'][] = array('field' => $field, 'value' => $currentEmail);

        $validator = new \Application\Validator\Doctrine\NoRecordExists($this->entityManager, $validatorOptions);
        $this->getInputFilter()->get($field)->getValidatorChain()->attach($validator);
        //endregion

        return parent::isValid();
    }
}