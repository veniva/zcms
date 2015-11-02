<?php

namespace Admin\Form;

use Application\Model\Entity;
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

    public function __construct(Entity\ListingContent $listingContentEntity, Collection $languages, Translator $translator, ValidatorMessages $validatorMessages)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($listingContentEntity);
        $metadataForm = $annotationBuilder->createForm(new Entity\Metadata(0,0));
        $this->validatorMessages = $validatorMessages;

        $inputFilter = $form->getInputFilter();
        $metadataInputFilter = $metadataForm->getInputFilter();

        $originalFormElements = [];
        foreach($form->getElements() as $element){
            $originalFormElements[] = $element;
        }
        foreach($languages as $language){
            if($language instanceof Entity\Lang){
                if($language->getId() != Misc::getDefaultLanguage()->getId()){
                    $isoCode = $language->getIsoCode();

                    //add some form elements and input filters for the different languages
                    foreach($originalFormElements as $element){
                        $newElement = clone $element;
                        $newElement->setName($element->getName().'_'.$isoCode);// eg. title_es
                        $form->add($newElement);

                        $this->setLanguageInputFilters($element->getName(), $inputFilter, $form, $isoCode);
                    }

                    foreach($metadataForm->getElements() as $metadataElement){
                        $newMetadataElement = clone $metadataElement;
                        $newMetadataElement->setName($metadataElement->getName().'_'.$isoCode);// eg. title_es
                        $form->add($newMetadataElement);

                        $this->setMetadataLanguageFilters($metadataElement->getName(), $inputFilter, $metadataInputFilter, $metadataForm, $isoCode);
                    }
                }
            }
        }

        //add default language metadata elements and filters
        foreach($metadataForm->getElements() as $metadataElement){
            $form->add($metadataElement);

            //add the original filters to the newly added form element
            $validatorsAndFilters = $this->prepareValidatorsAndFilters($metadataElement->getName(), $metadataInputFilter, $form);
            $inputFilter->add(array(
                'validators' => $validatorsAndFilters['validators'],
                'filters' => $validatorsAndFilters['filters'],
                'required' => false, //also allow empty value
            ), $metadataElement->getName());
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
     * Add filters and validators to the newly added similar form elements
     * @param $inputName
     * @param InputFilterInterface $inputFilter
     * @param Form $form
     * @param $isoCode
     */
    protected function setLanguageInputFilters($inputName, InputFilterInterface $inputFilter, Form $form, $isoCode)
    {
        $validatorsAndFilters = $this->prepareValidatorsAndFilters($inputName, $inputFilter, $form, $isoCode);
        $inputFilter->add(array(
            'validators' => $validatorsAndFilters['validators'],
            'filters' => $validatorsAndFilters['filters'],
            'required' => false, //also allow empty value
        ), $inputName.'_'.$isoCode);
    }

    /**
     * Add filters and validators to the newly added meta tag form elements
     * @param $inputName
     * @param InputFilterInterface $inputFilter
     * @param InputFilterInterface $metadataInputFilter
     * @param Form $form
     * @param $isoCode
     */
    protected function setMetadataLanguageFilters($inputName, InputFilterInterface $inputFilter, InputFilterInterface $metadataInputFilter, Form $form, $isoCode)
    {
        $validatorsAndFilters = $this->prepareValidatorsAndFilters($inputName, $metadataInputFilter, $form, $isoCode);
        $inputFilter->add(array(
            'validators' => $validatorsAndFilters['validators'],
            'filters' => $validatorsAndFilters['filters'],
            'required' => false, //also allow empty value
        ), $inputName.'_'.$isoCode);
    }

    /**
     * Get the input validators and filters from the form defined via annotation builder in Listing entity,
     * @return array ['validators', 'filters']
     */
    protected function prepareValidatorsAndFilters($inputName, InputFilterInterface $inputFilter, Form $form, $isoCode = null)
    {
        $validators = [];
        $this->validatorMessages->setValidatorMessages($inputFilter->get($inputName), function()use($form,$inputFilter,$isoCode,$inputName){
            $isoCode = $isoCode ? ' ('.$isoCode.')' : '';
            return '"'.$form->get($inputName)->getLabel().$isoCode.'"';
        }, $validators);
        $filters = [];
        foreach($inputFilter->get($inputName)->getFilterChain()->getFilters() as $filter){
            if($filter instanceof InputFilterInterface){
                $filters[] = $filter;//v_todo - make sure clone is not necessary
            }
        }
        return array(
            'validators' => $validators,
            'filters' => $filters
        );
    }
}
