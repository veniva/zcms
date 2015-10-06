<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 03/10/2015
 * Time: 21:35
 */

namespace Application\Paginator;


use Zend\Paginator\Adapter\AdapterInterface;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\ORM\Query;

//@Source http://stackoverflow.com/questions/6635194/doctrine-2-and-zend-paginator
//@Source http://framework.zend.com/manual/current/en/modules/zend.paginator.advanced.html
class DoctrineAdapter implements AdapterInterface
{
    protected $query;
    /**
     * @var DoctrinePaginator
     */
    protected $doctrinePaginator;

    public function __construct(Query $query)
    {
        $this->query = $query;
        $this->doctrinePaginator = $this->doctrinePaginator($query);
    }

    public function count()
    {
        return $this->doctrinePaginator->count();
    }

    public function getItems($offset, $itemsPerPage)
    {
        $this->query->setFirstResult($offset)->setMaxResults($itemsPerPage);
        return iterator_to_array($this->doctrinePaginator);
    }

    protected function doctrinePaginator(Query $query, $isFetchJoinQuery = true)
    {
        return new DoctrinePaginator($query, $isFetchJoinQuery);
    }
}