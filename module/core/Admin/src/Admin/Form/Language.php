<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form;


use Doctrine\ORM\EntityManagerInterface;
use Logic\Core\Model\Entity\Lang;
use Zend\Form\Form;
use Zend\Hydrator\ClassMethods;

class Language extends Form
{
    const NAME_MAX_LENGTH = 15;

    public function __construct(EntityManagerInterface $em, array $flagCodeOptions)
    {
        parent::__construct('language');
        $lang = new Lang();
        $this->setObject($lang)->
            setHydrator(new ClassMethods(false));

        //check if there is any languages in the DB, and if there are none, then require that this first entry be with status "default"
        $langCount = $em->getRepository(get_class($lang))->countLanguages();
        $statusOptions = $langCount ? $lang->getStatusOptions() : [Lang::STATUS_DEFAULT => Lang::getStatusOptions()[Lang::STATUS_DEFAULT]];

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Name'
            ),
            'attributes' => array(
                'maxlength' => self::NAME_MAX_LENGTH,
            )
        ));

        $this->add(array(
            'name' => 'isoCode',
            'type' => 'Select',
            'options' => array(
                'label' => 'ISO code',
                'empty_option' => 'Select',
                'value_options' => $flagCodeOptions
            ),
            'attributes' => array(
                'id' => 'flags_select'
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => $statusOptions
            ),
        ));

        $this->add(array(
            'type' => 'csrf',
            'name' => 'language_csrf',
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            'attributes' => array(
                'value' => 'Edit'
            ),
        ));

        $inputFilter = $this->getInputFilter();

        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => self::NAME_MAX_LENGTH,
                    ),
                )
            ),
        ), 'name');

    }

    public function isValid($isDefault = null)
    {
        if($isDefault === true){//if the edited language is default, make the status not required as it'll be missing
            $this->getInputFilter()->get('status')->setRequired(false);
        }
        return parent::isValid();
    }
}