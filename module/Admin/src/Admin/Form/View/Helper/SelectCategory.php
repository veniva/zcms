<?php

namespace Admin\Form\View\Helper;


use Admin\CategoryTree\CategoryTree;
use Zend\Form\Element\Select;
use Zend\Form\View\Helper\FormSelect;

class SelectCategory extends FormSelect
{
    public function __invoke(CategoryTree $categoryTree, $selectedCategoryId = null, $route = null, $idRouteOption = 'id')
    {
        $view = $this->getView();
        $element = new Select('filter_category');
        $element->setAttribute('id', 'filter_category');
        $categories = $categoryTree->getCategories();

        $selected = null;

        if($route){//set the url route as options value
            $options = [$view->langUrl($route) => $view->translate('All categories')];
            foreach($categories as $category){
                $options[$view->langUrl($route, [$idRouteOption => $category['id']])] = $category['indent'].$category['title'];
            }
            $selected = $view->langUrl($route, [$idRouteOption => $selectedCategoryId]);

        }else{//set the category id as option value
            $options = ['' => $view->translate('All categories')];
            foreach($categories as $category){
                $options[$category['id']] = $category['indent'].$category['title'];
            }
            $selected = $selectedCategoryId;
        }

        $element->setValueOptions($options);
        $element->setValue($selected);

        return $this->render($element);
    }
}