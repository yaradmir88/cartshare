<?php 
use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

Loc::loadMessages(__FILE__);
?>
<form action="<?=$APPLICATION->GetCurPage();?>">
    <?=bitrix_sessid_post();?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="id" value="radmir.cartshare">
    <input type="hidden" name="uninstall" value="Y">
    <input type="hidden" name="step" value="2">
    <?= CAdminMessage::ShowMessage(Loc::getMessage('MOD_UNINST_WARN'));?>
    <p><?=Loc::getMessage('RADMIR_CARTSHARE_SAVE_DATA')?></p>
    <p><input type="checkbox" name="save_data" id="save_data" value="Y" checked><label for="save_data"><?=Loc::getMessage('RADMIR_CARTSHARE_SAVE_HL')?></label></p>
    <input type="submit" name="" value="<?=Loc::getMessage('MOD_UNINST_DEL')?>">
</form>