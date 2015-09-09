<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 11:31
 */

namespace Application\Model;


use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Feature;

class LangTable extends AbstractTableGateway
{
    public function __construct()
    {
        $this->table = 'lang';

        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature(new Feature\GlobalAdapterFeature());
        $this->initialize();
    }

    public function getAllLangs()
    {
        return $this->select();
    }
}
