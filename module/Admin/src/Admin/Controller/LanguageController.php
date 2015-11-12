<?php

namespace Admin\Controller;


use Admin\Form\Language as LanguageForm;
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
            $form->setData($post);
            if($form->isValid($post['isoCode'], $oldIso)){
                //if this is the new default language, change the old default to status active
                if($oldStatus != $post['status'] && $post['status'] == 2){
                    $oldDefaultLanguage = $entityManager->getRepository(get_class($language))->findOneByStatus(2);
                    if($oldDefaultLanguage instanceof Lang){
                        $oldDefaultLanguage->setStatus(1);
                        $entityManager->persist($oldDefaultLanguage);
                    }
                }

                $entityManager->persist($language);
                $entityManager->flush();

                //upload new image
                if(isset($post['country_img']) && !$post['country_img']['error']){
                    $publicDir = $this->getServiceLocator()->get('config')['other']['public-path'];
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
        if($lang instanceof Lang){
            $entityManager->remove($lang);
            $entityManager->flush();

            //remove flag image if existing
            $publicDir = $this->getServiceLocator()->get('config')['other']['public-path'];
            $flagsDir = $publicDir.$this->flagsDir;
            $imgName = $flagsDir.$lang->getIsoCode().'.png';
            if(file_exists($imgName)) unlink($imgName);
        }
        return $this->redir()->toRoute('admin/default', ['controller' => 'language', 'page' => $page]);

    }
}