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
use phpDocumentor\Reflection\DocBlock\Tag;

class CategoryRepository extends EntityRepository
{
    public function getAllTopCategories($langId = 1, $type = 1)
    {
        $category = new Category();
        $categoryClassName = get_class($category);

        $dql = <<<TAG
SELECT c, co, l, lc
FROM $categoryClassName c
JOIN c.content co
JOIN c.listings l
JOIN l.content lc
WHERE c.type = $type
AND c.parentId = 0
AND co.langId = $langId
AND lc.lang = $langId
ORDER BY c.sort, l.sort, l.id
TAG;
        $categories = $this->getEntityManager()->createQuery($dql)->getArrayResult();
        foreach($categories as &$category){
            $category['content'] = $category['content'][0];
        }
        return $categories;
    }
}