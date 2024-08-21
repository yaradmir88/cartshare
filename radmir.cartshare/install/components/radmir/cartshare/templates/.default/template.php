<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CBitrixComponentTemplate $this */
/** @var CAllMain $APPLICATION */
/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
$this->addExternalJS($templateFolder.'/assets/rmodal/rmodal.js');
$this->addExternalCss($templateFolder.'/assets/rmodal/rmodal.css');
?>
<div class="basket-checkout-container">
	<button id="rmodal-show" class="btn btn-md btn-primary"><?=Loc::getMessage('CARTSHARE_BUTTON_TEXT')?></button>
</div>
<div id="rmodal" class="rmodal">
	<div class="rmodal-dialog">
		<button id="rmodal-close" class="rmodal-close">×</button>
		<div class="rmodal-header">
			<h2><?=Loc::getMessage('CARTSHARE_POPUP_HEADER')?></h2>
		</div>
		<div class="rmodal-content">
			<div class="mb-3"><?=Loc::getMessage('CARTSHARE_POPUP_TEXT')?></div>
			<div class="row">
				<div class="col-8">
					<input type="text" readonly="readonly" value="" id="cart-link" class="form-control">
				</div>
				<div class="col-4">
					<button id="copy-button" class="btn btn-md btn-primary w-100"><?=Loc::getMessage('CARTSHARE_POPUP_BUTTON')?></button>
				</div>
			</div>
		</div>
	</div>
</div>