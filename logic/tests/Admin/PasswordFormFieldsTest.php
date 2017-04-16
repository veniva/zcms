<?php

namespace Tests\Admin;


use PHPUnit\Framework\TestCase;
use Zend\Form\Form;

class PasswordFormFieldsTest extends TestCase
{

    protected function createForm(&$form = null)
    {
        $form = new Form();
        $form->add(array(
            'type' => 'Logic\Core\Admin\Form\UserPassword',
            'name' => 'password_fields'
        ));
        return $form->getInputFilter();
    }

    public function testFields()
    {
        $inputFilter = $this->createForm();
        $fieldsetInputFilter = $inputFilter->get('password_fields');
        $password = $fieldsetInputFilter->get('password');
        $password->setValue('some');

        $this->assertFalse($password->isValid());
    }

    public function testFieldsTwo()
    {
        $inputFilter = $this->createForm();
        $fieldsetInputFilter = $inputFilter->get('password_fields');

        $password = $fieldsetInputFilter->get('password');
        $password->setValue('Some123456');
        $this->assertTrue($password->isValid());

        $repeat = $fieldsetInputFilter->get('password_repeat');
        $repeat->setValue('Some');
        $this->assertFalse($repeat->isValid());
    }

    public function testFieldsThree()
    {
        $form = null;
        $inputFilter = $this->createForm($form);

        $passwordFields = $form->get('password_fields');
        $passwordFields->get('password')->setValue('Some123456');
        $passwordFields->get('password_repeat')->setValue('Some123456');

        $fieldsetInputFilter = $inputFilter->get('password_fields');
        $repeat = $fieldsetInputFilter->get('password_repeat');
        $this->assertTrue($repeat->isValid());
    }

    public function testFormValidNoRepeat()
    {
        $form = null;
        $this->createForm($form);

        $form->setData([
            'password_fields' => [
                'password' => 'Some1234',
            ]
        ]);

        $this->assertTrue($form->isValid());
    }
}