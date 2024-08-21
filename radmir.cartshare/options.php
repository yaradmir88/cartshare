<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Application as App;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(App::getDocumentRoot().BX_ROOT.'/modules/main/options.php');
Loc::loadMessages(__FILE__);

$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);

if ($POST_RIGHT < "S") {
    $APPLICATION->AuthForm(Loc::getMessage("RADMIR_CARTSHARE_ACCESS_DENIED"));
}

Loader::includeModule($module_id);

$aTabs = [
    [
        "DIV" => "main_options_tab",
        "TAB" => Loc::getMessage("RADMIR_CARTSHARE_SETTINGS"),
        "OPTIONS" => [
            ['action', Loc::getMessage("RADMIR_CARTSHARE_OPTION_ACTION"), 'savecart', ['text', 20]],
            ['redirect', Loc::getMessage("RADMIR_CARTSHARE_OPTION_REDIRECT"), '/personal/cart/', ['text', 20]],
            ['clean', Loc::getMessage("RADMIR_CARTSHARE_OPTION_CLEAN"), 'Y', ['checkbox']],
        ]
    ],
    [
        "DIV"   => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS")
    ]
];

if ($request->isPost() && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        foreach ($aTab["OPTIONS"] as $arOption) {
            if (!is_array($arOption)) {
                continue;
            }
            
            if ($request["update"]) {
                $optionValue = $request->getPost($arOption[0]);

                //empty checkbox hack
                if (!empty($arOption[3][0]) && $arOption[3][0] == "checkbox" && $optionValue == "") {
                    $optionValue = "N";
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            }

            if ($request["default"]) {
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }
}

$tabControl = new CAdminTabControl(
    "tabControl",
    $aTabs
);
// отображаем заголовки закладок
$tabControl->Begin();
?>
<form action="<?=$request->getPhpSelf(); ?>?mid=<?=$module_id?>&lang=<?=App::getInstance()->getContext()->getLanguage()?>" method="post">
    <? foreach ($aTabs as $aTab) {
        if ($aTab["OPTIONS"]) {
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab["OPTIONS"]);
        }
    }
    $tabControl->BeginNextTab();
    
    require_once App::getDocumentRoot() . "/bitrix/modules/main/admin/group_rights.php";
    $tabControl->Buttons();
    echo bitrix_sessid_post();
    ?>
    <input class="adm-btn-save" type="submit" name="update" value="<?=Loc::getMessage("RADMIR_CARTSHARE_ACTION_SAVE")?>" />
    <input type="submit" name="default" value="<?=Loc::getMessage("RADMIR_CARTSHARE_ACTION_DEFAULT")?>" />
</form>
<?
$tabControl->End();
