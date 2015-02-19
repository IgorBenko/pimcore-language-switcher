<?
namespace LanguageSwitcher;

use Pimcore\Config;
use Pimcore\Model\Document;
use Pimcore\Tool;

class Switcher {

    /**
     * Key of the property for identifying branch roots
     */
    const BRANCH_ROOT_PROPERTY_KEY = "languageSwitcherBranchRoot";

    /**
     * Name of the Website Settings key for setting the parent container of branches
     */
    const PARENT_NODE_WSETTING_NAME = "LanguageSwitcherParentNode";

    /**
     * @var Document Current active document
     */
    private $currentDocument;

    /**
     * @var Document Branch root of current active document
     */
    private $currentBranchRoot;

    /**
     * @var Document Parent node of branches
     */
    private $rootNode;

    /**
     * @var int[] Indexes of nodes from the root of the branch to the current document
     */
    private $currentDocIndexes;

    /**
     * @var Document\Page[] All branch roots
     */
    private $branchRoots;


    /**
     * @param null|Document $currentDocument
     */
    public function __construct($currentDocument=null)
    {
        if ($currentDocument) {
            $this->setCurrentDocument($currentDocument);
        }

        $config = Config::getWebsiteConfig();
        $this->rootNode = $config->get(self::PARENT_NODE_WSETTING_NAME);
        if (!$this->rootNode instanceof Document) {
            $this->rootNode = Document::getById(1);
        }

        $branchRoots = new Document\Listing();
        $subqueryIncludedDocs = "SELECT cid FROM properties WHERE name='" . self::BRANCH_ROOT_PROPERTY_KEY . "'";

        $branchRoots->setCondition("parentId=? AND id IN ($subqueryIncludedDocs)", [$this->rootNode->getId()] );
        $branchRoots->load();

        foreach ($branchRoots as $doc) {
            $this->branchRoots[] = $doc;
        }

    }


    /**
     * @return SwitcherEntry[]
     */
    public function getEntries() {

        $entries = [];

        foreach ($this->branchRoots as $branchRoot) {
            if ($branchRoot instanceof Document\Page) {

                /* @var \Pimcore\Model\Document $topDoc */
                $isActive = $branchRoot == $this->currentBranchRoot;

                if ($isActive) {
                    $branchSubdoc = $this->getCurrentDocument(true);
                } else {
                    $branchSubdoc = $this->getSameLevelDocInDifferentBranch($branchRoot);

                    if (!$branchSubdoc) {
                        $branchSubdoc = $branchRoot;
                    }
                }

                $entries[] = new SwitcherEntry($branchSubdoc, $isActive);
            }
        }

        return $entries;
    }


    /**
     * @param $branchRoot
     * @return mixed
     */
    public function getSameLevelDocInDifferentBranch($branchRoot)
    {
        if ($this->getCurrentDocument(true)->getId() === 1) {
            return $branchRoot;
        }

        if (!$this->currentDocIndexes) {
            $parent = $this->getCurrentDocument(true);

            $this->currentDocIndexes = [];

            $i = 0;
            while($parent->getParent()->getId() !== $this->rootNode->getId() && $i<30){ // Safety counter added for any edge cases

                $this->currentDocIndexes[] = $this->getRealDocIndex($parent);

                $parent = $parent->getParent();
                $i++;
            }
            $this->currentDocIndexes = array_reverse($this->currentDocIndexes, false);
        }

        /* @var Document $currentLevel  */
        $currentLevel = $branchRoot;
        for ($i=0; $i<count($this->currentDocIndexes); $i++) {
            $children = $currentLevel->getChilds();
            if (!array_key_exists($i, $this->currentDocIndexes)) {
                return $currentLevel;
            }
            $currentLevel = $children[$this->currentDocIndexes[$i]];
        }

        return $currentLevel;

    }


    /**
     * Set current document
     *
     * @param Document $doc
     */
    public function setCurrentDocument($doc)
    {
        $this->currentDocument = $doc;
        $this->currentBranchRoot = $doc->getProperty(self::BRANCH_ROOT_PROPERTY_KEY);
        $this->currentDocIndexes = [];
    }


    /**
     * @param bool $required
     * @return Document
     * @throws \Zend_Exception
     */
    public function getCurrentDocument($required=false)
    {
        if (!$this->currentDocument && $required) {
            throw new \Zend_Exception("Current document is not set");
        }

        return $this->currentDocument;
    }


    /**
     * Indexes of documents are relative, so they aren't always 0-based and consecutive
     *
     * @param Document $doc
     * @return int
     */
    private function getRealDocIndex($doc)
    {
        /* @var Document[] $siblings  */
        $siblings = $doc->getParent()->getChilds();

        $i = 0;
        foreach ($siblings as $sibling) {
            if ($sibling->getId() === $doc->getId()) {
                return $i;
            }
            $i++;
        }
    }
}