<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Radmir\Cartshare\Cart;


class CartShareController extends \Bitrix\Main\Engine\Controller
{
    public function configureActions(): array
    {
        return [
            'getLink' => [
                'prefilters' => []
            ]
        ];
    }
	public function getLinkAction()
	{
		return ['link' => Cart::getCartLink()];
	}
}