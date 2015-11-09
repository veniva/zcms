<?php

namespace Admin\Form;

use Application\Model\Entity\CategoryContent;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class CategoryContentFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $inputFilterSpec;

    public function __construct()
    {
        parent::__construct('content');
        $this
            ->setHydrator(new ClassMethodsHydrator(false))
            ->setObject(new CategoryContent())
        ;

        $this->add(array(
            'name' => 'alias',
            'options' => array(
                'label' => 'Alias'
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => 'Name'
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
            'alias' => array(
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ),
            'title' => array(
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => 15,
                        ),
                    ),

                ),
            ),
        );
    }
}