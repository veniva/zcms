<?php

namespace Admin\Controller;


use Admin\Form\Language as LanguageForm;
use Application\Model\Entity\Category;
use Application\Model\Entity\Lang;
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

        $form = new LanguageForm();
        $form->bind($language);
        $oldIso = null;
        $oldStatus = null;
        if($language->getId()){
            $oldIso = $language->getIsoCode();
            $oldStatus = $language->getStatus();
        }

        if($this->getRequest()->isPost()){
            $post = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if($language->isDefault()) unset($post['status']);//ensures no status can be changed if lang is default

            $form->setData($post);
            if($form->isValid($post['isoCode'], $oldIso, $language->isDefault())){
                //if this is the new default language, change the old default to status active, and populate the missing content in the new default lang
                if(isset($post['status']) && $oldStatus != $post['status'] && $language->isDefault($post['status'])){

                    //region fill missing content in categories
                    $oldDefaultLanguageId = $oldDefaultLanguage->getId();
                    $editedLanguageId = $language->getId();//note! this is null if new language
                    $getMissingData = function($contentCollection)use($entityManager,$language,$oldDefaultLanguageId,$editedLanguageId){
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

                    //v_todo - if the language is edited and made inactive, purge all it's content
                }

                $entityManager->persist($language);
                $entityManager->flush();

                //upload new image
                if(isset($post['country_img']) && !$post['country_img']['error']){
                    $publicDir = $this->getServiceLocator()->get('config')['public-path'];
                    $flagsDir = $publicDir.$this->flagsDir;
                    $imgName = $flagsDir.$post['isoCode'].'.png';
                    //remove old if existing
                    if(file_exists($imgName)) unlink($imgName);
                    if(file_exists($flagsDir.$oldIso.'.png')) unlink($flagsDir.$oldIso.'.png');

                    \move_uploaded_file($post['country_img']['tmp_name'], $imgName);
                }
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

            //remove flag image if existing
            $publicDir = $this->getServiceLocator()->get('config')['public-path'];
            $flagsDir = $publicDir.$this->flagsDir;
            $imgName = $flagsDir.$lang->getIsoCode().'.png';
            if(file_exists($imgName)) unlink($imgName);
        }
        return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);

    }
}