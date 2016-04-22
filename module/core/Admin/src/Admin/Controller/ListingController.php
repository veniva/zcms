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
use Application\Model\Entity;
use Application\Stdlib\Strings;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Form\Element\Select;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Form\Element;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class ListingController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;
    
    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    /**
     * Initially loaded to display the list page's template
     * @return ViewModel
     */
    public function listAction()
    {
        $categoryTree = $this->getServiceLocator()->get('category-tree');
        $selectCategoryElement = new Select('filter_category');
        $selectCategoryElement->setAttribute('id', 'filter_category');
        $selectCategoryElement->setEmptyOption($this->translator->translate('All categories'));
        $selectCategoryElement->setValueOptions($categoryTree->getSelectOptions());

        return new ViewModel([
            'selectCategory' => $selectCategoryElement,
            'locale' => $this->translator->getLocale()
        ]);
    }

    /**
     * Called asynchronously
     * @return JsonModel
     */
    public function getList()
    {
        $parentCategory = $this->params()->fromQuery('filter', '0');
        $page = $this->params()->fromQuery('page', 1);
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);

        $listingsPaginated = $listingRepository->getListingsPaginated($parentCategory);
        $listingsPaginated->setCurrentPageNumber($page);

        $defaultLanguageID = $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId();
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($listingsPaginated, 'Sliding', 'paginator/sliding_ajax', ['id' => $parentCategory]);

        $i = 0;
        $listingsData = [];
        foreach($listingsPaginated as $listing){
            $listingsData[$i]['id'] = $listing->getId();
            $listingsData[$i]['sort'] = $listing->getSort();
            $listingsData[$i]['link'] = $listing->getSingleListingContent($defaultLanguageID)->getLink();
            $n = 0;
            $categories = [];
            foreach($listing->getCategories() as $category){
                $categories[$n]['id'] = $category->getId();
                $categories[$n]['title'] = $category->getSingleCategoryContent($defaultLanguageID)->getTitle();
                $n++;
            }
            $listingsData[$i]['categories'] = $categories;
            $i++;
        }

        return new JsonModel([
            'title' => $this->getTranslator()->translate('Pages'),
            'lists' => $listingsData,
            'paginator' => $paginator,
            'parentCategory' => $parentCategory,
            'defaultLanguageID' => $this->getServiceLocator()->get('language')->getDefaultLanguage()->getId(),
        ]);
    }

    public function get($id)
    {
        return $this->addEditListing($id);
    }

    public function addJsonAction()
    {
        return $this->addEditListing();
    }

    protected function addEditListing($id = null)
    {
        $action = $id ? 'edit' : 'add';
        $parentFilter = $this->params()->fromQuery('filter', 0);
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);
        if($action == 'add'){
            //check if there is at least one category available
            $categoryEntity = new Entity\Category();
            $categoryNumber = $entityManager->getRepository(get_class($categoryEntity))->countAll();
            if(!$categoryNumber)
                return $this->redirToList('You must create at least one category in order to add pages', 'error');
        }

        $languagesService = $this->getServiceLocator()->get('language');
        $languages = $languagesService->getActiveLanguages();

        if($action == 'edit'){
            $listing = $listingRepository->findOneBy(['id' => $id]);
            if(!$listing) return $this->redirWrongParameter();

        }else{
            $listing = $this->getServiceLocator()->get('listing-entity');
        }

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($listing);

        $listingContent = $action == 'edit' ? $listing->getContent() : null;
        $form = new ListingForm($this->getServiceLocator()->get('entity-manager'), $listingContent);
        $form->bind($listing);
        if($action == 'edit'){
            if(isset($listing->getCategories()[0]))
                $form->get('category')->setValueOptions($categoryTree->getSelectOptions())->setValue($listing->getCategories()[0]->getId());
        }else{
            $form->get('category')->setValueOptions($categoryTree->getSelectOptions())->setValue($parentFilter);
        }

        return $this->renderData($form, $listing, $action, $languages);
    }

    protected function renderData($form, $listing, $action, $languages, $message = null)
    {
        //add form-control CSS class to some form elements
        foreach($form->getFieldsets() as $fieldset){
            foreach($fieldset->getFieldsets() as $subFieldset){
                foreach($subFieldset->getElements() as $element){
                    $inputCSSClass = !empty($element->getAttribute('class')) ? $element->getAttribute('class').' ' : '';
                    $element->setAttribute('class', $inputCSSClass.'form-control');
                }
            }
        }

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'id' => $listing->getId(),
            'form' => $form,
            'listing' => $listing,
            'action' => $action,
            'image' => $listing->getListingImage() ? $listing->getId().'/'.$listing->getListingImage()->getImageName() : null,
            'locale' => $this->translator->getLocale(),
            'activeLanguages' => $languages,
        ]);
        $viewModel->setTemplate('admin/listing/edit');
        $jsonModel = [
            'title' => $this->translator->translate(ucfirst($action).' a page'),
            'form' => $renderer->render($viewModel),
        ];
        if($message)
            $jsonModel['message'] = $message;

        return new JsonModel($jsonModel);
    }

    public function update($id, $data)
    {
        return $this->handleCreateUpdate($data, $id);
    }

    public function create($data)
    {
        return $this->handleCreateUpdate($data);
    }

    public function handleCreateUpdate($data, $id = null){
        $action = !$id ? 'add' : 'edit';
        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);
        if(!$id){
            //check if there is at least one category available
            $categoryEntity = new Entity\Category();
            $categoryNumber = $entityManager->getRepository(get_class($categoryEntity))->countAll();
            if(!$categoryNumber)
                return $this->redirToList('You must create at least one category in order to add pages', 'error');

        }

        if($action == 'edit'){
            $listing = $listingRepository->findOneBy(['id' => $id]);
            if(!$listing) return $this->redirWrongParameter();

        }else{
            $listing = new Entity\Listing();
        }

        if(!empty($data['content'])){
            foreach($data['content'] as &$content){
                if(empty($content['alias'])){
                    $content['alias'] = Strings::alias($content['title']);
                }else{
                    $content['alias'] = Strings::alias($content['alias']);
                }
            }
        }

        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];

        $languagesService = $this->getServiceLocator()->get('language');
        $languages = $languagesService->getActiveLanguages();

        //add empty language content to the collection, so that input fields are created
        $this->addEmptyContent($listing);

        $listingContent = $action == 'edit' ? $listing->getContent() : null;
        $form = new ListingForm($this->getServiceLocator()->get('entity-manager'), $listingContent);
        $form->bind($listing);
        $form->get('category')->setValueOptions($categoryTree->getSelectOptions());

        $returnError = function($message) use($form, $listing, $action, $languages){
            $message = ['type' => 'error', 'text' => $message, 'no_redir' => 1];
            return $this->renderData($form, $listing, $action, $languages, $message);
        };

        $hasImage = (!empty($data['listing_image']['base64']) && !empty($data['listing_image']['name']));
        //validate image
        if($hasImage){
            $messages = [];
            $result = $form->validateBase64Image($data['listing_image']['name'], $data['listing_image']['base64'], $messages);
            if(!$result)
                return $returnError($this->translator->translate(implode('<br />', $messages)));//v_todo - translate
        }

        $form->setData($data);
        if($form->isValid()){
            $category = $entityManager->find(get_class($this->getServiceLocator()->get('category-entity')),
                ['id' => $form->getInputFilter()->getValue('category')]);
            $listing->setOnlyCategory($category);

            $entityManager->persist($listing);

            //is the image scheduled for removal
            if($action == 'edit'){
                if(!empty($data['image_remove']) && $listing->getListingImage()){
                    $this->removeListingImage($listing->getListingImage(), $imgDir, $listing->getId());
                }
            }

            //upload new image
            $upload = false;
            if($hasImage){
                if($action == 'edit'){
                    if(empty($data['image_remove']) && $listing->getListingImage()){
                        $this->removeListingImage($listing->getListingImage(), $imgDir, $listing->getId());
                    }
                }

                $listingImage = new ListingImage($listing);
                $listingImage->setImageName($data['listing_image']['name']);
                $upload = true;
            }

            $entityManager->flush();
            if($upload){
                $uploadDir = $imgDir.$listing->getId();
                if(!file_exists($uploadDir) && !is_dir($uploadDir)){
                    mkdir($uploadDir);
                }
                \file_put_contents($uploadDir.'/'.$data['listing_image']['name'], base64_decode($data['listing_image']['base64']));
            }

            if($this->getRequest()->isPost()){
                $this->getResponse()->setStatusCode(201);
            }

            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate(sprintf($this->translator->translate('The page has been %s successfully'),
                    $this->translator->translate($action.'ed')))],
            ]);

        }
        return $returnError($this->translator->translate('Please check the form for errors'));
    }

    protected function addEmptyContent(Entity\Listing $listing)
    {
        $languagesService = $this->getServiceLocator()->get('language');

        $contentLangIDs = [];
        foreach($listing->getContent() as $content){
            $contentLangIDs[] = $content->getLang()->getId();
        }

        foreach($languagesService->getActiveLanguages() as $language){
            if(!in_array($language->getId(), $contentLangIDs)){
                new ListingContent($listing, $language);
            }
        }
    }

    protected function removeListingImage(Entity\ListingImage $listingImage, $listingsDir, $listingId)
    {
        $fileName = $listingsDir.$listingId.'/'.$listingImage->getImageName();
        if(file_exists($fileName))
            unlink($fileName);
        $this->getServiceLocator()->get('entity-manager')->remove($listingImage);
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

    public function deleteAjaxAction()
    {
        $post = $this->params()->fromPost('ids', null);
        if(!$post){
            return $this->redirToList('You must choose at least one item to delete', 'error');
        }
        $listingIds = explode(',', $post);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $listingEntity = $this->getServiceLocator()->get('listing-entity');

        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-core-dir'];

        foreach($listingIds as $listingId){//v_todo - create an ORM event listener, or a tool "on demand", to delete images of listings removed on category deletion
            $listing = $entityManager->find(get_class($listingEntity), $listingId);
            if(!$listing){
                return $this->redirToList('Invalid listing ID passed', 'error');
            }

            if($listing->getListingImage()){
                $fileSystem = new Filesystem();
                $fileSystem->remove($imgDir.$listing->getId());
            }

            $entityManager->remove($listing);
        }
        $entityManager->flush();
        return $this->redirToList('The pages have been deleted successfully');

    }

    protected function redirWrongParameter()
    {
        return $this->redirToList('There was missing/wrong parameter in the request', 'error');
    }

    protected function redirToList($message = null, $messageType = 'success')
    {
        if(!in_array($messageType, ['success', 'error', 'info']))
            throw new \InvalidArgumentException('Un-existing message type');

        return new JsonModel([
            'message' => ['type' => $messageType, 'text' => $this->translator->translate($message)],
        ]);
    }
}
