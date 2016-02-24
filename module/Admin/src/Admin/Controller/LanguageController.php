<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Admin\Form\Language as LanguageForm;
use Application\Model\Entity\Category;
use Application\Model\Entity\Lang;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class LanguageController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $flagsDir = '/img/flags/';

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $pageNumber = $this->params()->fromRoute('page');
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $languageRepo = $entityManager->getRepository(get_class(new Lang()));

        $languagesPaginated = $languageRepo->getLanguagesPaginated();
        $languagesPaginated->setCurrentPageNumber($pageNumber);

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $paginator = $renderer->paginationControl($languagesPaginated, 'Sliding', 'paginator/sliding_ajax');

        $i = 0;
        $languages = [];
        foreach($languagesPaginated as $language){
            $languages[$i]['id'] = $language->getId();
            $languages[$i]['isoCode'] = $language->getIsoCode();
            $languages[$i]['isDefault'] = $language->isDefault();
            $languages[$i]['name'] = $language->getName();
            $languages[$i]['statusName'] = $language->getStatusName();
            $i++;
        }

        return new JsonModel([
            'title' => $this->translator->translate('Languages'),
            'page' => $pageNumber,
            'languages' => $languages,
            'paginator' => $paginator
        ]);
    }

    public function editJsonAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $page = $this->params()->fromRoute('page');
        if(empty($id)){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
            ]);
        }

        return $this->addEditLanguage($page, $id);
    }

    public function addJsonAction()
    {
        $page = $this->params()->fromRoute('page');
        return $this->addEditLanguage($page);
    }

    /**
     * Displays the form
     * @param $id NULL - add ELSE edit
     * @param $page
     * @return JsonModel
     */
    protected function addEditLanguage($page, $id = null)
    {
        $action = $id ? 'edit' : 'add';
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        if($id){
            $languageEntity = new Lang();
            $language = $entityManager->find(get_class($languageEntity), $id);
            if(!$language){
                return new JsonModel([
                    'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
                ]);
            }
        }else{
            $language = new Lang();
        }

        $form = new LanguageForm($this->getServiceLocator());
        $form->bind($language);
        return $this->renderData($action, $language, $page, $form);

    }

    protected function renderData($action, $language, $page, $form)
    {
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'action' => $action,
            'id' => $language->getId(),
            'page' => $page,
            'form' => $form,
            'lang' => !empty($language->getIsoCode()) ? $language : null,
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') :
                $language->getIsoCode() ?: null
        ]);
        $viewModel->setTemplate('admin/language/edit');

        return new JsonModel([
            'title' => $this->translator->translate(ucfirst($action).' a language'),
            'form' => $renderer->render($viewModel),
            'page' => $page
        ]);
    }

    public function update($id)
    {
        $page = $this->params()->fromRoute('page');
        return $this->handleCreateUpdate($page, $id);
    }

    public function create()
    {
        $page = $this->params()->fromRoute('page');
        return $this->handleCreateUpdate($page);
    }

    public function handleCreateUpdate($page, $id = null)
    {
        $action = $id ? 'edit' : 'add';
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        if($id){
            $languageEntity = new Lang();
            $language = $entityManager->find(get_class($languageEntity), $id);
            if(!$language){
                return new JsonModel([
                    'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')],
                ]);
            }
        }else{
            $language = new Lang();
        }

        $oldDefaultLanguage = $entityManager->getRepository(get_class($language))->findOneByStatus(Lang::STATUS_DEFAULT);
        $oldStatus = null;
        if($language->getId()){
            $oldStatus = $language->getStatus();
        }
        $form = new LanguageForm($this->getServiceLocator());
        $form->bind($language);

        $request = $this->getRequest();
        if($request->isPost()){
            $data = $this->getRequest()->getPost()->toArray();
        }else{//is PUT
            $data = [];
            parse_str($this->getRequest()->getContent(), $data);
        }


        if($language->isDefault()) unset($data['status']);//ensures no status can be changed if lang is default

        $form->setData($data);
        if($form->isValid($language->isDefault($oldStatus))){
            //if this is the new default language, change the old default to status active, and populate the missing content in the new default lang
            if(isset($data['status']) && $language->isDefault($data['status']) && $oldDefaultLanguage){

                //region fill missing content in categories
                $oldDefaultLanguageId = $oldDefaultLanguage->getId();
                $editedLanguageId = $language->getId();//note! this is null if new language
                $getMissingData = function($contentCollection)use($entityManager,$language,$oldDefaultLanguageId,$editedLanguageId)
                {
                    $defaultContent = null;
                    $editedContent = null;
                    foreach($contentCollection as $content){
                        $contentLangId = $content->getLang()->getId();
                        if($contentLangId == $oldDefaultLanguageId){
                            $defaultContent = $content;
                            continue;
                        }
                        if($editedLanguageId && $contentLangId == $editedLanguageId){//if($editedLanguageId) = do this only in case of action == edit
                            $editedContent = $content;
                            continue;
                        }
                        if($defaultContent && $editedContent) break;//job done
                    }
                    return [
                        'default_content' => $defaultContent,
                        'edited_content' => $editedContent
                    ];
                };
                //parse through all the categories
                $categories = $entityManager->getRepository(get_class(new Category()))->findByType(1);
                foreach($categories as $category){
                    //region copy category content
                    $categoryContents = $getMissingData($category->getContent());
                    if(!$categoryContents['edited_content'] && $categoryContents['default_content']){
                        $newContent = clone $categoryContents['default_content'];
                        $newContent->setLang($language);
                        $category->addCategoryContent($newContent);
                        $entityManager->persist($category);
                    }
                    //endregion

                    //region copy listings content
                    foreach($category->getListings() as $listing){
                        //deal with content
                        $listingContents = $getMissingData($listing->getContent());
                        if(!$listingContents['edited_content'] && $listingContents['default_content']){
                            $newListingContent = clone $listingContents['default_content'];
                            $newListingContent->setLang($language);
                            $listing->addContent($newListingContent);
                            $entityManager->persist($listing);
                        }
                    }
                    //endregion
                }
                //endregion

                if($oldDefaultLanguage instanceof Lang){
                    $oldDefaultLanguage->setStatus(Lang::STATUS_ACTIVE);
                    $entityManager->persist($oldDefaultLanguage);
                }

            }

            $entityManager->persist($language);
            $entityManager->flush();

            if($request->isPost()){
                $this->getResponse()->setStatusCode(201);
            }
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The language has been '.$action.'ed successfully')],
            ]);
        }

        return $this->renderData($action, $language, $page, $form);
    }

    public function delete()
    {
        $id = $this->params()->fromRoute('id', null);
        if(empty($id)){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('There was missing/wrong parameter in the request')]
            ]);
        }

        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $lang = $entityManager->find(get_class(new Lang()), $id);
        if($lang->isDefault()){
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $this->translator->translate('The default language cannot be deleted')]
            ]);
        }
        if($lang instanceof Lang){
            $entityManager->remove($lang);
            $entityManager->flush();
        }
        return new JsonModel([
            'message' => ['type' => 'success', 'text' => $this->translator->translate('The language has been deleted successfully')]
        ]);

    }
}