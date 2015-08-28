<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 04/08/2015
 * Time: 12:14
 */

namespace Application\Model;


use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\Feature;

class ListingsTable extends AbstractTableGateway
{
    public function __construct()
    {
        $this->table = 'listings';
        $this->featureSet = new Feature\FeatureSet();
        $this->featureSet->addFeature(new Feature\GlobalAdapterFeature());
        $this->adapter = Feature\GlobalAdapterFeature::getStaticAdapter();
    }

    public function getCategoryListings($cid)
    {
        return $this->getAdapter()->query("
            SELECT *
            FROM listings l
            JOIN listing_category lc ON l.id = lc.listing_id
            WHERE lc.category_id = ?
        ", [(int)$cid]);
    }
}