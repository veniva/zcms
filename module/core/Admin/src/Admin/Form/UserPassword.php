<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2016 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;


use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;

class UserPassword extends Fieldset implements InputFilterProviderInterface
{
    public function __construct()
    {
        parent::__construct('password_fields');

        $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'options' => array(
                'label' => 'Password'
            )
        ));

        $this->add(array(
            'name' => 'password_repeat',
            'type' => 'password',
            'options' => array(
                'label' => 'Password repeat'
            ),
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
        return array(
            'password' => array(
                'filters'    => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name' => 'Admin\Validator\Password',
                    )
                ),
            ),
            'password_repeat' => array(
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
            ),
        );
    }
}