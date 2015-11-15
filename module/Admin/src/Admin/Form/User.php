<?php
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

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct('user');
        $user = new UserEntity();
        $this->setObject($user)->
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
                'value_options' => $user->getRoleOptions()
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

    public function isValid($action = null, $currentUserName = null, $userEntityClassName = null)
    {
        if($action == 'edit'){
            $this->getInputFilter()->get('password')->setRequired(false);
        }
        if(!empty($this->get('password')->getValue()))
            $this->getInputFilter()->get('password_repeat')->setRequired(true);

        //attach NoRecordExists validator to the user name
        $validatorOptions = array(
            'entityClass' => $userEntityClassName ?: get_class(new \Application\Model\Entity\User()),
            'field' => 'uname',
        );
        if($currentUserName)
            $validatorOptions['exclude'][] = array('field' => 'uname', 'value' => $currentUserName);

        $validator = new \Application\Validator\Doctrine\NoRecordExists($this->entityManager, $validatorOptions);
        $this->getInputFilter()->get('uname')->getValidatorChain()->attach($validator);

        return parent::isValid();
    }
}