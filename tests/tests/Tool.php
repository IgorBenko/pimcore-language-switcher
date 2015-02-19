<?php
namespace LanguageSwitcherTest;

use LanguageSwitcher\Switcher;
use Pimcore\Config;
use Pimcore\File;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Folder;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Version;
use Symfony\Component\Yaml\Yaml;

class Tool
{

    /**
     * @var Page[]
     */
    public $documents;

    /**
     * @var int
     */
    private $numberOfPagesInYaml;

    public function __construct()
    {
        Version::disable();
    }

    public function createDocumentTree($yamlPath)
    {
        $docs = Yaml::parse($yamlPath);
        $this->numberOfPagesInYaml = 0;
        $this->createDocumentChildren($docs);
    }

    private function createDocumentChildren($node, $parentNodeId=1, $depth=0)
    {
        if ($depth > 0) {
            $page = new Page();
            $page->setParentId($parentNodeId);
            $page->setController(Config::getSystemConfig()->documents->default_controller);
            $page->setAction(Config::getSystemConfig()->documents->default_action);
            $page->setPublished(true);
            $page->setKey(File::getValidFilename($node["key"]));
            $page->setTitle($node["key"]);
            $page->setProperty("navigation_name", "text", $node["key"], false);

            try {
                $page->save();
            } catch (\Exception $e) {
                $path = Document::getById($parentNodeId)->getFullPath() . "/" . File::getValidFilename($node["key"]);
                $page = Document::getByPath($path);
            }

            if ($depth == 1 && $node["exclude"] !== true) {
                $page->setProperty(Switcher::BRANCH_ROOT_PROPERTY_KEY, "document", $page->getId(), false, true);
                $page->save();
            }

            $parentNodeId = $page->getId();
            $this->documents[] = $page;
            $this->numberOfPagesInYaml++;
        }

        if (array_key_exists("children", $node)) {
            foreach ($node["children"] as $child) {
                $this->createDocumentChildren($child, $parentNodeId, $depth+1);
            }
        }
    }

    /**
     * @return int
     */
    public function getNumberOfPagesInYaml()
    {
        return $this->numberOfPagesInYaml;
    }

    /**
     * Utility method to print YAML tree
     *
     * @param array$node
     * @param int $depth
     */
    private function printDocumentChildren($node, $depth=0)
    {
        if ($depth > 0) {
            if ($depth > 1) {
                echo str_repeat(" ", ($depth-1)*3) . "- ";
            }
            echo $node["key"] . PHP_EOL;
        } else {
            echo PHP_EOL . PHP_EOL . "Menu structure:" . PHP_EOL;
        }

        if (array_key_exists("children", $node)) {
            foreach ($node["children"] as $child) {
                $this->printDocumentChildren($child, $depth+1);
            }
        }
    }

    public function removeDocumentTree()
    {
        foreach ($this->documents as $d) {
            $d->delete();
        }
    }
}