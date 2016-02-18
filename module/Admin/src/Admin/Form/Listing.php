<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;

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
        ));

        $this->add(array(
            'name' => 'image_remove',
            'type' => 'checkbox',
            'options' => array(
                'label' => 'Remove image',
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
            'validators' => array(
                array(
                    'name' => 'File\Extension',
                    'options' => array(
                        'extension' => array('jpeg', 'jpg', 'png', 'gif')
                    ),
                    'break_chain_on_failure' => true,
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
}
