<? namespace Wecan\Tools;

trait Translatable
{
    public $curLang;

    public function translate() {
        if($this->isTranslationNeeded()) {
            $this->arResult = $this->translateElement($this->arResult);
            if(is_array($this->arResult['ITEMS'])) {
                foreach($this->arResult['ITEMS'] as &$arItem) {
                    $arItem = $this->translateElement($arItem);
                }
            }
        }
    }

    protected function translateElement($arElement) {
        foreach(['NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT'] as $field) {
            if(array_key_exists($field, $arElement) && $arElement['PROPERTIES'][$field . "_" . $this->curLang]['VALUE']) {
                $arElement[$field] = is_array($arElement['PROPERTIES'][$field . "_" . $this->curLang]['VALUE'])
                    ? $arElement['PROPERTIES'][$field . "_" . $this->curLang]['~VALUE']['TEXT']
                    : $arElement['PROPERTIES'][$field . "_" . $this->curLang]['VALUE'];
            }
        }

        return $arElement;
    }

    protected function translateSection($arSection) {
        return $arSection;
    }

    protected function isTranslationNeeded() {
        return $this->curLang !== 'RU';
    }

    public function onPrepareComponentParams($arParams) {
        $arParams['PROPERTY_CODE'][] = "NAME";

        return $arParams;
    }

    public function executeComponent() {
        $this->curLang = strtoupper(\Bitrix\Main\Context::getCurrent()->getLanguage());
        parent::executeComponent();
    }
}