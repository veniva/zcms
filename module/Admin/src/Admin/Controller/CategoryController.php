<?php

namespace Admin\Controller;


use Admin\Form\Category;
use Application\Model\Entity\CategoryContent;
use Application\Service\Invokable\Misc;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\Stdlib\ArrayUtils;

class CategoryController extends AbstractActionController
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
        $lang = Misc::getLangID();
        $parent = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $categoryEntity = $this->getServiceLocator()->get('category-entity');
        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));

        $categoriesPaginated = $categoryRepository->getPaginatedCategories($parent);
        $categoriesPaginated->setCurrentPageNumber($page);

        $category = $parent ? $categoryRepository->getCategory($parent, $lang) : null;
        $categoryAlias = $category ? $category['content']['alias'] : null;

        return [
            'title' => 'Categories',
            'categories' => $categoriesPaginated,
            'parent_id' => $parent,
            'category_alias' => $categoryAlias,
            'page' => $page,
        ];
    }

    public function editAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        $page = $this->params()->fromRoute('page', 1);
        if(empty($id))
            $this->redir()->toRoute('admin/category', ['action' => 'add', 'page' => $page]);

        $serviceLocator = $this->getServiceLocator();
        $entityManager = $serviceLocator->get('entity-manager');
        $categoryEntity = $serviceLocator->get('category-entity');

        $categoryRepository = $entityManager->getRepository(get_class($categoryEntity));
        $category = $categoryRepository->findOneById($id);
        $parentCategory = $categoryRepository->findOneById($category->getParentId());

        $categoryContentDefaultLanguageEntity = $category->getContent();

        //foreach active language add a content title field to the form
        $languages = Misc::getActiveLangs();
        $formClass = new Category($categoryContentDefaultLanguageEntity, $languages);
        $form = $formClass->getForm();

        $contentLanguageEntities = [];
        foreach($languages as $language){
            if($language->getId() != Misc::getDefaultLanguage()->getId()){

                $categoryContentLanguageEntity = $category->getContent($language->getId());

                if(get_class($categoryContentLanguageEntity) == get_class($categoryContentDefaultLanguageEntity)){//if content on that language exists
                    $contentLanguageEntities[$language->getIsoCode()] = $categoryContentLanguageEntity;
                    $form->get('title_'.$language->getIsoCode())->setValue($categoryContentLanguageEntity->getTitle());
                }

            }
        }
        $form->bind($categoryContentDefaultLanguageEntity);

        $request = $this->getRequest();
        if($request->isPost()){
            $post = ArrayUtils::iteratorToArray($request->getPost());
            $post['alias'] = Misc::alias($post['title']);
            $form->setData($post);
            if($form->isValid()){
                $entityManager->persist($categoryContentDefaultLanguageEntity);

                foreach($languages as $language) {
                    if ($language->getId() != Misc::getDefaultLanguageID()) {
                        $post['alias'] = Misc::alias($post['title_'.$language->getIsoCode()]);
                        $post['title'] = $post['title_'.$language->getIsoCode()];

                        if(isset($contentLanguageEntities[$language->getIsoCode()])){
                            $categoryContentLanguageEntity = $contentLanguageEntities[$language->getIsoCode()];
                            if(empty($post['title'])){
                                $entityManager->remove($categoryContentLanguageEntity);
                                continue;
                            }
                        }else{
                            $categoryContentLanguageEntity = new CategoryContent();//new entry
                            if(!empty($post['title'])){
                                $categoryContentLanguageEntity->setCategory($category);
                                $categoryContentLanguageEntity->setLangId($language->getId());
                            }
                        }

                        $form->bind($categoryContentLanguageEntity);
                        $form->setData($post);
                        if($form->isValid()){
                            $entityManager->persist($categoryContentLanguageEntity);
                        }
                    }
                }

                $entityManager->flush();

                $this->flashMessenger()->addSuccessMessage($this->translator->translate('The category has been edited successfully'));
                $this->redir()->toRoute('admin/category', [
                    'id' => isset($parentCategory) ? $parentCategory->getId() : null,
                    'page' => $page,
                ]);
            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate('There was an error in the new category name on the default language'));
                $this->redirect()->toUrl($_SERVER['REQUEST_URI']);//redirect to the same URL
            }
        }

        return [
            'form' => $form,
            'parentCategory' => $parentCategory,
            'page' => $page,
        ];
    }

    public function addAction()
    {

    }
}
