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
        $categId = $this->params()->fromRoute('id');
        $page = $this->params()->fromRoute('page');
        $parentFilter = $this->params()->fromRoute('filter');
        if(!$categId){
            return $this->redir()->toRoute('admin/listing', [
                'id' => $parentFilter,
                'page' => $page,
            ]);
        }

        $this->dependencyProvider($entityManager, $listingEntity, $categoryTree, $listingRepository);

        $listing = $listingRepository->findOneBy(['id' => $categId]);
        if(!$listing){
            return $this->redir()->toRoute('admin/listing', [
                'id' => $parentFilter,
                'page' => $page,
            ]);
        }

        $listingContentDefaultLanguage = $listing->getContent();
        $publicDir = __DIR__.'/../../../../../public';
        $imgDir = $this->getServiceLocator()->get('config')['listing']['img-path'];

        $languages = Misc::getActiveLangs();
        $listingForm = new ListingForm($listingContentDefaultLanguage, $languages,
            $this->getServiceLocator()->get('translator'), $this->getServiceLocator()->get('validator-messages'));

        $form = $listingForm->getForm();

        //add form-control CSS class to all the form elements except for "sort"
        foreach($form->getElements() as $element){
            if($element->getName() != 'sort'  && !$element instanceof Element\File &&
                !$element instanceof Element\Checkbox && $element->getName() != 'category'){
                $inputCSSClass = !empty($element->getAttribute('class')) ? $element->getAttribute('class').' ' : '';
                $element->setAttribute('class', $element->getAttribute('class').$inputCSSClass.'form-control');
            }
        }

        $form->get('sort')->setValue($listing->getSort());
        $form->get('category')->setValueOptions($categoryTree->getCategoriesAsOptions())->setValue($listing->getCategories()[0]->getId());

        $form->bind($listingContentDefaultLanguage);

        //set metadata content on the default language
        $listingMetadata = $listing->getMetadata();
        if($listingMetadata instanceof Metadata){
            foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
                $form->get($input)->setValue($listingMetadata->{'get'.$input}());
            }
        }

        $listingContentEntities = [Misc::getDefaultLanguage()->getIsoCode() => $listingContentDefaultLanguage];
        $listingMetaEntities = [Misc::getDefaultLanguage()->getIsoCode() => $listingMetadata];
        foreach($languages as $language){
            if($language->getId() != Misc::getDefaultLanguage()->getId()){
                //set content fields
                $listingContentLanguage = $listing->getContent($language->getId());
                if(get_class($listingContentLanguage) == get_class($listingContentDefaultLanguage)){//if content on that language exists
                    $listingContentEntities[$language->getIsoCode()] = $listingContentLanguage;
                    foreach(['link', 'alias', 'title', 'text'] as $input){
                        $form->get($input.'_'.$language->getIsoCode())->setValue($listingContentLanguage->{'get'.$input}());

                        if($input != 'alias')//set all the language input fields but alias to be required
                            $form->getInputFilter()->get($input.'_'.$language->getIsoCode())->setRequired(true);
                    }
                }

                //set metadata fields
                $listingMetadataLanguage = $listing->getMetadata($language->getId());
                if($listingMetadataLanguage instanceof Metadata){
                    $listingMetaEntities[$language->getIsoCode()] = $listingMetadataLanguage;
                    foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
                        $form->get($input.'_'.$language->getIsoCode())->setValue($listingMetadataLanguage->{'get'.$input}());
                    }
                }
            }
        }

        $request = $this->getRequest();
        if($request->isPost()){
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);
            $post['alias'] = (!empty($post['alias'])) ? $post['alias'] : Misc::alias($post['title']);
            if($form->isValid()){
                $isValid = true;//may be changed to false to abort data saving
                //set data to entity classes, other than $listingContentDefaultLanguage which is bound to the $form
                $listing->setSort($form->getInputFilter()->getValue('sort'));
                $category = $entityManager->find(get_class($this->getServiceLocator()->get('category-entity')),
                    ['id' => $form->getInputFilter()->getValue('category')]);
                $listing->setOnlyCategory($category);
                foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
                    $listingMetadata->{'set'.$input}($form->getInputFilter()->getValue($input));
                }

                $entityManager->persist($listing);

                foreach($languages as $language){
                    if ($language->getId() != Misc::getDefaultLanguage()->getId()) {
                        $iso = $language->getIsoCode();

                        //set the listing content in a given language, only set if link, title and text are all filled in
                        if(!empty($post['link_'.$iso]) && !empty($post['title_'.$iso]) && !empty($post['text_'.$iso])){

                            if(isset($listingContentEntities[$language->getIsoCode()])){
                                $listingContentLanguage = $listingContentEntities[$language->getIsoCode()];
                            }else{
                                $listingContentLanguage = new ListingContent($listing, $language);
                            }

                            foreach(['link', 'title', 'text'] as $input){
                                $listingContentLanguage->{'set'.$input}($form->getInputFilter()->getValue($input.'_'.$iso));
                            }
                            $alias = !empty($post['alias_'.$iso]) ? $form->getInputFilter()->getValue('alias_'.$iso) :
                                Misc::alias($form->getInputFilter()->getValue('title_'.$iso));
                            $listingContentLanguage->setAlias($alias);

                        }else if(!empty($post['link_'.$iso]) || !empty($post['title_'.$iso]) || !empty($post['text_'.$iso])) {
                            $this->flashMessenger()->addErrorMessage(
                                sprintf($this->translator->translate('Content %s - you must fill in all the required fields or leave them all empty'), '('.$iso.')')
                            );
                            $isValid = false;
                            break;
                        }

                        //set the metadata in a given language
                        if(empty($post['metaTitle_'.$iso]) && empty($post['metaDescription_'.$iso]) && empty($post['metaKeywords_'.$iso])){
                            if(isset($listingMetaEntities[$language->getIsoCode()])){
                                $entityManager->remove($listingMetaEntities[$language->getIsoCode()]);//remove the metadata on that language if no fields are filled in
                            }
                        }else{
                            if(isset($listingMetaEntities[$language->getIsoCode()])){
                                $listingMetadataLanguage = $listingMetaEntities[$language->getIsoCode()];
                            }else{
                                $listingMetadataLanguage = new Metadata($listing, $language);
                            }

                            foreach(['metaTitle', 'metaDescription', 'metaKeywords'] as $input){
                                $listingMetadataLanguage->{'set'.$input}($form->getInputFilter()->getValue($input.'_'.$iso));
                            }
                        }
                    }
                }

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

                if($isValid){
                    $entityManager->flush();
                    return $this->redir()->toRoute('admin/listing', [
                        'id' => $parentFilter,
                        'page' => $page,
                    ]);
                }

            }else{
                $this->flashMessenger()->addErrorMessage($this->translator->translate("Please check the form for errors"));
            }
        }

        return [
            'page' => $page,
            'filter' => $parentFilter,
            'form' => $form,
            'listing' => $listing,
            'action' => 'Edit',
            'image' => $listing->getListingImage() ? $imgDir.$listing->getListingImage()->getImageName() : $listing->getListingImage(),
        ];
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
