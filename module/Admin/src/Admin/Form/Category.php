<?php

namespace Admin\Form;

use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Service\Invokable\Misc;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\I18n\Translator\Translator;
use Zend\Validator;
use Application\Validator\ValidatorMessages;

class Category
{
    /**
     * @var \Zend\Form\Form
     */
    protected $form;

    public function __construct(CategoryContent $categoryContentEntity, Collection $languages, Translator $translator)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($categoryContentEntity);
        $inputFilter = $form->getInputFilter();

        foreach($languages as $language){
            if($language instanceof Lang){
                if($language->getId() != Misc::getDefaultLanguage()->getId()){
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

                    //region Retrieve filters and validator defined in entity via annotations and re-create those for the title field
                    $titleValidators = [];
                    foreach($inputFilter->get('title')->getValidatorChain()->getValidators() as $validator){
                        if(isset($validator['instance'])){
                            $titleValidators[] = $validator['instance'];
                        }
                    }

                    $titleFilters = [];
                    foreach($inputFilter->get('title')->getFilterChain()->getFilters() as $filter){
                        if($filter instanceof \Zend\InputFilter\InputFilterInterface){
                            $titleFilters[] = $filter;
                        }
                    }

                    $inputFilter->add(array(
                        'validators' => $titleValidators,
                        'filters' => $titleFilters,
                        'required' => false, //also allow empty value
                    ), 'title_'.$language->getIsoCode());
                    //endregion
                }
            }
        }

        $form->add(array(
            'name' => 'sort',
            'type' => 'Number',
            'options' => array(
                'label' => 'Sort'
            ),
            'attributes' => array(
                'maxlength' => 3,
                'class' => 'numbers',
            ),
        ));

        $form->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        $inputFilter->add(array(
            'validators' => array(
                array('name' => 'Digits'),
            ),
            'required' => false,//also allow empty value
        ), 'sort');
        $form->setInputFilter($inputFilter);

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