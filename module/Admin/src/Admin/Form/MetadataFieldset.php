<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

use Application\Model\Entity\Metadata;
use Zend\Filter\ToNull;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Hydrator\ClassMethods;

class MetadataFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $maxLength = 255;

    public function __construct($name = 'metadata', array $options = [])
    {
        parent::__construct($name, $options);
        $this->setHydrator(new ClassMethods(false))
            ->setObject(new Metadata());

        $this->add(array(
            'name' => 'metaTitle',
            'options' => array(
                'label' => 'Meta title',
            ),
            'attributes' => array(
                'maxlength' => $this->maxLength,
            ),
        ));

        $this->add(array(
            'name' => 'metaDescription',
            'options' => array(
                'label' => 'Meta description',
            ),
            'attributes' => array(
                'maxlength' => $this->maxLength,
            ),
        ));

        $this->add(array(
            'name' => 'metaKeywords',
            'options' => array(
                'label' => 'Meta keywords',
            ),
            'attributes' => array(
                'maxlength' => $this->maxLength,
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
            'metaTitle' => array(
                'required' => false,
                'filters' => array(
                    array('name' =>  'StringTrim'),
                    array('name' =>  'StripTags'),
                    array(
                        'name' => 'ToNull',
                        'options' => array(
                            'type' => ToNull::TYPE_STRING
                        ),
                    ),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->maxLength
                        ),
                    ),
                ),
            ),
            'metaDescription' => array(
                'required' => false,
                'filters' => array(
                    array('name' =>  'StringTrim'),
                    array('name' =>  'StripTags'),
                    array(
                        'name' => 'ToNull',
                        'options' => array(
                            'type' => ToNull::TYPE_STRING
                        ),
                    ),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->maxLength
                        ),
                    ),
                ),
            ),
            'metaKeywords' => array(
                'required' => false,
                'filters' => array(
                    array('name' =>  'StringTrim'),
                    array('name' =>  'StripTags'),
                    array(
                        'name' => 'ToNull',
                        'options' => array(
                            'type' => ToNull::TYPE_STRING
                        ),
                    ),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->maxLength
                        ),
                    ),
                ),
            ),
        );
    }
}