<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Logic\Core\Form;

use Logic\Core\Validator\Doctrine\NoRecordExists;
use Logic\Core\Model\Entity\Category as CategoryEntity;
use Logic\Core\Model\Entity\CategoryContent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Zend\Form\Form;
use Zend\Hydrator\ClassMethods;
use Zend\Validator;

class Category extends Form
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var null|Collection
     */
    protected $contentCollection;

    public function __construct()
    {

        parent::__construct('category');
        $this->setHydrator(new ClassMethods())
            ->setObject(new CategoryEntity());

        $this->add(array(
            'type' => 'Zend\Form\Element\Collection',
            'name' => 'content',
            'options' => array(
                'target_element' => array(
                    'type' => 'Logic\Core\Admin\Form\CategoryContentFieldset',
                ),
            ),
        ));

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
            'name' => 'parent_id',
            'type' => 'Select',
            'options' => array(
                'label' => 'Category'
            ),
        ));

        $this->add(array(
            'type' => 'csrf',
            'name' => 'category_csrf',
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
            'required' => false,
            'filters' => array(
                array('name' => 'ToNull')
            ),
        ), 'parent_id');
    }

    public function isFormValid(EntityManager $entityManager, $contentCollection = null)
    {
        $contentClassName = CategoryContent::class;

        //check if the 'title' field is unique in the database
        $validatorOptions = [
            'entityClass' => $contentClassName,
            'field' => 'title',
            'exclude' => null,
        ];

        if($contentCollection instanceof Collection){
            foreach($contentCollection as $content){
                $validatorOptions['exclude'][] = array('field' => 'category', 'value' => $content->getCategory());
                break;
            }
        }
        $validator = new NoRecordExists($entityManager, $validatorOptions);
        $this->getInputFilter()->get('content')->getInputFilter()->get('title')->getValidatorChain()->attach($validator);

        return parent::isValid();
    }
}