<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 04/08/2015
 * Time: 15:43
 */

namespace Application\Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Category
 * @package Application\Model\Entity
 *
 * @Entity @Table(name="category_content")
 */
class CategoryContent
{
    /**
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Category", inversedBy="content")
     */
    protected $category;

    /**
     * @Column(type="integer", name="lang_id")
     */
    protected $langId;

    /**
     * @Column(type="string")
     */
    protected $alias;

    /**
     * @Column(type="string")
     */
    protected $title;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getCategory()
    {
        return $this->category;
    }

//    public function setCategory(Category $category)
//    {
//        $category->setCategoryContent($this);
//        $this->category = $category;
//    }

    public function getLangId()
    {
        return $this->langId;
    }

    public function setLangId($langId)
    {
        $this->langId = $langId;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}