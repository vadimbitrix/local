<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Context;
use Bitrix\Sale;
use Devob\Helpers\Utilities;

class DevobCartComponent extends CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    private const CURRENCY = 'RUB';

    /* -------------------------------------------------------------------- */
    /*  Controllerable                                                      */
    /* -------------------------------------------------------------------- */
    public function configureActions(): array
    {
        $post = new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]);

        return [
            'getCart'     => ['prefilters' => []],
            'addToCart'   => ['prefilters' => [$post]],
            'remove'      => ['prefilters' => [$post]],
            'clear'       => ['prefilters' => [$post]],
        ];
    }

    /* -------------------------------------------------------------------- */
    /*  executeComponent                                                    */
    /* -------------------------------------------------------------------- */
    public function executeComponent()
    {
        $basketData = $this->collectBasket();

        $this->arResult['ITEMS']         = $basketData['items'];
        $this->arResult['TOTALQTY']      = $basketData['totalQty'];
        $this->arResult['TOTALSUM']      = $basketData['totalSum'];
        $this->arResult['TOTALSUM_PRINT']= $basketData['totalSumPrint'];

        // Для component_epilog.php
        $cp = $this->__component;
        if (is_object($cp))
        {
            $cp->SetResultCacheKeys([
                'ITEMS',
                'TOTALQTY',
                'TOTALSUM',
                'TOTALSUM_PRINT'
            ]);
        }

        $this->includeComponentTemplate();
    }

    /* -------------------------------------------------------------------- */
    /*  ACTIONS                                                             */
    /* -------------------------------------------------------------------- */

    /**
     * Вернуть текущую корзину
     * @return array|true[]
     */
    public function getCartAction(): array
    {
        $data = $this->collectBasket();
        return ['success' => true] + $data;
    }

    /**
     * Добавить товар
     * @param int $productId
     * @param int $qty
     * @return array
     */
    public function addToCartAction(int $productId, int $qty = 1): array
    {
        if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
            return ['success'=>false, 'error'=>'Модуль sale&catalog не подключён'];
        }

        $siteId = Context::getCurrent()->getSite();
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), $siteId);

        $item = $basket->getExistsItem('catalog', $productId);
        if ($item) {
            $item->setField('QUANTITY', 1);
        } else {
            $item = $basket->createItem('catalog', $productId);
            $item->setFields([
                'QUANTITY'               => 1,
                'CURRENCY'               => self::CURRENCY,
                'LID'                    => $siteId,
                'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
            ]);
        }

        $saveResult = $basket->save();
        if (!$saveResult->isSuccess()) {
            return ['success' => false, 'error' => implode('; ', $saveResult->getErrorMessages())];
        }

        $cartData = $this->collectBasket();

        return ['success' => true] + $cartData + ['cart' => $cartData];
    }

    /**
     * Удалить позицию
     * @param int $basketItemId
     * @return array|true[]
     */
    public function removeAction(int $basketItemId): array
    {
        $basket = $this->loadBasket();
        if (!$basket) { return ['success'=>false, 'error'=>'Корзина не найдена!']; }

        $item = $basket->getItemById($basketItemId);
        if ($item) {
            $item->delete();
            $basket->save();
        }

        return ['success'=>true] + $this->collectBasket();
    }

    /**
     * Очистить корзину
     * @return array|true[]
     */
    public function clearAction(): array
    {
        $basket = $this->loadBasket();
        if ($basket) {
            foreach ($basket as $item) {
                $item->delete();
            }
            $basket->save();
        }
        return ['success'=>true] + $this->collectBasket();
    }

    /* -------------------------------------------------------------------- */
    /*  HELPERS                                                             */
    /* -------------------------------------------------------------------- */

    /**
     * Загружает текущую корзину Bitrix\Sale\Basket
     * @return Sale\Basket|null
     */
    private function loadBasket(): ?Sale\Basket
    {
        if (!Loader::includeModule('sale')) return null;
        $siteId = Context::getCurrent()->getSite();
        return Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), $siteId);
    }

    /**
     * Формирует лёгкий массив для фронта
     * @return array
     */
    private function collectBasket(): array
    {
        $basket   = $this->loadBasket();
        $currency = self::CURRENCY;

        if (!$basket) {
            return [
                'items'         => [],
                'totalQty'      => 0,
                'totalSum'      => 0,
                'totalSumPrint' => Utilities::formatCurrency(0, $currency),
            ];
        }

        $items     = [];
        $totalSum  = 0;
        $totalQty  = 0;

        foreach ($basket as $item) {
            $quantity = (int)$item->getQuantity();
            if ($quantity <= 0) {
                $quantity = 1;
            }

            $price = (float)$item->getPrice();
            $itemCurrency = $item->getCurrency() ?: $currency;
            $sum = $price * $quantity;
            $items[] = [
                'basketId'   => $item->getId(),
                'productId'  => $item->getProductId(),
                'name'       => $item->getField('NAME'),
                'qty'        => $quantity,
                'price'      => $price,
                'pricePrint' => Utilities::formatCurrency($price, $itemCurrency),
                'sum'        => $sum,
                'sumPrint'   => Utilities::formatCurrency($sum, $itemCurrency),
                'url'        => $item->getField('DETAIL_PAGE_URL') ?: '',
                'image'      => Utilities::getProductImage((int)$item->getProductId()),
            ];

            $totalSum += $sum;
            $totalQty += $quantity;
        }
        return [
            'items'         => $items,
            'totalQty'      => $totalQty,
            'totalSum'      => $totalSum,
            'totalSumPrint' => Utilities::formatCurrency($totalSum, $currency)
        ];
    }
}
