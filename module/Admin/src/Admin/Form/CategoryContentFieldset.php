<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

use Application\Model\Entity\CategoryContent;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class CategoryContentFieldset extends Fieldset implements InputFilterProviderInterface
{

    protected $maxTitleLength = 15;
    protected $maxAliasLength = 15;

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
            'attributes'=> array(
                'maxlength' => $this->maxAliasLength,
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => 'Name'
            ),
            'attributes'=> array(
                'maxlength' => $this->maxTitleLength,
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
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'max' => $this->maxAliasLength,
                        ),
                    )
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
                            'max' => $this->maxTitleLength,
                        ),
                    ),
                ),
            ),
        );
    }
}