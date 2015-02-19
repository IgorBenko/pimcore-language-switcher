<?php

namespace LanguageSwitcher;

use Pimcore\API\Plugin as PluginLib;
use Pimcore\Model\Property\Predefined;
use Pimcore\Model\WebsiteSetting;

class Plugin extends PluginLib\AbstractPlugin implements PluginLib\PluginInterface {

    public function init() {

    }

	public static function install (){

        $property = Predefined::getByKey(Switcher::BRANCH_ROOT_PROPERTY_KEY);
        if (!$property->getId()) {
            $data = [
                "key" => Switcher::BRANCH_ROOT_PROPERTY_KEY,
                "name" => "LanguageSwitcher: Root of the branch",
                "description" => "Add this option to all of the branch roots",
                "ctype" => "document",
                "type" => "document",
                "inheritable" => true
            ];
            $property = Predefined::create();
            $property->setValues($data);
            $property->save();
        }


        $websiteSetting = WebsiteSetting::getByName(Switcher::PARENT_NODE_WSETTING_NAME);
        if (!$websiteSetting) {
            $dataWebsiteSetting = [
                "name" => Switcher::PARENT_NODE_WSETTING_NAME,
                "type" => "document",
                "data" => 1
            ];
            $websiteSetting = new WebsiteSetting();
            $websiteSetting->setValues($dataWebsiteSetting);
            $websiteSetting->save();
        }


        return "LanguageSwitcher successfuly installed";
	}
	
	public static function uninstall (){
        $property = Predefined::getByKey(Switcher::BRANCH_ROOT_PROPERTY_KEY);
        $property->delete();

        $websiteSetting = WebsiteSetting::getByName(Switcher::PARENT_NODE_WSETTING_NAME);
        $websiteSetting->delete();

        return "LanguageSwitcher successfuly uninstalled";
	}

	public static function isInstalled () {
        $property = Predefined::getByKey(Switcher::BRANCH_ROOT_PROPERTY_KEY);
        $websiteSetting = WebsiteSetting::getByName(Switcher::PARENT_NODE_WSETTING_NAME);

        return $property->getId() && $websiteSetting;
	}

    public static function getTranslationFile($language) {
        if(file_exists(PIMCORE_PLUGINS_PATH . "/LanguageSwitcher/texts/" . $language . ".csv")){
            return "/LanguageSwitcher/texts/" . $language . ".csv";
        }
        return "/LanguageSwitcher/texts/en.csv";
    }
}
