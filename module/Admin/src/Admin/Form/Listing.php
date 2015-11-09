<?php

namespace Admin\Form;

use Application\Model\Entity;
use Application\Service\Invokable\Misc;
use Doctrine\Common\Collections\Collection;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Form;
use Zend\I18n\Translator\Translator;
use Zend\InputFilter\InputFilterInterface;
use Zend\Validator;

/**
 * v_todo - refactor this to use the fieldsets approach: http://framework.zend.com/manual/current/en/modules/zend.form.collections.html
 * v_todo - http://stackoverflow.com/questions/12002722/using-annotation-builder-in-extended-zend-form-class/18427685#18427685
 * Class Listing
 * @package Admin\Form
 */
class Listing
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct(Entity\ListingContent $listingContentEntity, Collection $languages, Translator $translator)
    {
        $annotationBuilder = new AnnotationBuilder;
        $form = $annotationBuilder->createForm($listingContentEntity);
        $metadataForm = $annotationBuilder->createForm(new Entity\Metadata());

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
            'name' => 'category',//v_todo - create multiple parent categories support
            'type' => 'Select',
            'options' => array(
                'label' => 'Category'
            ),
        ));

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
        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'Digits',
                ),
            ),
        ), 'sort');

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
                    'name' => 'File\ImageSize',
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
        foreach($inputFilter->get($inputName)->getValidatorChain()->getValidators() as $validator){
            if(isset($validator['instance'])){
                $validators[] = $validator['instance'];
            }
        }
        $filters = [];
        foreach($inputFilter->get($inputName)->getFilterChain()->getFilters() as $filter){
            if($filter instanceof InputFilterInterface){
                $filters[] = $filter;
            }
        }
        return array(
            'validators' => $validators,
            'filters' => $filters
        );
    }
}
