<?php
/**
 * ZCMS - a light weight CMS
 *
 * @copyright Copyright (c) 2015 Ventsislav Ivanov
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU/GPL 3.0 licence
 */

namespace Admin\Controller;

use Logic\Core\Adapters\Zend\Translator;
use Logic\Core\Admin\Language\LanguageList;
use Logic\Core\Admin\Language\LanguageUpdate;
use Logic\Core\Form\Language as LanguageForm;
use Logic\Core\Interfaces\StatusCodes;
use Logic\Core\Model\Entity\Category;
use Logic\Core\Model\Entity\Lang;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class LanguageController extends AbstractRestfulController implements TranslatorAwareInterface
{
    use TranslatorAwareTrait, ServiceLocatorAwareTrait;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $serviceLocator)
    {
        $this->setServiceLocator($serviceLocator);
    }

    protected $flagsDir = '/img/flags/';

    public function listAction()
    {
        return new ViewModel();
    }

    public function getList()
    {
        $pageNumber = $this->params()->fromQuery('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');

        $logic = new LanguageList(new Translator($this->getTranslator()), $entityManager);
        $result = $logic->getList($pageNumber);

        $renderer = $this->getServiceLocator()->get('ViewRenderer');
        $paginator = $renderer->paginationControl($result->get('langs_paginated'), 'Sliding', 'paginator/sliding_ajax');

        return new JsonModel([
            'title' => $result->get('title'),
            'lists' => $result->get('lang_data'),
            'paginator' => $paginator
        ]);
    }

    public function get($id)
    {
        $em = $this->getServiceLocator()->get('entity-manager');
        $flagCodes = $this->getServiceLocator()->get('flag-codes');

        $logic = new LanguageUpdate(new Translator($this->getTranslator()), $em, $flagCodes);

        $result = $logic->showForm($id);
        if($result->status !== StatusCodes::SUCCESS) {
            return new JsonModel([
                'message' => ['type' => 'error', 'text' => $result->message],
            ]);
        }

        return $this->renderData('edit', $result->get('language'), $result->get('form'));
    }

    public function addJsonAction()
    {
        return $this->addEditLanguage();
    }

    /**
     * Displays the form
     * @param $id NULL - add ELSE edit
     * @return JsonModel
     */
    protected function addEditLanguage($id = null)
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

        $fc = $this->getServiceLocator()->get('flag-codes');
        $form = new LanguageForm($this->getServiceLocator()->get('entity-manager'), $fc->getFlagCodeOptions());
        $form->bind($language);
        return $this->renderData($action, $language, $form);

    }

    protected function renderData($action, $language, $form)
    {
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\RendererInterface');
        $viewModel = new ViewModel([
            'action' => $action,
            'id' => $language->getId(),
            'form' => $form,
            'lang' => !empty($language->getIsoCode()) ? $language : null,
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') :
                $language->getIsoCode() ?: null
        ]);
        $viewModel->setTemplate('admin/language/edit');

        return new JsonModel([
            'title' => $this->translator->translate(ucfirst($action).' a language'),
            'form' => $renderer->render($viewModel),
        ]);
    }

    public function update($id, $data)
    {
        return $this->handleCreateUpdate($data, $id);
    }

    public function create($data)
    {
        return $this->handleCreateUpdate($data);
    }

    public function handleCreateUpdate($data, $id = null)
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

            if($this->getRequest()->isPost()){
                $this->getResponse()->setStatusCode(201);
            }
            return new JsonModel([
                'message' => ['type' => 'success', 'text' => $this->translator->translate('The language has been '.$action.'ed successfully')],
            ]);
        }

        return $this->renderData($action, $language, $form);
    }

    public function delete($id)
    {
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