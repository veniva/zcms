<?php

namespace Logic\Core\Validator\Doctrine;


use Logic\Core\Validator\Doctrine\AbstractValidator;
use Zend\Validator\Exception;
use Doctrine\ORM\EntityManager;

/**
 * Class NoRecordExists
 * Thanks to http://stackoverflow.com/questions/7951453/zend-validate-db-recordexists-with-doctrine-2
 * @package Application\Validator\Doctrine
 */
class NoRecordExists extends AbstractValidator
{
    private $entityClass = null;
    private $field = null;
    private $exclude = null;
    private $em = null;

    const ERROR_ENTITY_EXISTS = 1;

    protected $messageTemplates = array(
        self::ERROR_ENTITY_EXISTS => 'Another record already contains %value%'
    );

    public function __construct(EntityManager $entityManager, $options){
        $this->entityClass = $options['entityClass'];
        $this->field = $options['field'];
        if(isset($options['exclude'])) $this->exclude = $options['exclude'];
        parent::__construct($entityManager);

    }

    public function getQuery(){
        $qb = $this->getEm()->createQueryBuilder();
        $qb->select('o')
            ->from($this->entityClass,'o')
            ->where('o.' . $this->field .'=:value');

        if ($this->exclude !== null){
            if (is_array($this->exclude)){

                foreach($this->exclude as $k => $ex){
                    $qb->andWhere('o.' . $ex['field'] .' != :value'.$k);
                    $qb->setParameter('value'.$k, $ex['value'] ? $ex['value'] : '');
                }
            }
        }
        $query = $qb->getQuery();
        return $query;
    }
    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        $valid = true;

        $this->setValue($value);

        $query = $this->getQuery();
        $query->setParameter("value", $value);

        $result = $query->getResult();

        if ($result){
            $valid = false;
            $this->error(self::ERROR_ENTITY_EXISTS);
        }
        return $valid;
    }
}