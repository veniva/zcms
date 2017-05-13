<?php

namespace Logic\Core\Admin\Page;

use Veniva\Lbs\Interfaces\StatusCodes;
use Veniva\Lbs\Interfaces\StatusMessages;
use Logic\Core\Model\Entity\Listing;
use Symfony\Component\Filesystem\Filesystem;

class PageDelete extends PageBase
{
    const ERR_CHOOSE_ITEM = 'pd.err-choose-items';
    const ERR_INVALID_ID = 'pd.err-invalid-id';

    public function delete(string $imgDir, string $idsToDelete = null)
    {
        if(!$idsToDelete) {
            return $this->result(self::ERR_CHOOSE_ITEM, 'You must choose at least one item to delete');
        }

        $pageIds = explode(',', $idsToDelete);

        //check for non numeric value in the array
        $pageIds = array_map(function($n) {
            if(!is_numeric($n)) return 'error';
            return $n;
        }, $pageIds);
        
        if(!is_array($pageIds) || in_array('error', $pageIds)) {
            return $this->result(StatusCodes::ERR_INVALID_PARAM, StatusMessages::ERR_INVALID_PARAM_MSG);
        }

        foreach($pageIds as $pageId) {
            //v_todo - create an ORM event listener, or a tool "on demand", to delete images of listings removed on category deletion
            $page = $this->em->find(Listing::class, $pageId);
            
            if(!$page) {
                return $this->result(self::ERR_INVALID_ID, 'Invalid page ID passed');
            }

            if($page->getListingImage()){
                $fileSystem = new Filesystem();
                $fileSystem->remove($imgDir.$page->getId());
            }

            $this->em->remove($page);
            //v_todo - delete cache file in data/cache if cache enabled in module Application/config/module.config.php
        }

        $this->em->flush();

        return $this->result(StatusCodes::SUCCESS, 'The pages have been deleted successfully');
    }
}