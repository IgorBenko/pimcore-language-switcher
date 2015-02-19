<?php


class LanguageSwitcher_IndexController extends \Pimcore\Controller\Action\Admin {
    
    public function getDocsInOtherBranchesAction() {
        $this->disableViewAutoRender();

        $currentId = (int)$this->getParam("id");

        $currentDoc = \Pimcore\Model\Document::getById($currentId);

        if ($currentDoc) {
            $languageSwitcher = new LanguageSwitcher\Switcher($currentDoc);

            $entries = [];
            foreach ($languageSwitcher->getEntries() as $entry) {
                if (!$entry->isActive()) {
                    $entries[] = [
                        "id" => $entry->getPage()->getId(),
                        "label" => $entry->getLabel(),
                        "path" => $entry->getPath(),
                        "language" => $entry->getPage()->getProperty("language")
                    ];
                }
            }

            $this->_helper->json($entries);
        }
    }

}
