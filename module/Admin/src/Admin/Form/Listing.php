<?php

namespace Admin\Form;

use Application\Model\Entity\ListingContent;
use Application\Model\Entity\Lang;
use Application\Service\Invokable\Misc;
use Application\Validator\ValidatorMessages;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Zend\I18n\Translator\Translator;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator;

class Listing
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @var ValidatorMessages
     */
    protected $validatorMessages;

    public function __construct(ListingContent $listingContentEntity, Collection $languages, Translator $translator, ValidatorMessages $validatorMessages)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($listingContentEntity);
        $inputFilter = $form->getInputFilter();
        $this->validatorMessages = $validatorMessages;

        foreach($languages as $language){
            if($language instanceof Lang){
                if($language->getId() != Misc::getDefaultLanguage()->getId()){
                    $isoCode = $language->getIsoCode();
                    //v_todo - iterate over the annotated form elements $form->getElements() and create language inputs automatically
                    //add some form elements
                    $form->add(array(
                        'name' => 'alias_'.$isoCode,
                        'type' => 'text',
                        'options' => array(
                            'label' => $form->get('alias')->getLabel()
                        ),
                    ));
                    $form->add(array(
                        'name' => 'link_'.$isoCode,
                        'type' => 'text',
                        'options' => array(
                            'label' => $form->get('link')->getLabel()
                        ),
                    ));
                    $form->add(array(
                        'name' => 'title_'.$isoCode,
                        'type' => 'text',
                        'options' => array(
                            'label' => $form->get('title')->getLabel()
                        ),
                    ));
                    $form->add(array(
                        'name' => 'text_'.$isoCode,
                        'type' => 'textarea',
                        'options' => array(
                            'label' => $form->get('text')->getLabel()
                        ),
                    ));

                    foreach(array('alias', 'link', 'title', 'text') as $inputName){
                        $this->setLanguageInputFilters($inputName, $inputFilter, $form, $isoCode);
                    }
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

        //set some custom messages to validators set via the annotation builder
        foreach(['alias', 'link', 'title'] as $inputName){
            $validatorMessages->setValidatorMessages($inputFilter->get($inputName), function()use($form,$inputFilter,$inputName){
                return '"'.$form->get($inputFilter->get($inputName)->getName())->getLabel().'"';
            });
        }

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
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Get the input validators and filters from the form defined via annotation builder in Listing entity,
     * and add those filters and validators to the newly added similar form elements
     *
     * @param string $inputName The name of the newly added element
     * @param InputFilterInterface $inputFilter
     * @param Form $form
     * @param string $isoCode The iso code for the given language (eg. "es", "en"...)
     */
    protected function setLanguageInputFilters($inputName, InputFilterInterface $inputFilter, Form $form, $isoCode)
    {
        $validators = [];
        $this->validatorMessages->setValidatorMessages($inputFilter->get($inputName), function()use($form,$inputFilter,$isoCode,$inputName){
            return '"'.$form->get($inputFilter->get($inputName)->getName())->getLabel().' ('.$isoCode.')"';
        }, $validators);
        $filters = [];
        foreach($inputFilter->get($inputName)->getFilterChain()->getFilters() as $filter){
            if($filter instanceof InputFilterInterface){
                $filters[] = $filter;
            }
        }
        $inputFilter->add(array(
            'validators' => $validators,
            'filters' => $filters,
            'required' => false, //also allow empty value
        ), $inputName.'_'.$isoCode);
    }
}
