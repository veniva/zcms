<?php

namespace Admin\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\i18n\Translator\Translator;

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
        return [
            'title' => 'Pages'
        ];
    }
}