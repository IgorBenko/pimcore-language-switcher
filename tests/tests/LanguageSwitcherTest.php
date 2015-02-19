<?php
use Pimcore\File;
use Pimcore\Model\Document;

class LanguageSwitcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \LanguageSwitcherTest\Tool;
     */
    private $tool;

    protected function setUp() {
        require_once("Tool.php");
        $this->tool = new LanguageSwitcherTest\Tool();
        $this->tool->createDocumentTree('fixtures/documentStructure.yaml');
    }


    public function testDocumentsCreatedSuccessfully()
    {
        $pagesInYaml = $this->tool->getNumberOfPagesInYaml();
        $this->assertSame(count($this->tool->documents), $pagesInYaml);
    }


    public function testGetSameLevelDocInDifferentBranch()
    {
        $currentDoc = Document::getByPath("/en/en_entry_1");
        $languageSwitcher = new \LanguageSwitcher\Switcher($currentDoc);


        // Return current doc if searching inside the same branch
        $currentBranch = Document::getByPath("/en");
        $doc = $languageSwitcher->getSameLevelDocInDifferentBranch($currentBranch);
        $this->assertSame($currentDoc->getId(), $doc->getId());


        $otherBranch = Document::getByPath("/de");
        $doc = $languageSwitcher->getSameLevelDocInDifferentBranch($otherBranch);
        $otherDoc = Document::getByPath("/de/de_entry_1");
        $this->assertSame($otherDoc->getId(), $doc->getId());


        $languageSwitcher->setCurrentDocument(Document::getByPath("/it"));
        $otherBranch = Document::getByPath("/de");
        $doc = $languageSwitcher->getSameLevelDocInDifferentBranch($otherBranch);
        $this->assertSame($otherBranch->getKey(), $doc->getKey());
    }


    public function testBranchRootsAreReturnedWhenCurrentDocIsHome()
    {
        $languageswitcher = new \LanguageSwitcher\Switcher(Document::getById(1));
        $entries = $languageswitcher->getEntries();

        $expectedKeys = ["en", "de", "it"];
        foreach ($entries as $key=>$e) {
            $this->assertSame($expectedKeys[$key], $e->getPage()->getKey());
            $this->assertFalse($e->isActive());
        }
    }


    public function testRelatedDocsAreReturnedOnBranchRootLevel()
    {
        $currentDoc = Document::getByPath("/de");

        $languageswitcher = new \LanguageSwitcher\Switcher($currentDoc);
        $entries = $languageswitcher->getEntries();

        $this->assertCount(3, $entries, "The number of returned entries is invalid");

        $expectedKeys = ["en", "de", "it"];
        foreach ($entries as $key=>$e) {
            $this->assertSame($expectedKeys[$key], $e->getPage()->getKey());

            if ($expectedKeys[$key] === "de") {
                $this->assertTrue($e->isActive());
            } else {
                $this->assertFalse($e->isActive());
            }
        }
    }


    public function testRelatedDocsAreReturnedFromLevel1()
    {
        $currentDoc = Document::getByPath("/en/en_entry_1");

        $languageswitcher = new \LanguageSwitcher\Switcher($currentDoc);
        $entries = $languageswitcher->getEntries();

        $this->assertCount(3, $entries, "The number of returned entries is invalid");

        $expectedKeys = ["en_entry_1", "de_entry_1", "it_entry_1"];
        foreach ($entries as $key=>$e) {
            $this->assertSame($expectedKeys[$key], $e->getPage()->getKey());

            if ($expectedKeys[$key] === "en_entry_1") {
                $this->assertTrue($e->isActive());
            } else {
                $this->assertFalse($e->isActive());
            }
        }
    }


    public function testRelatedDocsAreReturnedFromLevel2()
    {
        $currentDoc = Document::getByPath("/en/en_entry_1/en_entry_1_2");

        $languageswitcher = new \LanguageSwitcher\Switcher($currentDoc);
        $entries = $languageswitcher->getEntries();

        $this->assertCount(3, $entries, "The number of returned entries is invalid");

        $expectedKeys = ["en_entry_1_2", "de_entry_1_2", "it_entry_1_2"];
        foreach ($entries as $key=>$e) {
            $this->assertSame($expectedKeys[$key], $e->getPage()->getKey());

            if ($expectedKeys[$key] === "en_entry_1_2") {
                $this->assertTrue($e->isActive());
            } else {
                $this->assertFalse($e->isActive());
            }
        }
    }


    public function testOrphanedEntryReturnsParentDocument()
    {
        $currentDoc = Document::getByPath("/it/it_entry_orphaned");

        $languageswitcher = new \LanguageSwitcher\Switcher($currentDoc, "");
        $entries = $languageswitcher->getEntries();

        $this->assertCount(3, $entries, "The number of returned entries is invalid");

        $expectedKeys = ["en", "de", "it_entry_orphaned"];
        foreach ($entries as $key=>$e) {
            $this->assertSame($expectedKeys[$key], $e->getPage()->getKey());

            if ($expectedKeys[$key] === "it_entry_orphaned") {
                $this->assertTrue($e->isActive());
            } else {
                $this->assertFalse($e->isActive());
            }
        }
    }


    protected function tearDown()
    {
        $this->tool->removeDocumentTree();
    }
}