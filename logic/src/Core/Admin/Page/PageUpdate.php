<?php

namespace Logic\Core\Admin\Page;


use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Listing;
use Logic\Core\Model\ListingRepository;
use Logic\Core\Result;

class PageUpdate extends PageBase
{
    const ERR_PAGE_NOT_FOUND = 'p_upd.page-not-found';
    const ERR_PAGE_NOT_FOUND_MSG = 'The page to update was not found in the database';

    /**
     * @param int $id
     * @return Result
     */
    public function showForm(int $id): Result
    {
        $page = null;
        $result = $this->preparePage($id, $page);
        if($result->status !== StatusCodes::SUCCESS){
            return $result;
        }
        
        $this->helpers->addEmptyContent($page);

        $form = $this->form;
        $form->bind($page);

        if(isset($page->getCategories()[0]))
            $form->get('category')->setValueOptions($this->ct->getSelectOptions())->setValue($page->getCategories()[0]->getId());
        
        return $this->result(StatusCodes::SUCCESS, null, [
            'form' => $form,
            'page' => $page,
            'active_languages' => $this->language->getActiveLanguages()
        ]);
    }

    public function update(int $id, array $data, string $imgDir)
    {
        /** @var Listing $page */
        $page = null;
        $result = $this->preparePage($id, $page);
        if($result->status !== StatusCodes::SUCCESS){
            return $result;
        }

        $result = $this->prepareForm($page, $data, $hasImage);
        if($result->status !== StatusCodes::SUCCESS){
            return $result;
        }
        $form = $result->get('form');

        if($form->isFormValid($this->em, $page->getContent())){

            $page = $this->persistPage($form, $page);

            //is the image scheduled for removal
            if(!empty($data['image_remove']) && $page->getListingImage()){
                $this->removeListingImage($page, $imgDir);
            }

            //add new image to the Page Entity
            if($hasImage){
                if(empty($data['image_remove']) && $page->getListingImage()){
                    $this->removeListingImage($page, $imgDir);
                }

                $this->addPageImage($page, $data);
            }

            $this->em->flush();

            if($hasImage){
                $this->uploadImage($page, $imgDir, $data);
            }

            return $this->result(StatusCodes::SUCCESS, 'The page has been edited successfully');
        }

        return $this->result(StatusCodes::ERR_INVALID_FORM, 'Please check the form for errors', [
            'form' => $form,
            'page' => $page
        ]);
    }

    protected function preparePage(int $id, Listing &$page = null)
    {
        if(empty($id)){
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        /** @var ListingRepository $pageRepo */
        $pageRepo = $this->em->getRepository(Listing::class);
        $page = $pageRepo->findOneBy(['id' => $id]);

        if(!$page)
            return $this->result(self::ERR_PAGE_NOT_FOUND, self::ERR_PAGE_NOT_FOUND_MSG);

        return $this->result(StatusCodes::SUCCESS);
    }

    protected function removeListingImage(Listing $page, $pageDir)
    {
        $pageImage = $page->getListingImage();
        $pageId = $page->getId();
        $fileName = $pageDir.$pageId.'/'.$pageImage->getImageName();
        $this->fileSystem->remove($fileName);
        $this->em->remove($pageImage);
        if($this->libFileSystem->isDirEmpty($pageDir.$pageId)){
            $this->fileSystem->remove($pageDir.$pageId);
        }
    }
    
}