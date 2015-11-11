<?php

namespace Admin\Form;


use Application\Model\Entity\ListingContent;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class ListingContentFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $aliasMaxLength = 255;
    protected $linkMaxLength = 20;

    public function __construct($name = 'content', array $options = [])
    {
        parent::__construct($name, $options);
        $this->setHydrator(new ClassMethods(false))
            ->setObject(new ListingContent());

        $this->add(array(
            'name' => 'link',
            'options' => array(
                'label' => 'Link title',
            ),
            'attributes'=> array(
                'maxlength' => $this->linkMaxLength,
            ),
        ));

        $this->add(array(
            'name' => 'alias',
            'options' => array(
                'label' => 'Alias',
            ),
            'attributes'=> array(
                'maxlength' => $this->aliasMaxLength,
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => 'Page title',
            ),
            'attributes'=> array(
                'maxlength' => $this->linkMaxLength,
            ),
        ));

        $this->add(array(
            'type' => 'textarea',
            'name' => 'text',
            'options' => array(
                'label' => 'Content',
            ),
            'attributes' => array(
                'class' => 'summernote'
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
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->aliasMaxLength,
                        ),
                    )
                ),
                //v_todo - check unique in the DB
            ),
            'link' => array(
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->linkMaxLength,
                        ),
                    )
                ),
            ),
            'title' => array(
                'filters' => array(
                    array('name' => 'StringTrim'),
                    array('name' => 'StripTags'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->linkMaxLength,
                        ),
                    )
                ),
            ),
            'text' => array(
                //v_todo - create a filter for <script tag only
                'validators' => array(
                    array(
                        'name' => 'NotEmpty',
                    )
                ),
            ),
        );
    }
}