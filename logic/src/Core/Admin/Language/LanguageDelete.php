<?php

namespace Logic\Core\Admin\Language;

use Veniva\Lbs\Adapters\Interfaces\ITranslator;
use Veniva\Lbs\BaseLogic;
use Doctrine\ORM\EntityManager;
use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Lang;

class LanguageDelete extends BaseLogic
{
    const ERR_CANNOT_DELETE_DEFAULT = 'ld.cannot-delete-default';
    const ERR_CANNOT_DELETE_DEFAULT_MSG = 'The default language cannot be deleted';

    /** @var EntityManager */
    protected $em;

    public function __construct(ITranslator $translator, EntityManager $em)
    {
        parent::__construct($translator);

        $this->em = $em;
    }

    public function delete(int $id)
    {
        /** @var Lang $language */
        $language = $this->em->find(Lang::class, $id);
        if (!$language) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        if ($language->isDefault()) {
            return $this->result(self::ERR_CANNOT_DELETE_DEFAULT, self::ERR_CANNOT_DELETE_DEFAULT_MSG);
        }

        $this->em->remove($language);
        $this->em->flush();

        return $this->result(StatusCodes::SUCCESS, 'The language has been deleted successfully');
    }
}