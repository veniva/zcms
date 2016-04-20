<?php
namespace Application\Model\Entity;

/**
 * Class CategoryContent
 * @Entity @Table(name="category_content")
 */
class CategoryContent
{
    /**
     * @Id @ManyToOne(targetEntity="Category", inversedBy="content")
     */
    protected $category;

    /**
     * @Id @ManyToOne(targetEntity="Lang", inversedBy="categoryContent")
     * @OrderBy({"status" = "DESC"})
     */
    protected $lang;

    /**
     * @Column(type="string")
     */
    protected $alias;

    /**
     * @Column(type="string")
     */
    protected $title;

    public function __construct($category = null, $lang = null)
    {
        if($category)
            $this->setCategory($category);

        if($lang)
            $this->setLang($lang);
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(Category $category)
    {
        $category->addCategoryContent($this);
        $this->category = $category;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
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
