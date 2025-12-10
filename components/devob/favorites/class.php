<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Devob\Helpers\Utilities;

class DevobFavoritesComponent extends CBitrixComponent implements Controllerable
{
    /** @var string|null */
    private $entityClass = null;
    private bool $entityInitialized = false;
    private ?int $authorizedUserId = null;
    private bool $authorizationChecked = false;

    public function configureActions(): array
    {
        $post = new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]);

        return [
            'list'   => ['prefilters' => []],
            'add'    => ['prefilters' => [$post]],
            'remove' => ['prefilters' => [$post]],
            'toggle' => ['prefilters' => [$post]],
            'sync'   => ['prefilters' => [$post]],
        ];
    }

    public function onPrepareComponentParams($params)
    {
        $params['HL_BLOCK_ID'] = isset($params['HL_BLOCK_ID']) ? (int)$params['HL_BLOCK_ID'] : 2;

        return parent::onPrepareComponentParams($params);
    }

    public function executeComponent()
    {
        $this->getAuthorizedUserId();
        $this->arResult['SIGNED_PARAMETERS'] = $this->getSignedParametersSafe();
        $this->arResult['HL_BLOCK_ID'] = (int)($this->arParams['HL_BLOCK_ID'] ?? 2);

        $data = $this->getFavoritesData();

        $this->arResult['ITEMS'] = $data['items'];
        $this->arResult['TOTAL'] = $data['total'];
        $this->arResult['IDS'] = $data['ids'];
        $this->arResult['IS_AUTHORIZED'] = $data['is_authorized'];
        $this->arResult['HL_BLOCK_ID'] = $data['hl_block_id'];
        $this->arResult['HAS_PERSISTENT_STORAGE'] = $this->getEntityClass() !== null;

        $this->includeComponentTemplate();
    }

    public function listAction(): array
    {
        if ($this->guardAuthorization() === null) {
            return ['success' => false, 'requiresAuth' => true, 'is_authorized' => false];
        }

        return ['success' => true] + $this->getFavoritesData();
    }

    public function addAction(int $productId): array
    {
        if ($this->guardAuthorization() === null) {
            return ['success' => false, 'requiresAuth' => true, 'is_authorized' => false];
        }

        if ($productId <= 0) {
            return ['success' => false, 'error' => 'Некорректный идентификатор товара'];
        }

        $ids = $this->loadFavoritesIds();
        if (!in_array($productId, $ids, true)) {
            $ids[] = $productId;
            $this->saveFavoritesIds($ids);
        }

        return ['success' => true] + $this->getFavoritesData(true, $ids);
    }

    public function removeAction(int $productId): array
    {
        if ($this->guardAuthorization() === null) {
            return ['success' => false, 'requiresAuth' => true, 'is_authorized' => false];
        }

        if ($productId <= 0) {
            return ['success' => false, 'error' => 'Некорректный идентификатор товара'];
        }

        $ids = array_filter(
            $this->loadFavoritesIds(),
            static fn (int $id) => $id !== $productId
        );
        $this->saveFavoritesIds($ids);

        return ['success' => true] + $this->getFavoritesData(true, $ids);
    }

    public function toggleAction(int $productId): array
    {
        if ($this->guardAuthorization() === null) {
            return ['success' => false, 'requiresAuth' => true, 'is_authorized' => false];
        }

        if ($productId <= 0) {
            return ['success' => false, 'error' => 'Некорректный идентификатор товара'];
        }

        $ids = $this->loadFavoritesIds();
        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_filter($ids, static fn (int $id) => $id !== $productId));
        } else {
            $ids[] = $productId;
        }
        $this->saveFavoritesIds($ids);

        return ['success' => true] + $this->getFavoritesData(true, $ids);
    }

    public function syncAction(array $productIds = []): array
    {
        if ($this->guardAuthorization() === null) {
            return ['success' => false, 'requiresAuth' => true, 'is_authorized' => false];
        }

        $productIds = array_map('intval', $productIds);
        $productIds = array_values(array_unique(array_filter($productIds)));

        $current = $this->loadFavoritesIds();
        $merged = array_values(array_unique(array_merge($current, $productIds)));
        $this->saveFavoritesIds($merged);

        return ['success' => true] + $this->getFavoritesData(true, $merged);
    }

    private function getFavoritesData(bool $withProducts = true, ?array $preloadedIds = null): array
    {
        $isAuthorized = $this->getAuthorizedUserId() !== null;
        $ids = $preloadedIds !== null
            ? array_values(array_unique(array_map('intval', $preloadedIds)))
            : $this->loadFavoritesIds();
        $items = $withProducts ? $this->fetchProducts($ids) : [];

        $signedParameters = $this->arResult['SIGNED_PARAMETERS']
            ?? $this->getSignedParametersSafe();

        return [
            'ids' => $ids,
            'items' => $items,
            'total' => count($ids),
            'signed_parameters' => $signedParameters,
            'is_authorized' => $isAuthorized,
            'hl_block_id' => (int)($this->arParams['HL_BLOCK_ID'] ?? 2),
        ];
    }

    private function getSignedParametersSafe(): string
    {
        if (method_exists($this, 'getSignedParameters')) {
            /** @var callable $method */
            $method = [$this, 'getSignedParameters'];
            return (string)call_user_func($method);
        }

        if (class_exists('Bitrix\\Main\\Component\\ParameterSigner')) {
            return \Bitrix\Main\Component\ParameterSigner::signParameters(
                $this->getName(),
                $this->arParams
            );
        }

        $signer = new \Bitrix\Main\Security\Sign\Signer();
        $serializedParams = serialize($this->arParams);
        $encoded = base64_encode($serializedParams);

        return $signer->sign($encoded, $this->getName());
    }

    private function loadFavoritesIds(): array
    {
        $userId = $this->getAuthorizedUserId();
        $entityClass = $this->getEntityClass();

        if ($userId === null || !$entityClass) {
            return [];
        }

        return $this->loadHighloadFavorites($entityClass, $userId);
    }

    private function saveFavoritesIds(array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        $userId = $this->getAuthorizedUserId();
        $entityClass = $this->getEntityClass();

        if ($userId === null || !$entityClass) {
            return;
        }

        $this->saveHighloadFavorites($entityClass, $userId, $ids);
    }

    private function getAuthorizedUserId(): ?int
    {
        if ($this->authorizationChecked) {
            return $this->authorizedUserId;
        }

        $this->authorizationChecked = true;

        global $USER;

        if (!is_object($USER) || !$USER->IsAuthorized()) {
            $this->arResult['IS_AUTHORIZED'] = false;
            return $this->authorizedUserId = null;
        }

        $userId = (int)$USER->GetID();
        if ($userId > 0) {
            $this->arResult['IS_AUTHORIZED'] = true;
            return $this->authorizedUserId = $userId;
        }

        $this->arResult['IS_AUTHORIZED'] = false;

        return $this->authorizedUserId = null;
    }

    private function guardAuthorization(): ?int
    {
        global $USER;

        if (!is_object($USER) || !$USER->IsAuthorized()) {
            $this->arResult['IS_AUTHORIZED'] = false;
            return null;
        }

        return $this->getAuthorizedUserId();
    }

    private function loadHighloadFavorites(string $entityClass, int $userId): array
    {
        try {
            $result = $entityClass::getList([
                'select' => ['UF_PRODUCT_ID'],
                'filter' => ['UF_USER_ID' => $userId],
            ]);
        } catch (SystemException $exception) {
            \Bitrix\Main\Diag\Debug::dumpToFile('Favorites HL getList error: ' . $exception->getMessage(), date('d.m.Y H:i:s'), 'LogFavorites');
            return [];
        }

        $ids = [];
        while ($row = $result->fetch()) {
            $ids[] = (int)$row['UF_PRODUCT_ID'];
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function saveHighloadFavorites(string $entityClass, int $userId, array $ids): void
    {
        try {
            $existing = $entityClass::getList([
                'select' => ['ID', 'UF_PRODUCT_ID'],
                'filter' => ['UF_USER_ID' => $userId],
            ])->fetchAll();
        } catch (SystemException $exception) {
            \Bitrix\Main\Diag\Debug::dumpToFile('Favorites HL getList error: ' . $exception->getMessage(), date('d.m.Y H:i:s'), 'LogFavorites');
            return;
        }

        $existingMap = [];
        foreach ($existing as $row) {
            $existingMap[(int)$row['UF_PRODUCT_ID']] = (int)$row['ID'];
        }

        foreach ($existingMap as $productId => $rowId) {
            if (!in_array($productId, $ids, true)) {
                try {
                    $entityClass::delete($rowId);
                } catch (SystemException $exception) {
                    \Bitrix\Main\Diag\Debug::dumpToFile('Favorites HL delete error: ' . $exception->getMessage(), date('d.m.Y H:i:s'), 'LogFavorites');
                }
            }
        }

        foreach ($ids as $productId) {
            if (!isset($existingMap[$productId])) {
                $fields = [
                    'UF_PRODUCT_ID' => $productId,
                    'UF_USER_ID' => $userId,
                ];

                try {
                    $entityClass::add($fields);
                } catch (SystemException $exception) {
                    \Bitrix\Main\Diag\Debug::dumpToFile('Favorites HL add error: ' . $exception->getMessage(), date('d.m.Y H:i:s'), 'LogFavorites');
                }
            }
        }
    }

    private function getEntityClass(): ?string
    {
        if ($this->entityInitialized) {
            return $this->entityClass;
        }

        $this->entityInitialized = true;

        $hlBlockId = (int)($this->arParams['HL_BLOCK_ID'] ?? 2);
        if ($hlBlockId <= 0) {
            return $this->entityClass = null;
        }

        if (!Loader::includeModule('highloadblock')) {
            return $this->entityClass = null;
        }

        $hlBlock = HL\HighloadBlockTable::getById($hlBlockId)->fetch();
        if (!$hlBlock) {
            return $this->entityClass = null;
        }

        $entity = HL\HighloadBlockTable::compileEntity($hlBlock);
        return $this->entityClass = $entity->getDataClass();
    }

    private function fetchProducts(array $productIds): array
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if (empty($productIds)) {
            return [];
        }

        if (!Loader::includeModule('iblock')) {
            return [];
        }

        Loader::includeModule('currency');

        $orderMap = array_flip($productIds);
        $items = [];

        $select = [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'DETAIL_PAGE_URL',
            'PREVIEW_PICTURE',
            'CATALOG_GROUP_1',
            'CATALOG_PRICE_1',
            'CATALOG_CURRENCY_1',
        ];

        $res = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => 8, 'ID' => $productIds, 'ACTIVE' => 'Y'],
            false,
            false,
            $select
        );

        while ($row = $res->GetNext()) {
            $productId = (int)$row['ID'];
            $price = isset($row['CATALOG_PRICE_1']) ? (float)$row['CATALOG_PRICE_1'] : 0.0;
            $currency = $row['CATALOG_CURRENCY_1'] ?: 'RUB';

            $items[$productId] = [
                'productId' => $productId,
                'name' => $row['NAME'],
                'price' => $price,
                'pricePrint' => $price > 0 ? Utilities::formatCurrency($price, $currency) : '',
                'url' => $row['DETAIL_PAGE_URL'],
                'image' => $this->resolveImage((int)$row['PREVIEW_PICTURE']),
            ];
        }

        uasort($items, static function ($a, $b) use ($orderMap) {
            return $orderMap[$a['productId']] <=> $orderMap[$b['productId']];
        });

        return array_values($items);
    }

    private function resolveImage(int $fileId): string
    {
        if ($fileId > 0) {
            $file = CFile::GetFileArray($fileId);
            if ($file && isset($file['SRC'])) {
                return (string)$file['SRC'];
            }
        }

        return '/upload/no-photo-white.png';
    }
}
