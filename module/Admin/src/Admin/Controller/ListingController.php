<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Listing as ListingForm;
use Application\Model\Entity\ListingContent;
use Application\Model\Entity\ListingImage;
use Application\Model\Entity\Metadata;
use Application\Model\Entity;
use Application\Service\Invokable\Misc;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Element;
use Zend\View\Model\ViewModel;

class ListingController extends AbstractActionController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    public function listAction()
    {
        $parentCategory = $this->params()->fromRoute('id');
        $page = $this->params()->fromRoute('page');
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);

        $listingsPaginated = $listingRepository->getListingsPaginated($parentCategory);
        $listingsPaginated->setCurrentPageNumber($page);

        $categories = $categoryTree->getCategories();

        return [
            'title' => 'Pages',
            'listings' => $listingsPaginated,
            'categories' => $categories,
            'parentCategory' => $parentCategory,
            'page' => $page,
            'categoryTree' => $categoryTree,
            'defaultLanguageID' => $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId()
        ];
    }

    public function editAction()
    {
        return $this->addEditListing('edit');
    }

    public function addAction()
    {
        $return = $this->addEditListing('add');
        if($return instanceof ViewModel){
            $return->setTemplate('admin/listing/edit');
        }
        return $return;
    }

    protected function addEditListing($action)
    {
        $page = $this->params()->fromRoute('page');
        $parentFilter = $this->params()->fromRoute('filter');
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);
        if($action == 'edit'){
            $listingId = $this->params()->fromRoute('id');
            if(!$listingId){
                return $this->redir()->toRoute('admin/listing', [
                    'id' => $parentFilter,
                    'page' => $page,
                ]);
            }
        }else{
            //check if there is at least one category available
            $categoryEntity = new Entity\Category();
            $categoryNumber = $entityManager->getRepository(get_class($categoryEntity))->countAllOfType(1);
            if(!$categoryNumber){
                    $this->flashMessenger()->addErrorMessage($this->translator->translate("You must create at least one category in order to add pages"));
                    return $this->redir()->toRoute('admin/listing', [
                        'id' => $parentFilter,
                        'page' => $page,
                    ]);
            }
        }

        $languagesService = $this->getServiceLocator()->get('language');
        $languages = $languagesService->getActiveLanguages();

        if($action == 'edit'){
            $listing = $listingRepository->findOneBy(['id' => $listingId]);
            if(!$listing){
                return $this->redir()->toRoute('admin/listing', [
                    'id' => $parentFilter,
                    'page' => $page,
                ]);
            }
        }else{
            $listing = $this->getServiceLocator()->get('listing-entity');
        }

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($listing, $languages);

        $publicDir = $this->getServiceLocator()->get('config')['public-path'];
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-path'];
        $listingContent = $action == 'edit' ? $listing->getContent() : null;
        $form = new ListingForm($this->getServiceLocator()->get('entity-manager'), $listingContent);
        $form->bind($listing);
        if($action == 'edit'){
            if(isset($listing->getCategories()[0]))
                $form->get('category')->setValueOptions($categoryTree->getCategoriesAsOptions())->setValue($listing->getCategories()[0]->getId());
        }else{
            $form->get('category')->setValueOptions($categoryTree->getCategoriesAsOptions())->setValue($parentFilter);
        }

        //add form-control CSS class to some form elements
        foreach($form->getFieldsets() as $fieldset){
            foreach($fieldset->getFieldsets() as $subFieldset){
                foreach($subFieldset->getElements() as $element){
                    $inputCSSClass = !empty($element->getAttribute('class')) ? $element->getAttribute('class').' ' : '';
                    $element->setAttribute('class', $inputCSSClass.'form-control');
                }
            }
        }

        $request = $this->getRequest();
        if($request->isPost()){
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            foreach($post['content'] as &$content){//v_todo - check if this can be moved in the form's isValid()
                if(empty($content['alias'])){
                    $content['alias'] = Misc::alias($content['title']);
                }else{
                    $content['alias'] = Misc::alias($content['alias']);
                }
            }
            $form->setData($post);
            if($form->isValid()){
                $category = $entityManager->find(get_class($this->getServiceLocator()->get('category-entity')),
                    ['id' => $form->getInputFilter()->getValue('category')]);
                $listing->setOnlyCategory($category);

                $entityManager->persist($listing);

                //is the image scheduled for removal
                if($action == 'edit'){
                    if(!empty($post['image_remove']) && $listing->getListingImage()){
                        $this->removeListingImage($listing->getListingImage(), $publicDir.$imgDir, $listing->getId());
                    }
                }

                //upload new image
                $upload = false;
                if(isset($post['listingImage']) && !$post['listingImage']['error']){
                    if($action == 'edit'){
                        if(empty($post['image_remove']) && $listing->getListingImage()){
                            $this->removeListingImage($listing->getListingImage(), $publicDir.$imgDir, $listing->getId());
                        }
                    }

                    $listingImage = new ListingImage($listing);
                    $listingImage->setImageName($post['listingImage']['name']);
                    $upload = true;
                }

                $entityManager->flush();
                if($upload){
                    $uploadDir = $publicDir.$imgDir.$listing->getId();
                    if(!file_exists($uploadDir) && !is_dir($uploadDir)){
                        mkdir($uploadDir);
                    }
                    \move_uploaded_file($post['listingImage']['tmp_name'], $uploadDir.'/'.$post['listingImage']['name']);
                }

                $this->flashMessenger()->addSuccessMessage(sprintf($this->translator->translate('The page has been %s successfully'),
                        $this->translator->translate($action.'ed')));

                return $this->redir()->toRoute('admin/listing', [
                    'id' => $parentFilter,
                    'page' => $page,
                ]);

            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate("Please check the form for errors"));
            }
        }
        $viewModel = new ViewModel([
            'page' => $page,
            'filter' => $parentFilter,
            'form' => $form,
            'listing' => $listing,
            'action' => ucfirst($action),
            'image' => $listing->getListingImage() ? $imgDir.$listing->getId().'/'.$listing->getListingImage()->getImageName() : null,
            'locale' => $this->translator->getLocale(),
            'activeLanguages' => $languages,
        ]);
        return $viewModel;
    }

    protected function addEmptyContent(Entity\Listing $listing, \Doctrine\Common\Collections\Collection $languages)
    {
        $contentLangIDs = [];
        $defaultContent = null;
        $lang = $this->getServiceLocator()->get('language');
        foreach($listing->getContent() as $content){
            $contentLangIDs[] = $content->getLang()->getId();
            if($content->getLang()->getId() == $lang->getDefaultLanguage()->getId())
                $defaultContent = $content;
        }

        $metaLangIDs = [];
        $defaultMeta = null;
        foreach($listing->getMetadata() as $metadata){
            $metaLangIDs[] = $metadata->getLang()->getId();
            if($metadata->getLang()->getId() == $lang->getDefaultLanguage()->getId())
                $defaultMeta = $metadata;
        }

        foreach($languages as $language){
            if(!in_array($language->getId(), $contentLangIDs)){
                $newContent = new ListingContent($listing, $language);
                if($defaultContent){
                    $newContent->setAlias($defaultContent->getAlias());
                    $newContent->setLink($defaultContent->getLink());
                    $newContent->setTitle($defaultContent->getTitle());
                    $newContent->setText($defaultContent->getText());
                }
            }
            if(!in_array($language->getId(), $metaLangIDs)){
                $newMeta = new Metadata($listing, $language);
                if($defaultMeta){
                    $newMeta->setMetaTitle($defaultMeta->getMetaTitle());
                    $newMeta->setMetaDescription($defaultMeta->getMetaDescription());
                    $newMeta->setMetaKeywords($defaultMeta->getMetaKeywords());
                }
            }
        }
    }

    protected function removeListingImage(Entity\ListingImage $listingImage, $listingsDir, $listingId)
    {
        $this->getServiceLocator()->get('entity-manager')->remove($listingImage);
        unlink($listingsDir.$listingId.'/'.$listingImage->getImageName());
        $fileSystem = $this->getServiceLocator()->get('stdlib-file-system');
        if($fileSystem->isDirEmpty($listingsDir.$listingId)){
            rmdir($listingsDir.$listingId);
        }
    }

    protected function dependencyProvider(&$entityManager, &$listingEntity, &$categoryTree, &$listingRepository)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $listingEntity = $this->getServiceLocator()->get('listing-entity');
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $listingRepository = $entityManager->getRepository(get_class($listingEntity));
    }

    public function deleteAction()
    {
        $parentFilter = $this->params()->fromPost('id');
        $page = $this->params()->fromPost('page');
        $post = $this->params()->fromPost('ids', null);
        if(!$post){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('You must choose at least one item to delete'));
            return $this->redir()->toRoute('admin/listing');
        }
        $listingIds = explode(',', $post);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $listingEntity = $this->getServiceLocator()->get('listing-entity');

        $publicDir = $this->getServiceLocator()->get('config')['public-path'];
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-path'];

        foreach($listingIds as $listingId){//v_todo - create an ORM event listener, or a tool "on demand", to delete images of listings removed on category deletion
            $listing = $entityManager->find(get_class($listingEntity), $listingId);
            $entityManager->remove($listing);
            if($listing->getListingImage())
                $this->removeListingImage($listing->getListingImage(), $publicDir.$imgDir, $listing->getId());
        }
        $entityManager->flush();
        $this->flashMessenger()->addSuccessMessage($this->translator->translate('The pages have been deleted successfully'));
        return $this->redir()->toRoute('admin/listing', [
            'id' => $parentFilter,
            'page' => $page,
        ]);

    }
}
