import { loadExcludeList } from './files.js';

/**
 * Получение имени компонента из пути файла
 */
export function getComponentName(filePath) {
    const parts = filePath.split('/');

    // Для Битрикс компонентов: components/bitrix/menu/personal/ -> menu.personal
    if (parts.includes('bitrix')) {
        const bitrixIndex = parts.indexOf('bitrix');
        const componentDir = parts[bitrixIndex + 1];  // menu
        const templateDir = parts[bitrixIndex + 2];   // personal
        return `${componentDir}.${templateDir}`;
    }

    // Для собственных компонентов: components/news/list/ -> news.list
    const componentDir = parts[parts.length - 3];    // news
    const templateDir = parts[parts.length - 2];     // list
    return `${componentDir}.${templateDir}`;
}

/**
 * Получение имени Vue подкомпонента
 */
export function getVueComponentName(vuePath) {
    const parts = vuePath.split('/');
    const componentDir = parts[parts.length - 4];  // bitrix или news
    const templateDir = parts[parts.length - 3];   // menu или list
    const fileName = parts[parts.length - 1];      // menu-item.vue

    return `${componentDir}.${templateDir}/${fileName}`;
}

/**
 * Проверка, исключен ли компонент
 */
export function isComponentExcluded(componentPath) {
    const excludeConfig = loadExcludeList();
    const componentName = getComponentName(componentPath);

    // Проверяем по паттернам имен компонентов
    for (const pattern of excludeConfig.patterns) {
        const regex = new RegExp(pattern.replace(/\*/g, '.*'));
        if (regex.test(componentName)) {
            return true;
        }
    }

    // Проверяем по путям
    for (const excludePath of excludeConfig.paths || []) {
        const pathPattern = excludePath.replace(/\*/g, '.*');
        const regex = new RegExp(pathPattern);
        if (regex.test(componentPath)) {
            return true;
        }
    }

    return false;
}

/**
 * Определение типа компонента для вывода
 */
export function getComponentType(scssPath) {
    return scssPath.includes('/bitrix/')
        ? '(кастомный шаблон штатного компонента)'
        : '(собственный компонент)';
}
