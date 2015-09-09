<?php

namespace Application\Form;


use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterInterface;

class Contact extends Form
{
    public function __construct($name = 'contact_form')
    {
        parent::__construct($name);

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Name: '
            ),
            'attributes' => array(
                'required' => true,
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

    public function getInputFilter()
    {
        if(!$this->filter){
            $inputFilter = new InputFilter();
            $inputFactory = new InputFactory();

            $inputFilter->add($inputFactory->createInput(array(
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
            )));

            $inputFilter->add($inputFactory->createInput(array(
                'name'       => 'email',
                'filters'    => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim')
                ),
                'validators' => array(
                    array(
                        'name'    => 'EmailAddress',
                        'options' => array('messages' => array('emailAddressInvalidFormat' => 'Email format is not valid'))
                    ),
                    array(
                        'name'    => 'NotEmpty',
                        'options' => array('messages' => array('isEmpty' => '"Email" is required'))
                    )
                )
            )));

            $inputFilter->add($inputFactory->createInput(array(
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
            )));

            $this->filter = $inputFilter;
        }
        return $this->filter;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception('It is not allowed to override this input filter');
    }
}