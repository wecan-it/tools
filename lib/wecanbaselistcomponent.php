<?php namespace Wecan\Tools;

abstract class WecanBaseListComponent extends \CBitrixComponent
{
    protected $cacheKeys = ['ITEMS'];
    protected $cacheAddon = [];
    protected $navParams = [];

    protected function readDataFromCache() {
        if($this->arParams['CACHE_TYPE'] == 'N') return false;

        return !($this->StartResultCache(false, $this->cacheAddon));
    }

    protected function putDataToCache() {
        if(is_array($this->cacheKeys) && sizeof($this->cacheKeys) > 0) {
            $this->SetResultCacheKeys($this->cacheKeys);
        }
    }

    protected function abortDataCache() {
        $this->AbortResultCache();
    }

    protected function isAjaxRequest() {
        return \Bitrix\Main\Context::getCurrent()->getRequest()->isPost()
        && $this->arParams['AJAX_MODE'] == 'Y'
        && $this->arParams['AJAX_ID'] == \Bitrix\Main\Context::getCurrent()->getRequest()->getPost('bxajaxid');
    }

    protected function executeProlog() {
        if($this->isAjaxRequest()) {
            $GLOBALS['APPLICATION']->RestartBuffer();
        }
        if($this->arParams['COUNT'] > 0) {
            if($this->arParams['SHOW_NAV'] == 'Y') {
                \CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');
                $this->navParams = ['nPageSize' => $this->arParams['COUNT']];
                $arNavigation = \CDBResult::GetNavParams($this->navParams);
                $this->cacheAddon = [$arNavigation];
            } else {
                $this->navParams = ['nTopCount' => $this->arParams['COUNT']];
            }
        }
    }

    protected function executeEpilog() {
        if($this->isAjaxRequest()) {
            die();
        }
    }

    public function onPrepareComponentParams($params) {
        $result = [
            'SHOW_NAV'   => in_array($params['SHOW_NAV'], ['Y', 'N']) ? $params['SHOW_NAV'] : 'Y',
            'COUNT'      => intval($params['COUNT']) > 0 ? intval($params['COUNT']) : 30,
            'CACHE_TIME' => intval($params['CACHE_TIME']) > 0 ? intval($params['CACHE_TIME']) : 60,
            'CACHE_TYPE' => in_array($params['CACHE_TYPE'], ['Y', 'N']) ? $params['CACHE_TYPE'] : 'N',
        ];

        return array_merge($result, $params);
    }

    public function executeComponent() {
        try {
            $this->executeProlog();
            if(!$this->readDataFromCache()) {
                $this->getResult();
                $this->putDataToCache();
                $this->includeComponentTemplate();
            }
            $this->executeEpilog();
        } catch(\Exception $e) {
            $this->abortDataCache();
            ShowError($e->getMessage());
        }
    }

    abstract public function getResult();
}