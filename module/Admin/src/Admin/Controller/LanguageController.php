<?php

namespace Admin\Controller;


use Application\Model\Entity\Lang;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;

class LanguageController extends AbstractActionController
{
    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function indexAction()
    {
        $pageNumber = $this->params()->fromRoute('id');
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $languageRepo = $entityManager->getRepository(get_class(new Lang()));

        $languagesPaginated = $languageRepo->getLanguagesPaginated();
        $languagesPaginated->setCurrentPageNumber($pageNumber);

        return [
            'pageNumber' => $pageNumber,
            'languages' => $languagesPaginated,
        ];
    }
}