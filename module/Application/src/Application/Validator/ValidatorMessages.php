<?php

namespace Application\Validator;


use Zend\InputFilter\InputInterface;
use Zend\I18n\Translator\Translator;
use Zend\Validator;

class ValidatorMessages
{
    /**
     * @var Translator
     * @deprecated - use Validator's translator instead
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Sets custom messages to filter input validators.
     * If provided $validators[], it will be filled with validator instances to be attached further on to a filter input,
     * otherwise the validator messages of the particular filter input provided will be amended accordingly.
     *
     * @param InputInterface $input
     * @param null|callback $inputLabel
     * @param null|array $validators
     */
    public function setValidatorMessages(InputInterface $input, $inputLabel = null, &$validators = null)
    {
        if(!is_null($validators) && !is_array($validators))
            throw new \InvalidArgumentException();

        $inputLabel = is_callable($inputLabel) ? $inputLabel() : '';
        foreach($input->getValidatorChain()->getValidators() as $validator){
            //set message to validator StringLength
            if(isset($validator['instance']) && $validator['instance'] instanceof Validator\StringLength){
                $validatorInstance = is_null($validators) ? $validator['instance'] : clone $validator['instance'];

                $validatorInstance->setMessage(sprintf($this->translator->translate('The input %s is more than %%max%% characters long'),
                    $this->translator->translate($inputLabel)), Validator\StringLength::TOO_LONG);

                if(is_array($validators))
                    $validators[] = $validatorInstance;
            }
        }
    }
}