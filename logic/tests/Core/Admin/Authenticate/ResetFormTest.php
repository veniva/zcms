<?php

namespace Logic\Tests\Core\Admin\Authenticate;


use Logic\Core\Admin\Form\ResetPassword;
use PHPUnit\Framework\TestCase;

class ResetFormTest extends TestCase
{
    public function testFormInvalid()
    {
        $form = new ResetPassword();
        $form->setData([
            'password_fields' => [
                'password' => 'Some1234'
            ]
        ]);

        $this->assertFalse($form->isValid());
    }

    public function testFormValid()
    {
        $form = new ResetPassword();
        $form->setData([
            'password_fields' => [
                'password' => 'Some1234',
                'password_repeat' => 'Some1234'
            ]
        ]);

        $this->assertTrue($form->isValid());
    }
}