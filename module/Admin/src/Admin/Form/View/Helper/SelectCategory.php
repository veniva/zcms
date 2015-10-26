<?php

namespace Admin\Form\View\Helper;


use Admin\CategoryTree\CategoryTree;
use Zend\Form\Element\Select;
use Zend\Form\View\Helper\FormSelect;

class SelectCategory extends FormSelect
{
    public function __invoke(CategoryTree $categoryTree, $selectedCategoryId = null, $route = 'home', $idRouteOption = 'id')
    {
        $view = $this->getView();
        $element = new Select('filter_category');
        $element->setAttribute('id', 'filter_category');
        $categories = $categoryTree->getCategories();

        $selected = null;
        $options = [$view->langUrl($route) => $view->translate('All categories')];
        foreach($categories as $category){
            if($category['id'] == $selectedCategoryId)
                $selected = $view->langUrl($route, [$idRouteOption => $category['id']]);

            $options[$view->langUrl($route, [$idRouteOption => $category['id']])] = $category['indent'].$category['title'];
        }
        $element->setValueOptions($options);
        $element->setValue($selected);

        return $this->render($element);
    }
}