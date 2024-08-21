<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Radmir\Cartshare\Cart;



if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Loc::loadMessages(__FILE__);

class CartShareComponent extends CBitrixComponent
{

	protected function checkModules()
	{
		if (!Loader::includeModule('radmir.cartshare')){
			ShowError('Module `radmir.cartshare` not installed');
			return false;
		}

		return true;
	}

	

	public function executeComponent()
	{
		if ($this->checkModules()) {
			if (!Cart::isEmpty()) {
				$this->includeComponentTemplate();
			}
		}
	}
}
