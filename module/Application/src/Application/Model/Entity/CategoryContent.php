<?php
/**
 * Created by PhpStorm.
 * User: Ventsislav Ivanov
 * Date: 04/08/2015
 * Time: 15:43
 */

namespace Application\Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\Form\Annotation;

/**
 * Class CategoryContent
 * @Annotation\Name("category")
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ClassMethods")
 *
 * @Entity @Table(name="category_content")
 */
class CategoryContent
{
    /**
     * @Annotation\Exclude()
     *
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @Annotation\Exclude()
     *
     * @ManyToOne(targetEntity="Category", inversedBy="content")
     */
    protected $category;

    /**
     * @Annotation\Exclude()
     *
     * @Column(type="integer", name="lang_id")
     */
    protected $langId;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Options({"label": "Alias"})
     *
     * @Column(type="string")
     */
    protected $alias;

    /**
     * @Annotation\Type("text")
     * @Annotation\Filter({"name": "StripTags"})
     * @Annotation\Filter({"name": "StringTrim"})
     * @Annotation\Options({"label": "Name"})
     * @Annotation\Attributes({"required": true})
     *
     * @Column(type="string")
     */
    protected $title;

    public function __construct()
    {
        $this->category = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $category->setCategoryContent($this);
        $this->category = $category;
    }

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
