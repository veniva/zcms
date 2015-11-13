<?php

namespace Admin\Controller;


use Application\Model\Entity\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;
use Zend\View\Model\ViewModel;

class UserController extends AbstractActionController
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
        $pageNumber = $this->params()->fromRoute('page');
        $entityManager = $this->getServiceLocator()->get('entity-manager');
        $userRepoRepo = $entityManager->getRepository(get_class(new User()));

        $usersPaginated = $userRepoRepo->getUsersPaginated();
        $usersPaginated->setCurrentPageNumber($pageNumber);

        return [
            'page' => $pageNumber,
            'users' => $usersPaginated,
        ];
    }
}