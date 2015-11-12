<?php
namespace Admin\Form;


use Application\Model\Entity\Lang;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods;

class Language extends Form
{
    protected $nameMaxLength = 15;
    protected $isoCodeMaxLength = 2;

    public function __construct()
    {
        parent::__construct('language');
        $lang = new Lang();
        $this->setObject($lang)->
            setHydrator(new ClassMethods(false));

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => 'Name'
            ),
            'attributes' => array(
                'maxlength' => $this->nameMaxLength,
            )
        ));

        $this->add(array(
            'name' => 'isoCode',
            'options' => array(
                'label' => 'ISO code'
            ),
            'attributes' => array(
                'maxlength' => $this->isoCodeMaxLength,
            )
        ));

        $this->add(array(
            'name' => 'country_img',
            'type' => 'File',
            'options' => array(
                'label' => 'Country flag'
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Select',
            'options' => array(
                'label' => 'Status',
                'empty_option' => 'Select',
                'value_options' => $lang->getStatusOptions()
            ),
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
                        'max' => $this->nameMaxLength,
                    ),
                )
            ),
        ), 'name');

        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'max' => $this->isoCodeMaxLength,
                    ),
                )
            ),
        ), 'isoCode');

        $inputFilter->add(array(
            'validators' => array(
                array(
                    'name' => 'File\Extension',
                    'options' => array(
                        'extension' => array('png'),
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
                    'name' => 'File\ImageSize',//v_todo - when file is not image this throws notice.
                    'options' => array(
                        'maxWidth' => 20,
                        'maxHeight' => 20
                    ),
                ),
            ),

            'required' => false,
        ), 'country_img');

    }

    public function isValid($newIso = null, $oldIso = null)
    {
        if(!$oldIso || $newIso != $oldIso){//if action = add or edited iso code
            $this->getInputFilter()->get('country_img')->setRequired(true);
        }
        return parent::isValid();
    }
}