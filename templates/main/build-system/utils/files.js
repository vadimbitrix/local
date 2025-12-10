import fs from 'fs';
import crypto from 'crypto';
import { glob } from 'glob';
import config from '../config.js';

/**
 * Создание служебной папки сборки
 */
export function ensureBuildDir() {
    if (!fs.existsSync(config.buildDir)) {
        fs.mkdirSync(config.buildDir, { recursive: true });
        console.log('📁 Создана служебная папка .bitrix-build'.blue);
    }
}

/**
 * Получение MD5 хеша файла
 */
export function getFileHash(filePath) {
    if (!fs.existsSync(filePath)) return null;
    const content = fs.readFileSync(filePath, 'utf8');
    return crypto.createHash('md5').update(content).digest('hex');
}

/**
 * Загрузка сохраненных хешей
 */
export function loadHashes() {
    ensureBuildDir();
    if (fs.existsSync(config.hashFile)) {
        return JSON.parse(fs.readFileSync(config.hashFile, 'utf8'));
    }
    return {};
}

/**
 * Сохранение хешей
 */
export function saveHashes(hashes) {
    ensureBuildDir();
    fs.writeFileSync(config.hashFile, JSON.stringify(hashes, null, 2));
}

/**
 * Получение хешей компонента (с поддержкой старого формата)
 */
export function getComponentHashes(scssPath) {
    const hashes = loadHashes();
    const componentHashes = hashes[scssPath];

    // Если старый формат (просто строка) - конвертируем
    if (typeof componentHashes === 'string') {
        return {
            css: componentHashes,
            cssMin: null,
            timestamp: 0
        };
    }

    // Новый формат (объект)
    return componentHashes || {
        css: null,
        cssMin: null,
        timestamp: 0
    };
}

/**
 * Поиск файлов по паттерну с фильтрацией исключений
 */
export function findFiles(pattern, filterFn = null) {
    const files = glob.sync(config.components + pattern);

    if (filterFn) {
        return files.filter(filterFn);
    }

    return files;
}

/**
 * Создание файла исключений
 */
export function createExcludeFile() {
    if (!fs.existsSync(config.excludeFile)) {
        const excludeConfig = {
            patterns: config.defaultExcludes.patterns,
            paths: config.defaultExcludes.paths,
            note: "Система работает только с файлами в templates/components/ - кастомными шаблонами и собственными компонентами"
        };

        fs.writeFileSync(config.excludeFile, JSON.stringify(excludeConfig, null, 2));
        console.log('📝 Создан файл конфигурации исключений:', config.excludeFile);
    }
}

/**
 * Загрузка списка исключений
 */
export function loadExcludeList() {
    ensureBuildDir();
    createExcludeFile();

    if (fs.existsSync(config.excludeFile)) {
        return JSON.parse(fs.readFileSync(config.excludeFile, 'utf8'));
    }

    return config.defaultExcludes;
}
