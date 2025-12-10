import fs from 'fs';
import config from '../config.js';
import { findFiles } from '../utils/files.js';
import { getComponentName, isComponentExcluded, getComponentType } from '../utils/component.js';
import { compileScssTempContent, normalizeCss, showDifferences } from '../utils/css.js';

/**
 * Поиск SCSS файлов с фильтрацией исключений
 */
export function findComponentScss() {
    const allScssFiles = findFiles(config.patterns.scss);

    // Фильтруем исключенные компоненты
    const filteredFiles = allScssFiles.filter(scssPath => {
        const isExcluded = isComponentExcluded(scssPath);
        if (isExcluded) {
            console.log(`⏭️  Компонент исключен: ${getComponentName(scssPath)}`.gray);
            return false;
        }
        return true;
    });

    const excludedCount = allScssFiles.length - filteredFiles.length;
    if (excludedCount > 0) {
        console.log(`📊 Найдено SCSS файлов: ${allScssFiles.length}, исключено: ${excludedCount}, к обработке: ${filteredFiles.length}`.blue);
    } else {
        console.log(`📊 Найдено SCSS файлов: ${filteredFiles.length}`.blue);
    }

    return filteredFiles;
}

/**
 * Проверка изменений CSS
 */
export async function checkCssChanges() {
    console.log('🔍 Проверка изменений CSS...'.blue);

    const scssFiles = findComponentScss();
    let hasChanges = false;

    for (const scssPath of scssFiles) {
        const cssPath = scssPath.replace('.scss', '.css');
        const componentName = getComponentName(scssPath);

        if (!fs.existsSync(cssPath)) {
            console.log(`⚠️  CSS файл не найден: ${cssPath}`.yellow);
            console.log(`💡 Запустите npm run build для создания CSS файла`.gray);
            continue;
        }

        try {
            // Проверяем только основной CSS файл (не минифицированный)
            const tempCss = await compileScssTempContent(scssPath);
            if (!tempCss) continue;

            const currentCss = fs.readFileSync(cssPath, 'utf8');

            // Нормализуем CSS для сравнения
            const normalizedTemp = normalizeCss(tempCss);
            const normalizedCurrent = normalizeCss(currentCss);

            if (normalizedTemp !== normalizedCurrent) {
                hasChanges = true;
                console.log(`\n🚨 Найдены изменения в компоненте: ${componentName}`.red.bold);
                console.log(`📁 CSS файл: ${cssPath}`.gray);
                console.log(`📁 SCSS файл: ${scssPath}`.gray);
                console.log(`🏷️  Тип: ${getComponentType(scssPath)}`.cyan);

                // Показываем различия
                showDifferences(tempCss, currentCss, componentName);

                console.log(`\n💡 Действия:`.yellow);
                console.log(`   1. Перенесите изменения из CSS в SCSS файл`.white);
                console.log(`   2. Запустите пересборку: npm run build`.white);
                console.log(`   3. Или принудительную: npm run force-build\n`.white);
            }
        } catch (error) {
            console.log(`❌ Ошибка при проверке ${componentName}: ${error.message}`.red);
        }
    }

    if (!hasChanges) {
        console.log('✅ Все CSS файлы синхронизированы со SCSS'.green);
    }

    return !hasChanges;
}
