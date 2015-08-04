<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/08/2015
 * Time: 14:47
 */

namespace Application\Model;

use Zend\Db\TableGateway\Feature;
use Zend\Db\Adapter\Adapter;

class Category
{
    /**
     * @var Adapter
     */
    protected $adapter;

    public function __construct()
    {
        $this->adapter = Feature\GlobalAdapterFeature::getStaticAdapter();
    }

    public function getTopCategories($lang_id = 1)
    {
        return $this->adapter->query("
            SELECT c.id, cc.alias, cc.title, cc.lang_id
            FROM `category` c
            JOIN `category_content` cc ON c.id = cc.cid
            WHERE cc.lang_id = ?
            AND c.parent_id = 0
        ", [$lang_id]);
    }
}