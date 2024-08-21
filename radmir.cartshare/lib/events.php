<?php 
namespace Radmir\Cartshare;

use Bitrix\Main\Loader;
use Bitrix\Main\Application as App;
use Bitrix\Main\Config\Option;
use Radmir\Cartshare\SavedCartTable;
use Radmir\Cartshare\Cart;
// класс события
class Events
{
    
    static public function checkCartAction()
    {
        Loader::includeModule('sale');
        Loader::includeModule("catalog");
        
        $context = App::getInstance()->getContext();
        $request = $context->getRequest();

        
        
        $moduleOptions = Option::getForModule("radmir.cartshare");

        if (!empty($moduleOptions['action']) && !empty($request->get($moduleOptions['action'])) && is_numeric($request->get($moduleOptions['action']))) {
            $savedCardItem = SavedCartTable::getById($request->get($moduleOptions['action']))->fetch();
            if (!empty($savedCardItem['UF_CART'])) {
                try {
                    if ($moduleOptions['clean'] == 'Y') {
                        Cart::clearCurrentCart();
                    }

                    $basketItems = \Bitrix\Main\Web\Json::decode($savedCardItem['UF_CART'], true);

                    Cart::fillCurrentCart($basketItems);
                    SavedCartTable::increaseViewCounter($savedCardItem['ID']);

                    if (!empty($moduleOptions['redirect'])) {
                        \LocalRedirect($moduleOptions['redirect'], false, "301 Moved permanently");
                    }

                } catch (\Bitrix\Main\SystemException $e) {
                    echo $e->getMessage();
                }

            }
        }


        // $fields = $event->getParameter("fields");
        // echo "<pre>";
        // echo "Обработчик события";
        // var_dump($fields);
        // echo "</pre>";
    }
}