<?php
namespace Admin\Form;


use Application\Model\Entity\Lang;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\ClassMethods;

class Language extends Form
{
    protected $nameMaxLength = 15;
    protected $isoCodeMaxLength = 2;

    public function __construct(ServiceLocatorInterface $serviceManager)
    {
        parent::__construct('language');
        $lang = new Lang();
        $this->setObject($lang)->
            setHydrator(new ClassMethods(false));

        $fm = $serviceManager->get('flag-codes');

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
            'type' => 'Select',
            'options' => array(
                'label' => 'ISO code',
                'empty_option' => 'Select',
                'value_options' => $fm->getFlagCodeOptions()
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

    }

    public function isValid($newIso = null, $oldIso = null, $isDefault = null)
    {
        if($isDefault === true){//if the edited language is default, make the status not required as it'll be missing
            $this->getInputFilter()->get('status')->setRequired(false);
        }
        return parent::isValid();
    }
}