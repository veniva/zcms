<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

use Application\Model\Entity\ListingContent;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class ListingContentFieldset extends Fieldset implements InputFilterProviderInterface
{
    protected $aliasMaxLength = 255;
    protected $titleMaxLength = 255;
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
                'maxlength' => $this->titleMaxLength,
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
                            'max' => $this->titleMaxLength,
                        ),
                    )
                ),
            ),
            'text' => array(
                'filters' => array(
                    array(
                        'name' => 'Blacklist',
                        'options' => array(
                            'list' => array('<p><br></p>'),//remove the tags added by the JS text editor
                        )
                    )
                ),
                'validators' => array(
                    array('name' => 'NotEmpty'),
                ),
            ),
        );
    }
}