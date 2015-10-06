<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 22/08/2015
 * Time: 17:52
 */

namespace Application\Model;


use Application\Model\Entity\Category;
use Doctrine\ORM\EntityRepository;
use Zend\Paginator\Paginator;

class CategoryRepository extends EntityRepository
{
    public function getCategoriesListings($parent, $langId = 1, $type = 1)
    {
        $category = new Category();
        $categoryClassName = get_class($category);

        $dql = <<<TAG
            SELECT
                c, co, l, lc
            FROM $categoryClassName c
            JOIN c.content co
            JOIN c.listings l
            JOIN l.content lc
            WHERE c.type = $type
            AND c.parentId = $parent
            AND co.langId = $langId
            AND lc.lang = $langId
            ORDER BY c.id, c.sort, l.sort, l.id
TAG;
        $query = $this->getEntityManager()->createQuery($dql);
        $categories = $query->getArrayResult();
        foreach($categories as &$category){
            $category['content'] = $category['content'][0];
        }
        return $categories;
    }

    public function getCategories($parent = 0, $langId = 1, $type = 1)
    {
        $query = $this->categoriesDQL($parent, $langId, $type);
        $categories = $query->getArrayResult();
        foreach($categories as &$category){
            $category['content'] = $category['content'][0];
        }
        return $categories;
    }

    public function getPaginatedCategories($parent = 0, $langId = 1, $type = 1)
    {
        $query = $this->categoriesDQL($parent, $langId, $type);
        return new Paginator(new \Application\Paginator\DoctrineAdapter($query));
    }

    protected function categoriesDQL($parent, $langId, $type)
    {
        $category = new Category();
        $categoryClassName = get_class($category);

        $dql = <<<TAG
            SELECT
                c, co
            FROM $categoryClassName c
            LEFT JOIN c.content co
            WHERE c.type = $type
            AND c.parentId = $parent
            AND co.langId = $langId
            ORDER BY c.id, c.sort
TAG;
        return $this->getEntityManager()->createQuery($dql);
    }

    public  function getCategory($id, $langId = 1)
    {
        $category = new Category();
        $categoryClassName = get_class($category);

        $dql = <<<TAG
            SELECT
                c, co
            FROM $categoryClassName c
            LEFT JOIN c.content co
            WHERE c.id = $id
            AND co.langId = $langId
TAG;
        $query = $this->getEntityManager()->createQuery($dql);
        $results = $query->getArrayResult();
        $result =  reset($results);
        $result['content'] = $result['content'][0];
        return $result;
    }
}
