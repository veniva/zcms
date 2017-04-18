<?php

namespace Logic\Core\Admin\Form;

use Zend\Form\Form;

class ResetPassword extends Form
{
    public function __construct($name = 'reset_password', array $options = [])
    {
        parent::__construct($name, $options);
        
        $this->add([
            'name' => 'password_fields',
            'type' => 'Logic\Core\Admin\Form\UserPassword',
        ]);
        
        $this->add([
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => [
                'value' => 'Edit'
            ]
        ]);
        
        $this->getInputFilter()->get('password_fields')->get('password_repeat')->setRequired(true);
    }
}