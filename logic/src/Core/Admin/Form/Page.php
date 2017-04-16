<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Admin\Form;


use Logic\Core\Validator\Base64String;
use Logic\Core\Validator\Extension;
use Logic\Core\Validator\IsImage;
use Doctrine\ORM\EntityManager;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\Entity\ListingContent;
use Logic\Core\Validator\Doctrine\NoRecordExists;
use Zend\Form\Form;
use Zend\Validator;
use Zend\Hydrator\ClassMethods;
use Doctrine\Common\Collections\Collection;

/**
 * Class Listing
 * @package Admin\Form
 */
class Page extends Form
{
    const MAX_IMAGE_SIZE = 50;//In Kb
    const ALLOWED_EXTENSIONS = 'png,jpeg,gif,jpg';

    public function __construct()
    {
        parent::__construct('listing');

        $this->setHydrator(new ClassMethods(false))
            ->setObject(new Listing());

        $this->add(array(
            'name' => 'sort',
            'type' => 'Number',
            'options' => array(
                'label' => 'Sort',
            ),
            'attributes' => array(
                'maxlength' => 3,
                'class' => 'numbers'
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'content',
            'options' => array(
                'target_element' => array(
                    'type' => 'Logic\Core\Admin\Form\ListingContentFieldset'
                ),
            ),
        ));

        $this->add(array(
            'name' => 'category',//v_todo - create multiple parent categories support
            'type' => 'Select',
            'options' => array(
                'label' => 'Category'
            ),
        ));

        $this->add(array(
            'name' => 'listingImage',
            'type' => 'File',
            'options' => array(
                'label' => 'Page image'
            ),
            'attributes' => array(
                'data-bind' => 'fileInput: fileData'
            ),
        ));

        $this->add(array(
            'name' => 'image_remove',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Remove image',
                'use_hidden_element' => false,
            ),
        ));

        $this->add(array(
            'type' => 'csrf',
            'name' => 'listing_csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        //set input filters and validators
        $inputFilter = $this->getInputFilter();

        $inputFilter->add(array(
            'validators' => array(
                array('name' => 'Digits')
            )
        ), 'sort');

        $inputFilter->add(array(
            'validators' => array(
                array('name' => 'Digits'),
            ),
        ), 'category');

        $inputFilter->add(array(
            'required' => false,
        ), 'image_remove');
    }

    public function isFormValid(EntityManager $entityManager, $listingContent = null)
    {
        //check if the 'alias' field is unique in the database
        $validatorOptions = [
            'entityClass' => ListingContent::class,
            'field' => 'alias',
            'exclude' => null,
        ];

        //if the listing's content is being edited, don't compare it's aliases
        if($listingContent  instanceof Collection){
            foreach($listingContent as $content){
                $validatorOptions['exclude'][] = array('field' => 'listing', 'value' => $content->getListing());
                break;
            }
        }
        $validator = new NoRecordExists($entityManager, $validatorOptions);
        $inputFilter = $this->getInputFilter();
        $content = $inputFilter->get('content');
        $inputFilter = $content->getInputFilter();
        $alias = $inputFilter->get('alias');
        $validatorChain = $alias->getValidatorChain();
        $validatorChain->attach($validator);

        return parent::isValid();
    }

    public function validateBase64Image($imageName, $base64String, &$messages = [])
    {
        $validator = new Extension(['extensions' => self::ALLOWED_EXTENSIONS]);
        if(!$validator->isValid($imageName)){
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }

        $validator = new IsImage();
        if(!$validator->isValid(base64_decode($base64String))){
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }

        $validator = new Base64String(['max' => self::MAX_IMAGE_SIZE]);
        if(!$validator->isValid($base64String)){
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }


        return true;
    }
}
