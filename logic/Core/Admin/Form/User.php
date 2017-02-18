<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Admin\Form;

use Doctrine\ORM\EntityManager;
use Logic\Core\Model\Entity\User as UserEntity;
use Application\Validator\Doctrine\NoRecordExists;
use Zend\Form\Form;
use Zend\Hydrator\ClassMethods;

class User extends Form
{
    protected $nameMaxLength = 15;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var UserEntity
     */
    protected $loggedInUser;

    /**
     * User constructor.
     * @param UserEntity $loggedInUser
     * @param EntityManager $entityManager
     */
    public function __construct(UserEntity $loggedInUser, EntityManager $entityManager)//v_todo - replace $loggedInUser with new User (see usage)
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
            'type' => 'Logic\Core\Admin\Form\UserPassword',
            'name' => 'password_fields'
        ));

        $this->add(array(
            'type' => 'csrf',
            'name' => 'user_csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        //Attach input filters
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
                ),
                array(
                    'name' => 'Logic\Core\Validator\i18n\Alnum'
                )
            ),
        ), 'uname');
    }

    public function isValid($action = null, $currentUserName = null, $currentEmail = null, $editOwn = false)
    {
        if($action == 'edit'){
            $this->getInputFilter()->get('password_fields')->get('password')->setRequired(false);
        }
        if(!empty($this->get('password_fields')->get('password')->getValue()) || $action != 'edit')
            $this->getInputFilter()->get('password_fields')->get('password_repeat')->setRequired(true);
        if($editOwn)
            $this->getInputFilter()->get('role')->setRequired(false);

        $userEntityClassName = get_class($this->loggedInUser);

        //region attach NoRecordExists validator to the user name
        $field = 'uname';
        $validatorOptions = array(
            'entityClass' => $userEntityClassName,
            'field' => $field,
        );
        if($currentUserName)
            $validatorOptions['exclude'][] = array('field' => $field, 'value' => $currentUserName);

        $validator = new NoRecordExists($this->entityManager, $validatorOptions);
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

        $validator = new NoRecordExists($this->entityManager, $validatorOptions);
        $this->getInputFilter()->get($field)->getValidatorChain()->attach($validator);
        //endregion

        return parent::isValid();
    }
}