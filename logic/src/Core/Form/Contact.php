<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Form;


use Zend\Captcha\Image;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Contact extends Form  implements InputFilterProviderInterface
{
    public function __construct($urlCaptcha, $publicHtml = null)
    {
        parent::__construct('contact_form');

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Name: '
            ),
            'attributes' => array(
                'required' => true,
                'class' => 'form-control'
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'type' => 'Email',
            'options' => array(
                'label' => 'Email: '
            ),
            'attributes' => array(
                'required' => true,
                'class' => 'form-control'
            ),
        ));

        $this->add(array(
            'name' => 'captcha',
            'type' => 'Captcha',
            'options' => array(
                'label' => 'Security code',
                'captcha' => new Image(array(
                    'font' => $publicHtml.'fonts/PTN77F.ttf',
                    'imgDir' => $publicHtml.'img/captcha/',
                    'imgUrl' => $urlCaptcha,
                    'dotNoiseLevel' => 20,
                    'lineNoiseLevel' => 3,
                    'imgAlt' => 'captcha_img',
                )),
            ),
        ));

        $this->add(array(
            'name' => 'inquiry',
            'type' => 'Textarea',
            'options' => array(
                'label' => 'Question: '
            ),
            'attributes' => array(
                'required' => true,
                'class' => 'form-control'
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Submit',
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
        return [
            [
                'name'       => 'name',
                'filters'    => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array('messages' => array('isEmpty' => '"Name" is required'))
                    )
                )
            ],
            [
                'name'       => 'email',
                'filters'    => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(//v_todo - this doesn't work correctly
                    array(
                        'name'    => 'EmailAddress',
                        'options' => array('messages' => array('emailAddressInvalidFormat' => 'Email format is not valid'))
                    ),
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array('messages' => array('isEmpty' => '"Email" is required'))
                    )
                )
            ],
            [
                'name'       => 'inquiry',
                'filters'    => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array('messages' => array('isEmpty' => '"Question" is required'))
                    )
                )
            ]
        ];
    }
}
