<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

use Admin\Validator\Base64String;
use Admin\Validator\Extension;
use Admin\Validator\IsImage;
use Application\Model\Entity;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\Validator;
use Zend\Stdlib\Hydrator\ClassMethods;
use Doctrine\Common\Collections\Collection;

/**
 * Class Listing
 * @package Admin\Form
 */
class Listing extends Form
{
    const MAX_IMAGE_SIZE = 50;//In Kb
    const ALLOWED_EXTENSIONS = 'png,jpeg,gif,jpg';

    protected $entityManager;
    protected $listingContentCollection;

    public function __construct(EntityManager $entityManager, $listingContentCollection)
    {
        parent::__construct('listing');
        $this->entityManager = $entityManager;
        $this->listingContentCollection = $listingContentCollection;

        $this->setHydrator(new ClassMethods(false))
            ->setObject(new Entity\Listing());

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
                    'type' => 'Admin\Form\ListingContentFieldset'
                ),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'metadata',
            'options' => array(
                'target_element' => array(
                    'type' => 'Admin\Form\MetadataFieldset'
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

    public function isValid()
    {
        //region attach NoRecordExists filter to the field alias to ensure uniqueness
        $listingContent = $this->listingContentCollection;
        $listingContentClassName = isset($listingContent[0]) ? get_class($listingContent[0]) : get_class(new Entity\ListingContent());
        //check if the 'alias' field is unique in the database
        $validatorOptions = [
            'entityClass' => $listingContentClassName,
            'field' => 'alias',
            'exclude' => null,
        ];

        //if the listing's content is being edited, don't compare it's aliases
        if($listingContent  instanceof Collection){
            foreach($listingContent as $content){
                $validatorOptions['exclude'][] = array('field' => 'id', 'value' => $content->getId());
            }
        }
        $validator = new \Application\Validator\Doctrine\NoRecordExists($this->entityManager, $validatorOptions);
        $this->getInputFilter()->get('content')->getInputFilter()->get('alias')->getValidatorChain()->attach($validator);
        //endregion

        return parent::isValid();
    }

    public function validateBase64Image($imageName, $base64String, &$messages = null)
    {
        $validator = new Extension(['extensions' => self::ALLOWED_EXTENSIONS]);
        if(!$validator->isValid($imageName)){
            $messages = [];
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }

        $validator = new IsImage();
        if(!$validator->isValid(base64_decode($base64String))){
            $messages = [];
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }

        $validator = new Base64String(['max' => self::MAX_IMAGE_SIZE]);
        if(!$validator->isValid($base64String)){
            $messages = [];
            foreach($validator->getMessages() as $message){
                $messages[] = $message;
            }
            return false;
        }


        return true;
    }
}
