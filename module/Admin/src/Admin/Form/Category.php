<?php

namespace Admin\Form;

use Application\Model\Entity\Category as CategoryEntity;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Validator;

class Category
{
    /**
     * @var \Zend\Form\Form
     */
    protected $form;

    public function __construct(CategoryEntity $categoryEntity, Collection $languages)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($categoryEntity);
        $form->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'content',
            'options' => array(
                'target_element' => array(
                    'type' => 'Admin\Form\CategoryContentFieldset',
                ),
            ),
        ));

        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        $this->form = $form;
    }

    /**
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        return $this->form;
    }
}