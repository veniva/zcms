<?php

namespace Logic\Core\Admin\Page;

use Logic\Core\Admin\Form\Page;
use Veniva\Lbs\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Admin\Form\Page as PageForm;
use Veniva\Lbs\Result;

class PageCreate extends PageBase
{
    const ERR_NO_CATEGORY = 'p_create.no-categ-available';
    const ERR_NO_CATEGORY_MSG = 'You must create at least one category in order to add pages';

    public function showForm(int $parentFilter = 0)
    {
        $checkResult = $this->verifyCategory();
        if($checkResult->status !== StatusCodes::SUCCESS){
            return $checkResult;
        }

        $form = new PageForm();
        $page = new Listing();
        $this->helpers->addEmptyContent($page);
        
        $form->bind($page);
        $form->get('category')->setValueOptions($this->ct->getSelectOptions())->setValue($parentFilter);
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $form,
            'page' => $page,
            'active_languages' => $this->language->getActiveLanguages()
        ]);
    }

    public function create(array $data, string $imgDir)
    {
        $checkResult = $this->verifyCategory();
        if($checkResult->status !== StatusCodes::SUCCESS){//returns error and message
            return $checkResult;
        }
        
        $page = new Listing();

        $result = $this->prepareForm($page, $data, $hasImage);
        if($result->status !== StatusCodes::SUCCESS){//returns invalid form with message, form and page
            return $result;
        }
        /** @var Page $form */
        $form = $result->get('form');
        if($form->isFormValid($this->em)){
            $page = $this->persistPage($form, $page);

            //add new image to the Page Entity
            if($hasImage){
                $this->addPageImage($page, $data);
            }

            $this->em->flush();

            if($hasImage){
                $this->uploadImage($page, $imgDir, $data);
            }

            return $this->result(StatusCodes::SUCCESS, 'The page has been created successfully');
        }

        return $this->result(StatusCodes::ERR_INVALID_FORM, 'Please check the form for errors', [
            'form' => $form,
            'page' => $page
        ]);
    }

    //check if there is at least one category available
    public function verifyCategory(): Result
    {
        $categoryNumber = $this->em->getRepository(Category::class)->countAll();
        if(!$categoryNumber){
            return $this->result(self::ERR_NO_CATEGORY, self::ERR_NO_CATEGORY_MSG);
        }
        return $this->result(StatusCodes::SUCCESS);
    }
}