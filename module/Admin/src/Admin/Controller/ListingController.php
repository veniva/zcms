<?php

namespace Admin\Controller;


use Admin\Form\Listing as ListingForm;
use Application\Model\Entity\ListingContent;
use Application\Model\Entity\ListingImage;
use Application\Model\Entity\Metadata;
use Application\Model\Entity;
use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\Form\Element;
use Zend\View\Model\ViewModel;

class ListingController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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

        $languages = Misc::getActiveLangs();

        if($action == 'edit'){
            $listing = $listingRepository->findOneBy(['id' => $listingId]);
            if(!$listing){
                return $this->redir()->toRoute('admin/listing', [
                    'id' => $parentFilter,
                    'page' => $page,
                ]);
            }
        }else{
            $listing = new Entity\Listing();
        }

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($listing, $languages);

        $publicDir = $this->getServiceLocator()->get('config')['other']['public-path'];
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-path'];

        $listingForm = new ListingForm($listing, $languages);
        $form = $listingForm->getForm();
        $form->bind($listing);
        if($action == 'edit'){
            $form->get('category')->setValueOptions($categoryTree->getCategoriesAsOptions())->setValue($listing->getCategories()[0]->getId());
        }else{
            $form->get('category')->setValueOptions($categoryTree->getCategoriesAsOptions())->setValue($parentFilter);
        }

        //add form-control CSS class to some form elements
        foreach($form->getFieldsets() as $fieldset){
            foreach($fieldset->getFieldsets() as $subFieldset){
                foreach($subFieldset->getElements() as $element){
                    $inputCSSClass = !empty($element->getAttribute('class')) ? $element->getAttribute('class').' ' : '';
                    $element->setAttribute('class', $element->getAttribute('class').$inputCSSClass.'form-control');
                }
            }
        }

        $request = $this->getRequest();
        if($request->isPost()){
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            foreach($post['content'] as &$content){
                if(empty($content['alias'])){
                    $content['alias'] = Misc::alias($content['title']);
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
                        mkdir($publicDir.$imgDir.$listing->getId());
                    }
                    \move_uploaded_file($post['listingImage']['tmp_name'], $uploadDir.'/'.$post['listingImage']['name']);
                }
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
        ]);
        return $viewModel;
    }

    protected function addEmptyContent(Entity\Listing $listing, \Doctrine\Common\Collections\Collection $languages)
    {
        $contentLangIDs = [];
        $defaultContent = null;
        foreach($listing->getContent() as $content){
            $contentLangIDs[] = $content->getLang()->getId();
            if($content->getLang()->getId() == Misc::getDefaultLanguage()->getId())
                $defaultContent = $content;
        }

        $metaLangIDs = [];
        $defaultMeta = null;
        foreach($listing->getMetadata() as $metadata){
            $metaLangIDs[] = $metadata->getLang()->getId();
            if($metadata->getLang()->getId() == Misc::getDefaultLanguage()->getId())
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

        $publicDir = $this->getServiceLocator()->get('config')['other']['public-path'];
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-path'];

        foreach($listingIds as $listingId){
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
