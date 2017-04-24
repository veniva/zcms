<?php

namespace Logic\Core\Admin\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class RestorePasswordForm extends Form implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('password_forgotten');
        
        $this->add(array(
            'name' => 'email',
            'options' => [
                'label' => 'Registered email'
            ],
            'attributes' => [
                'required' => 'required',
                'type' => 'email'
            ]
        ));
    }

    /**
     * Should return an array specification compatible with
     * {@link Zend\InputFilter\Factory::createInputFilter()}.
     *
     * @return array
     */
    public function getInputFilterSpecification()
    {
        return [
            'email' => [
                'filters' => [
                    ['name' => 'StringTrim']
                ],
                'validators' => [
                    ['name' => 'EmailAddress']
                ]
            ]
        ];
    }
}