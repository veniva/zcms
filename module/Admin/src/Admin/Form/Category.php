<?php

namespace Admin\Form;

use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Service\Invokable\Misc;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\I18n\Translator\Translator;
use Zend\Validator;

class Category
{
    /**
     * @var \Zend\Form\Form
     */
    protected $form;

    public function __construct(CategoryContent $categoryContentEntity, Collection $languages, Translator $translator)
    {
        $maxTitleSize = 15;
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($categoryContentEntity);
        $inputFilter = $form->getInputFilter();

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

                    $inputFilter->add(array(
                        'validators' => array(
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'max' => $maxTitleSize,
                                    'messages' => array(
                                        Validator\StringLength::TOO_LONG =>
                                            sprintf($translator->translate('The input %s is more than %%max%% characters long'),
                                                '"'.$translator->translate('Name').' ('.$language->getIsoCode().')"')
                                    )))
                        ),
                        'required' => false, //also allow empty value
                    ), 'title_'.$language->getIsoCode());
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
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => $maxTitleSize,
                        'messages' => array(
                            Validator\StringLength::TOO_LONG =>
                                sprintf($translator->translate('The input %s is more than %%max%% characters long'),
                                    '"'.$translator->translate('Name').'"')
                        ))))
        ), 'title');
        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'Digits',
                    'options' => array(
                        'messages' => array(
                            Validator\Digits::NOT_DIGITS => $translator->translate('The input must contain only digits')
                        )
                    ),
                ),
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