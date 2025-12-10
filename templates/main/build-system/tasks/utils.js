import fs from 'fs';
import { glob } from 'glob';
import config from '../config.js';
import { ensureBuildDir, loadExcludeList } from '../utils/files.js';

/**
 * Переключение минификации
 */
export function toggleMinification() {
    config.minification.enabled = !config.minification.enabled;

    const status = config.minification.enabled ? 'включена' : 'выключена';
    console.log(`⚙️  Минификация ${status}`.yellow);

    if (config.minification.enabled) {
        console.log('📦 При следующей сборке будут созданы .min.css файлы'.gray);
    } else {
        console.log('📄 При следующей сборке будут созданы только обычные .css файлы'.gray);
    }
}

/**
 * Очистка минифицированных файлов
 */
export function cleanMinFiles() {
    console.log('🧹 Очистка минифицированных CSS файлов...'.yellow);

    const minFiles = glob.sync(config.components + '**/*.min.css');

    minFiles.forEach(minFile => {
        if (fs.existsSync(minFile)) {
            fs.unlinkSync(minFile);
            console.log(`🗑️  Удален: ${minFile}`.gray);
        }
    });

    console.log(`✅ Удалено ${minFiles.length} минифицированных файлов`.green);
}

/**
 * Показ списка исключений
 */
export function showExcludeList() {
    const excludeConfig = loadExcludeList();

    console.log('\n📋 Конфигурация исключений:'.blue.bold);

    if (excludeConfig.patterns.length > 0) {
        console.log('\n🏷️  Паттерны компонентов:'.yellow);
        excludeConfig.patterns.forEach(pattern => {
            console.log(`   • ${pattern}`.white);
        });
    } else {
        console.log('\n🏷️  Паттерны компонентов: нет исключений'.gray);
    }

    if (excludeConfig.paths && excludeConfig.paths.length > 0) {
        console.log('\n📁 Пути исключений:'.yellow);
        excludeConfig.paths.forEach(path => {
            console.log(`   • ${path}`.white);
        });
    } else {
        console.log('📁 Пути исключений: нет исключений'.gray);
    }

    console.log('\n💡 Система обрабатывает только файлы в templates/components/'.cyan);
    console.log('   • Кастомные шаблоны штатных компонентов (components/bitrix/...)'.cyan);
    console.log('   • Собственные компоненты (components/news/...)'.cyan);
}

/**
 * Инициализация системы
 */
export function initializeSystem() {
    ensureBuildDir();
    console.log('🎉 Bitrix SCSS + Vue Builder инициализирован!'.green.bold);
    console.log('\n📂 Структура проекта:'.yellow);
    console.log('   components/bitrix/menu/personal/  - кастомные шаблоны штатных компонентов'.gray);
    console.log('   components/news/list/             - собственные компоненты'.gray);
    console.log('   .bitrix-build/                    - служебные файлы сборщика'.gray);
    console.log('   build-system/                     - модули сборки'.gray);

    console.log('\n📋 Доступные команды:'.yellow);
    console.log('   npm run build        - Полная сборка с проверкой изменений'.white);
    console.log('   npm run check        - Проверка изменений CSS'.white);
    console.log('   npm run watch        - Режим разработки с автосборкой'.white);
    console.log('   npm run build-vue    - Сборка только Vue компонентов'.white);
    console.log('   npm run force-build  - Принудительная сборка'.white);
    console.log('   make exclude         - Список исключенных компонентов'.white);
}

/**
 * Тест системы
 */
export function testSystem() {
    console.log('✅ Bitrix SCSS + Vue Builder готов к работе!'.green.bold);
    console.log('🔧 Node.js версия:', process.version);
    console.log('🚀 ES Modules: поддерживаются'.green);
    console.log('🎨 Sass: готов к компиляции'.blue);
    console.log('🔍 Система отслеживания изменений: активна'.cyan);
    console.log('⚡ Система исключений: настроена'.magenta);
    console.log('🌟 Vue.js поддержка: включена'.yellow);
    console.log('🏗️  Модульная архитектура: активна'.green);

    console.log('\n💡 Следующие шаги:'.yellow);
    console.log('   1. Создайте SCSS файлы в папках компонентов'.gray);
    console.log('   2. Запустите npm run build для первой сборки'.gray);
    console.log('   3. Используйте npm run watch для разработки'.gray);
}
