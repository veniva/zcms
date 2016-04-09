<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Form\View\Helper;

use Zend\Form\View\Helper\FormSelect;
use Zend\Form\ElementInterface;

class SelectCategory extends FormSelect
{
    public function __invoke(ElementInterface $element = null, array $categories = [], $selectedCategoryId = null, $route = null, $idRouteOption = 'id')
    {
        $view = $this->getView();

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