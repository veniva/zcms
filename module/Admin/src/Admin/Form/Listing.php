<?php

namespace Admin\Form;

use Application\Model\Entity;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Zend\Validator;

/**
 * Class Listing
 * @package Admin\Form
 */
class Listing
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(Entity\Listing $listingEntity, Collection $languages)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($listingEntity);
        $form->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'content',
            'options' => array(
                'target_element' => array(
                    'type' => 'Admin\Form\ListingContentFieldset'
                ),
            ),
        ));
        $form->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'metadata',
            'options' => array(
                'target_element' => array(
                    'type' => 'Admin\Form\MetadataFieldset'
                ),
            ),
        ));

        $form->add(array(
            'name' => 'category',//v_todo - create multiple parent categories support
            'type' => 'Select',
            'options' => array(
                'label' => 'Category'
            ),
        ));

        $form->add(array(
            'name' => 'listingImage',
            'type' => 'File',
            'options' => array(
                'label' => 'Page image'
            ),
        ));

        $form->add(array(
            'name' => 'image_remove',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Remove image',
            ),
        ));

        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        //set input filters and validators
        $inputFilter = $form->getInputFilter();

        $inputFilter->add(array(
            'validators' => array(
                array('name' => 'Digits'),
            ),
        ), 'category');

        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'File\Extension',
                    'options' => array(
                        'extension' => array('jpeg', 'jpg', 'png', 'gif')
                    ),
                ),
                array(
                    'name' => 'File\Size',
                    'options' => array(
                        'max' => '50Kb',
                    ),
                ),
                array(
                    'name' => 'File\ImageSize',//v_todo - when file is not image this throws notice
                    'options' => array(
                        'maxWidth' => 300,
                        'maxHeight' => 300
                    ),
                ),
            ),
            'required' => false,
        ), 'listingImage');

        $inputFilter->add(array(
            'required' => false,
        ), 'image_remove');

        $this->form = $form;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}
