<?php 
namespace Radmir\Cartshare;
use Bitrix\Sale;
use Bitrix\Main\Type; 
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Radmir\Cartshare\SavedCartTable;

Loc::loadMessages(__FILE__);
Loader::includeModule('sale');
Loader::includeModule("catalog");

class Cart 
{
    public static function clearCurrentCart() {

        $currentBasket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());
        $basketItems = $currentBasket->getBasketItems();

        foreach ($basketItems as $basketItem) {
            $currentBasket->getItemById($basketItem->getId())->delete();
        }

        return $currentBasket->save();
    }

    public static function fillCurrentCart($items) {
        $basketItems = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());
        foreach ($items as $item) {
            $tmpBasketItem = $basketItems->createItem('catalog', $item['id']);
            $tmpBasketItem->setFields([
                'QUANTITY' => $item['quantity'],
                'CURRENCY' => \Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                'LID' => Context::getCurrent()->getSite(),
                'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
            ]);
        }
	    return $basketItems->save();
    }

    public static function getCartLink() {
        $result = '';
        $jsonCart = self::getJSONCart();
        $shortUri = \CBXShortUri::GenerateShortUri();

        $saveResult = SavedCartTable::add([
            'UF_DATE' => new Type\DateTime(),
            'UF_CART' => $jsonCart,
            'UF_USER_ID' => \Bitrix\Main\Engine\CurrentUser::get()->getId(),
            'UF_CNT_VIEW' => 0,
            'UF_LINK' => $shortUri,
        ]);

        if ($saveResult->isSuccess()) {
            $id = $saveResult->getId();
            $paramName = \Bitrix\Main\Config\Option::get("radmir.cartshare", "action", "savecart");

            \CBXShortUri::Add([
                "URI" => "/?".$paramName."=".$id,
                "SHORT_URI" => $shortUri,
                "STATUS" => "301",
            ]);

            $request = Context::getCurrent()->getRequest();         
            $scheme = ($request->isHttps()) ? 'https' : 'http';
            $server = Context::getCurrent()->getServer();

            $result = $scheme.'://'.$server->getHttpHost().'/'.$shortUri;
        }

        return $result;
    }

    private static function getJSONCart() {
        $basketItems = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());

        $items = [];
        foreach ($basketItems as $basketItem) {
            $items[] = ['id' => $basketItem->getProductId(), 'quantity' => $basketItem->getQuantity()];
        }

        return ($items) ? \Bitrix\Main\Web\Json::encode($items) : false;
    }

    public static function isEmpty() {
        $basketItems = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());
        return !(count($basketItems) > 0);
    }

}
