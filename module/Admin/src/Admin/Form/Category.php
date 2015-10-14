<?php

namespace Admin\Form;

use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Service\Invokable\Misc;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;

class Category
{
    /**
     * @var \Zend\Form\Form
     */
    protected $form;

    public function __construct(CategoryContent $categoryContentEntity, Collection $languages)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($categoryContentEntity);
        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        foreach($languages as $language){
            if($language instanceof Lang){
                if($language->getId() != Misc::getDefaultLanguageID()){
                    $form->add(array(
                        'name' => 'title_'.$language->getIsoCode(),
                        'type' => 'text',
                        'options' => array(
                            'label' => 'Name'
                        ),
                    ));
                    $form->add(array(
                        'name' => 'alias_'.$language->getIsoCode(),
                        'type' => 'text',
                        'options' => array(
                            'label' => 'Alias'
                        ),
                    ));

                }
            }
        }
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