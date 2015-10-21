<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 22/08/2015
 * Time: 17:52
 */

namespace Application\Model;


use Application\Model\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM;
use Doctrine\ORM\QueryBuilder;
use Zend\Paginator\Paginator;

class CategoryRepository extends ORM\EntityRepository
{
    /**
     * Get the categories and the listings belonging to them in a particular parent category only
     * @param $parent
     * @param int $langId
     * @param int $type
     * @return array
     */
    public function getCategoriesListings($parent, $langId, $type = 1)
    {
        $category = new Category();
        $categoryClassName = get_class($category);
        $parent = $this->andCategoryParent($this->createQueryBuilder('c'), $parent);

        $dql = <<<TAG
            SELECT
                c, co, l, lc
            FROM $categoryClassName c
            JOIN c.content co
            JOIN c.listings l
            JOIN l.content lc
            WHERE c.type = $type
            AND $parent
            AND co.lang = $langId
            AND lc.langId = $langId
            ORDER BY c.id, c.sort, l.sort, l.id
TAG;
        $query = $this->getEntityManager()->createQuery($dql);
        $categories = $query->getArrayResult();
        foreach($categories as &$category){
            $category['content'] = $category['content'][0];
            foreach($category['listings'] as &$listing){
                $listing['content'] = $listing['content'][0];
            }
        }
        return $categories;
    }

    public function translateCategoryTitles(array $categories, $displayLang)
    {
        //replace the title of the categories with the display language category titles (if existing)
        $categoryQueryBuilder = $this->createQueryBuilder('c');
        $categoryQueryBuilder
            ->select('c', 'co', 'cl', 'lc')
            ->join('c.content', 'co')
            ->join('c.listings', 'cl')
            ->join('cl.content', 'lc')
            ->where('c.id = ?1')
            ->andWhere('co.lang = '.$displayLang)
            ->andWhere('lc.langId = '.$displayLang);
        foreach($categories as &$category){
            $categoryQueryBuilder->setParameter(1, $category['id']);
            $query = $categoryQueryBuilder->getQuery();
            $title = $alias = null;
            $listings = [];
            try{
                $categ = $query->getSingleResult(ORM\AbstractQuery::HYDRATE_ARRAY);
                $title = $categ['content'][0]['title'];
                $alias = $categ['content'][0]['alias'];
                $listings = $categ['listings'];

            }catch(ORM\NoResultException $e){}
            if($title)
                $category['content']['title'] = $title;
            if($alias)
                $category['content']['alias'] = $alias;
            //replace the listing's content with the display language listing's content
            foreach($category['listings'] as &$categList){
                foreach($listings as $listing){
                    if($categList['id'] == $listing['id']){
                        $categList['content'] = isset($listing['content'][0]) ? $listing['content'][0] : $listing['content'];
                    }
                }
            }
        }
        return $categories;
    }

    public function getCategories($parent = 0, $type = 1)
    {
        $query = $this->categoriesDQL($parent, $type);
        $categories = $query->getArrayResult();
        foreach($categories as &$category){
            $category['content'] = $category['content'][0];
        }
        return $categories;
    }

    public function getPaginatedCategories($parent = 0, $type = 1)
    {
        $query = $this->categoriesDQL($parent, $type);
        return new Paginator(new \Application\Paginator\DoctrineAdapter($query));
    }

    protected function categoriesDQL($parent, $type)
    {
        $qb = $this->createQueryBuilder('c');
        $parent = $this->andCategoryParent($qb, $parent);
        $qb->select('c', 'co')
            ->leftJoin('c.content', 'co')
            ->where('c.type='.$type)
            ->andWhere($parent)
            ->orderBy('c.id, c.sort');
        return $qb->getQuery();
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
            AND co.lang = $langId
TAG;
        $query = $this->getEntityManager()->createQuery($dql);
        $results = $query->getArrayResult();
        $result =  reset($results);
        $result['content'] = $result['content'][0];
        return $result;
    }

    public function getParentCategories(Category $parentCategory)
    {
        $parents = $parentCategory->getParents();
        $relatedParentCategories = new ArrayCollection();
        foreach($parents as $parent){
            $relatedParentCategories->add($parent);
        }
        $relatedParentCategories->add($parentCategory);

        return $relatedParentCategories;
    }

    public function countChildren($id)
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select($qb->expr()->count('ch'))
            ->leftJoin('c.children', 'ch')
            ->where('c.id ='.$id);
        $query = $qb->getQuery();
        return $query->getSingleScalarResult();
    }

    protected function andCategoryParent(QueryBuilder $qb, $parent)
    {
        return $parent = empty($parent) ? '('.$qb->expr()->isNull('c.parent').' OR c.parent=0)' : 'c.parent='.$parent;
    }
}
