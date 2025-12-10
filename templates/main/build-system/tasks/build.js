import { findComponentScss, checkCssChanges } from './check.js';
import { buildScssComponent, buildMinifiedOnly } from '../builders/scss.js';
import { getComponentName } from '../utils/component.js';
import fs from 'fs';
import path from 'path';

// Хранилище снапшотов для watch режима
let watchSnapshots = new Map();

/**
 * Создание снапшота CSS файлов при запуске watch
 */
export async function createWatchSnapshot() {
    console.log('📸 Создание снапшота CSS файлов для watch режима...'.blue);

    const scssFiles = findComponentScss();
    const snapshots = new Map();

    for (const scssPath of scssFiles) {
        const cssPath = scssPath.replace('.scss', '.css');

        if (fs.existsSync(cssPath)) {
            const cssContent = fs.readFileSync(cssPath, 'utf8');
            snapshots.set(scssPath, {
                cssContent,
                timestamp: Date.now(),
                cssPath
            });
        }
    }

    watchSnapshots = snapshots;
    console.log(`📸 Создан снапшот для ${snapshots.size} компонентов`.gray);

    return snapshots;
}

/**
 * Проверка внешних изменений в watch режиме
 */
export async function checkExternalChangesInWatch() {
    if (watchSnapshots.size === 0) {
        console.log('⚠️  Снапшот не создан. Создаем...'.yellow);
        await createWatchSnapshot();
        return [];
    }

    const externalChanges = [];

    for (const [scssPath, snapshot] of watchSnapshots) {
        const cssPath = snapshot.cssPath;

        if (fs.existsSync(cssPath)) {
            const currentCssContent = fs.readFileSync(cssPath, 'utf8');

            // Сравниваем с исходным снапшотом (не с новым SCSS!)
            if (currentCssContent !== snapshot.cssContent) {
                externalChanges.push({
                    scssPath,
                    cssPath,
                    originalContent: snapshot.cssContent,
                    currentContent: currentCssContent,
                    componentName: getComponentName(scssPath)
                });
            }
        }
    }

    return externalChanges;
}

/**
 * Создание backup внешних изменений
 */
export async function backupExternalChanges(externalChanges) {
    const backupDir = './.bitrix-build/external-changes-backup';

    if (!fs.existsSync(backupDir)) {
        fs.mkdirSync(backupDir, { recursive: true });
    }

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

    for (const change of externalChanges) {
        const backupFileName = `${change.componentName}_${timestamp}.css`;
        const backupPath = path.join(backupDir, backupFileName);

        fs.writeFileSync(backupPath, change.currentContent);
        console.log(`💾 Backup создан: ${backupPath}`.green);
    }
}

/**
 * Обработка внешних изменений
 */
export async function handleExternalChanges(externalChanges) {
    if (externalChanges.length === 0) return true;

    console.log('\n🚨 ВНИМАНИЕ: Обнаружены внешние изменения CSS файлов!'.red.bold);
    console.log('📝 Возможно, CSS был изменен через админку Битрикс или другими разработчиками'.yellow);

    for (const change of externalChanges) {
        console.log(`\n📁 Компонент: ${change.componentName}`.cyan);
        console.log(`   CSS файл: ${change.cssPath}`.gray);

        // Показываем различия
        const { showDifferences } = await import('../utils/css.js');
        showDifferences(change.originalContent, change.currentContent, change.componentName);
    }

    console.log('\n💾 Внешние изменения автоматически сохранены в backup'.green);
    console.log('🔄 Продолжаем watch режим (можете восстановить из backup при необходимости)'.cyan);

    return true; // Продолжаем work, но с backup
}

/**
 * Умная сборка для watch режима
 */
export async function buildForWatch() {
    console.log('🔄 Watch сборка SCSS файлов...'.cyan);

    // Проверяем внешние изменения перед сборкой
    const externalChanges = await checkExternalChangesInWatch();

    if (externalChanges.length > 0) {
        console.log(`⚠️  Обнаружены внешние изменения в ${externalChanges.length} файлах`.yellow);

        // Создаем backup и показываем изменения
        await backupExternalChanges(externalChanges);
        await handleExternalChanges(externalChanges);
    }

    const scssFiles = findComponentScss();
    if (scssFiles.length === 0) return;

    // Собираем файлы
    await Promise.all(
        scssFiles.map(scssPath => buildScssComponent(scssPath))
    );

    // Обновляем снапшот после успешной сборки
    await updateWatchSnapshot(scssFiles);

    console.log('✅ Watch сборка завершена'.green);
}

/**
 * Обновление снапшота после сборки
 */
async function updateWatchSnapshot(scssFiles) {
    for (const scssPath of scssFiles) {
        const cssPath = scssPath.replace('.scss', '.css');

        if (fs.existsSync(cssPath)) {
            const cssContent = fs.readFileSync(cssPath, 'utf8');
            watchSnapshots.set(scssPath, {
                cssContent,
                timestamp: Date.now(),
                cssPath
            });
        }
    }
}


/**
 * Основная сборка с проверкой изменений
 */
export async function buildWithCheck() {
    console.log('🚀 Запуск сборки шаблонов компонентов Битрикс...'.blue.bold);

    // Сначала проверяем изменения
    const isSync = await checkCssChanges();

    if (!isSync) {
        console.log('\n❌ Сборка остановлена из-за несинхронизированных изменений'.red.bold);
        console.log('📝 CSS файлы были изменены через админку Битрикс'.yellow);
        console.log('🔄 Перенесите изменения в SCSS файлы и запустите сборку заново'.yellow);
        console.log('⚡ Или используйте npm run force-build для принудительной сборки'.yellow);
        throw new Error('Несинхронизированные изменения');
    }

    return await buildScssFiles();
}

/**
 * Принудительная сборка без проверок
 */
export async function forceBuild() {
    console.log('⚡ Принудительная сборка всех компонентов...'.yellow.bold);
    console.log('⚠️  Игнорируются проверки изменений CSS'.yellow);

    return await buildScssFiles();
}

/**
 * Сборка SCSS файлов
 */
async function buildScssFiles() {
    console.log('\n🔨 Начинаем сборку SCSS файлов...'.blue);

    const scssFiles = findComponentScss();

    if (scssFiles.length === 0) {
        console.log('⚠️  Не найдено SCSS файлов для сборки'.yellow);
        console.log('💡 Создайте файлы style.scss в папках компонентов'.gray);
        throw new Error('Нет файлов для сборки');
    }

    await Promise.all(
        scssFiles.map(scssPath => buildScssComponent(scssPath))
    );

    console.log('\n🎉 Сборка завершена успешно!'.green.bold);
    console.log('📂 Все CSS файлы обновлены и готовы к использованию'.green);
}

/**
 * Сборка только минифицированных файлов
 */
export async function buildMinOnly() {
    console.log('📦 Сборка только минифицированных CSS файлов...'.blue);

    const scssFiles = findComponentScss();

    if (scssFiles.length === 0) {
        console.log('⚠️  Не найдено SCSS файлов для сборки'.yellow);
        throw new Error('Нет файлов для сборки');
    }

    await Promise.all(
        scssFiles.map(scssPath => buildMinifiedOnly(scssPath))
    );

    console.log('\n🎉 Минификация завершена!'.green.bold);
}
