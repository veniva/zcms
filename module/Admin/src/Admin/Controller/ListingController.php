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
        if($action == 'edit'){
            $listingId = $this->params()->fromRoute('id');
            if(!$listingId){
                return $this->redir()->toRoute('admin/listing', [
                    'id' => $parentFilter,
                    'page' => $page,
                ]);
            }
        }

        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);
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

        $publicDir = __DIR__.'/../../../../../public';
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
                if(!empty($post['image_remove']) && $listing->getListingImage()){
                    $this->removeListingImage($listing->getListingImage(), $publicDir.$imgDir);
                }
                //upload new image
                if(isset($post['listingImage']) && !$post['listingImage']['error']){
                    if(empty($post['image_remove']) && $listing->getListingImage()){
                        $this->removeListingImage($listing->getListingImage(), $publicDir.$imgDir);
                    }
                    $listingImage = new ListingImage($listing);
                    $listingImage->setImageName($post['listingImage']['name']);
                    \move_uploaded_file($post['listingImage']['tmp_name'], $publicDir.$imgDir.$post['listingImage']['name']);
                }

                $entityManager->flush();
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
            'image' => $listing->getListingImage() ? $imgDir.$listing->getListingImage()->getImageName() : null,
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

    protected function removeListingImage(Entity\ListingImage $listingImage, $dir)
    {
        $this->getServiceLocator()->get('entity-manager')->remove($listingImage);
        unlink($dir.$listingImage->getImageName());
    }

    protected function dependencyProvider(&$entityManager, &$listingEntity, &$categoryTree, &$listingRepository)
    {
        /* \Doctrine\Orm\EntityManager */
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        /* \Application\Model\Entity\Listing */
        $listingEntity = $this->getServiceLocator()->get('listing-entity');
        /* \Admin\CategoryTree\CategoryTree $categoryTree */
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        /* @var \Application\Model\ListingRepository $listingRepository */
        $listingRepository = $entityManager->getRepository(get_class($listingEntity));
    }
}
