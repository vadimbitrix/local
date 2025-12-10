<?php

namespace Devob\Lib\Builder;

use Bitrix\Main\Page\Asset;

/**
 * Универсальный загрузчик Vue3 компонентов для Bitrix
 */
class VueComponentLoader
{
    private $templateFolder;
    private $componentId;
    private $arResult;
    private $config;

    public function __construct($templateFolder, $componentId, $arResult, $config = [])
    {
        $this->templateFolder = $templateFolder;
        $this->componentId = $componentId;
        $this->arResult = $arResult;
        $this->config = $config;
        $this->config['namespace'] = $this->config['namespace'] ?? 'BitrixVueData';
        $this->config['globalVarName'] = $this->config['globalVarName'] ?? 'template';
        $this->config['dataKeys'] = $this->config['dataKeys'] ?? ['ITEMS'];
    }

    /**
     *  Основной метод - делает всё в одном вызове
     */
    public function initialize()
    {
        // Передаем данные из PHP в JavaScript
        $this->sendDataToJS();

        // Подключаем Vue файлы
        $scriptsCount = $this->includeVueFiles();

        // Если файлы есть - создаем Vue приложение
        if ($scriptsCount > 0) {
            $this->createVueApp();
        }

        return $scriptsCount > 0;
    }

    /**
     *  Передача данных из PHP в JS
     */
    private function sendDataToJS()
    {
        $data = [];

        // Берем нужные ключи из $arResult
        foreach ($this->config['dataKeys'] as $key) {
            if (!isset($this->arResult[$key])) {
                continue;
            }

            $value = $this->arResult[$key];
            $normalizedKey = strtolower($key);
            $data[$normalizedKey] = $value;

            $camelKey = $this->snakeToCamel($normalizedKey);
            if ($camelKey !== $normalizedKey && !array_key_exists($camelKey, $data)) {
                $data[$camelKey] = $value;
            }
        }

        // Добавляем служебные данные, сохраняя существующие настройки компонента
        $existingSettings = [];
        if (isset($data['settings']) && is_array($data['settings'])) {
            $existingSettings = $data['settings'];
        }

        $loaderSettings = [
            'componentId' => $this->componentId,
            'templateFolder' => $this->templateFolder,
        ];
        $data['settings'] = array_merge($existingSettings, $loaderSettings);
        $namespace = $this->config['namespace'];
        $jsonData = \Bitrix\Main\Web\Json::encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "<script>";
        echo "window.{$namespace} = window.{$namespace} || {};";
        echo "window.{$namespace}['{$this->componentId}'] = " . $jsonData . ";";
        echo "</script>";
    }

    /**
     *  Подключение Vue файлов
     */
    private function includeVueFiles()
    {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . $this->templateFolder;
        $files = [];

        //  Дочерние компоненты ПЕРВЫМИ
        $subComponents = glob($basePath . '/vue-component/*.js');
        foreach ($subComponents as $file) {
            $files[] = 'vue-component/' . basename($file);
        }

        //  Главный компонент ПОСЛЕДНИМ
        if (file_exists($basePath . '/script.js')) {
            $files[] = 'script.js';
        }

        //  Подключаем через Bitrix Asset
        foreach ($files as $file) {
            Asset::getInstance()->addJs($this->templateFolder . '/' . $file);
        }

        return count($files);
    }

    /**
     *  Создание Vue 3 приложения
     */
    private function createVueApp()
    {
        $namespace = $this->config['namespace'];
        $globalVar = $this->config['globalVarName'];
        // Находим дочерние компоненты для регистрации
        $basePath = $_SERVER['DOCUMENT_ROOT'] . $this->templateFolder;
        $subComponents = glob($basePath . '/vue-component/*.js');

        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Проверяем что всё загружено
                if (typeof Vue === 'undefined' || !Vue.createApp) {
                    console.error('❌ Vue 3 не найден!');
                    return;
                }
                if (typeof <?= $globalVar ?> === 'undefined') {
                    console.error('❌ Главный компонент не найден!');
                    return;
                }

                const targetElement = document.getElementById('<?= $this->componentId ?>');
                if (!targetElement) {
                    console.error('❌ DOM элемент не найден: <?= $this->componentId ?>');
                    return;
                }

                // Получаем данные и создаем приложение
                const componentData = window.<?= $namespace ?>['<?= $this->componentId ?>'];

                const { createApp } = Vue;
                let mainComponent;
                if (<?= $globalVar ?>.default) {
                    mainComponent = <?= $globalVar ?>.default;
                } else if (<?= $globalVar ?>.template || <?= $globalVar ?>.data) {
                    mainComponent = <?= $globalVar ?>;
                } else {
                    console.error('❌ Неправильная структура компонента:', <?= $globalVar ?>);
                    return;
                }

                const enhancedComponent = {
                    ...mainComponent,
                    data() {
                        let vueData = {};

                        if (typeof mainComponent.data === 'function') {
                            try {
                                vueData = mainComponent.data.call(this) || {};
                            } catch (error) {
                                console.error('❌ Ошибка при вызове оригинального data():', error);
                            }
                        } else if (mainComponent.data && typeof mainComponent.data === 'object') {
                            vueData = mainComponent.data;
                        }

                        if (typeof vueData !== 'object' || vueData === null) {
                            vueData = {};
                        }
                        return {
                            ...vueData,
                            ...(componentData || {})
                        };
                    }
                };

                const app = createApp(enhancedComponent, componentData || {});
                // Подключаем Pinia к приложению
                if (window.devobPinia) {
                    app.use(window.devobPinia);
                }

                // Регистрируем дочерние компоненты
                <?php foreach ($subComponents as $file):
                $fileName = basename($file, '.js');
                $camelCase = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $fileName))));
                ?>
                if (typeof window.<?= $camelCase ?> !== 'undefined') {
                    app.component('<?= $fileName ?>', window.<?= $camelCase ?>.default || window.<?= $camelCase ?>);
                }
                <?php endforeach; ?>

                // Монтируем приложение
                try {
                    const mountedApp = app.mount(targetElement);
                    window['vue_<?= $this->componentId ?>'] = mountedApp;
                } catch (error) {
                    console.error('❌ Ошибка создания приложения:', error);
                }
            });
        </script>
        <?php
    }
    private function snakeToCamel($string)
    {
        $string = str_replace(['-', '_'], ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return lcfirst($string);
    }
}
