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
use Application\Model\Entity\CategoryContent;
use Application\Model\Entity\Lang;
use Application\Model\Entity\ListingContent;
use Application\Model\Entity\Metadata;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\View\Model\ViewModel;

class LanguageController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    protected $flagsDir = '/img/flags/';

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function indexAction()
    {
        $pageNumber = $this->params()->fromRoute('page');
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $languageRepo = $entityManager->getRepository(get_class(new Lang()));

        $languagesPaginated = $languageRepo->getLanguagesPaginated();
        $languagesPaginated->setCurrentPageNumber($pageNumber);

        return [
            'page' => $pageNumber,
            'languages' => $languagesPaginated,
        ];
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $page = $this->params()->fromRoute('page');
        if(empty($id))
            return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);

        return $this->addEditLanguage($id, $page);
    }

    public function addAction()
    {
        $page = $this->params()->fromRoute('page');

        $return = $this->addEditLanguage(null, $page);
        if($return instanceof ViewModel) {
            $return->setTemplate('admin/language/edit');
        }
        return $return;
    }

    protected function addEditLanguage($id, $page)
    {
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        if($id){
            $languageEntity = new Lang();
            $language = $entityManager->find(get_class($languageEntity), $id);
            if(!$language)
                return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);
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

        if($this->getRequest()->isPost()){
            $post = $this->getRequest()->getPost()->toArray();
            if($language->isDefault()) unset($post['status']);//ensures no status can be changed if lang is default

            $form->setData($post);
            if($form->isValid($language->isDefault($oldStatus))){
                //if this is the new default language, change the old default to status active, and populate the missing content in the new default lang
                if(isset($post['status']) && $language->isDefault($post['status']) && $oldDefaultLanguage){

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

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The language has been edited successfully'));
                return $this->redir()->toRoute('admin/default', [
                    'controller' => 'language',
                    'page' => $page
                ]);
            }
        }

        return new ViewModel([
            'action' => $id ? 'edit' : 'add',
            'page' => $page,
            'form' => $form,
            'lang' => !empty($language->getIsoCode()) ? $language : null,
            'flagCode' => $this->getRequest()->isPost() ? $this->params()->fromPost('isoCode') :
                $language->getIsoCode() ?: null
        ]);
    }

    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $page = $this->params()->fromRoute('page');
        if(empty($id))
            return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);

        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $lang = $entityManager->find(get_class(new Lang()), $id);
        if($lang->isDefault()){
            $this->flashMessenger()->addErrorMessage($this->translator->translate('The default language cannot be deleted'));
            return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);
        }
        if($lang instanceof Lang){
            $entityManager->remove($lang);
            $entityManager->flush();
        }
        return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);

    }
}