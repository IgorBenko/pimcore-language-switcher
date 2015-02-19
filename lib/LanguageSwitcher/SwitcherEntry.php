<?php
namespace LanguageSwitcher;

use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;

class SwitcherEntry {

    /**
     * @var bool
     */
    private $isActive;

     /**
     * @var Page
     */
    private $page;


    /**
     * @param Page $page
     * @param bool $isActive
     */
    public function __construct($page, $isActive=false)
    {
        $this->setPage($page);
        $this->setActive($isActive);
    }


    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }


    /**
     * @param boolean $isActive
     */
    public function setActive($isActive)
    {
        $this->isActive = $isActive;
    }


    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->getPage()->getProperty("navigation_name");
    }


    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getPage()->getFullPath();
    }


    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }


    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }


    /**
     * @return Document
     */
    public function getBranchRoot()
    {
        return $this->getPage()->getProperty(Switcher::BRANCH_ROOT_PROPERTY_KEY);
    }


    /**
     * @return string
     */
    public function getBranchLabel()
    {
        return $this->getBranchRoot()->getProperty("navigation_name");
    }


    /**
     * @return string
     */
    public function getBranchPath()
    {
        return $this->getBranchRoot()->getFullPath();
    }
}
