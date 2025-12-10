import gulp from 'gulp';
import gulpSassPlugin from 'gulp-sass';
import * as sass from 'sass';
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import rename from 'gulp-rename';
import path from 'path';
import config from '../config.js';
import { getFileHash, loadHashes, saveHashes } from '../utils/files.js';
import { getComponentName } from '../utils/component.js';

// Инициализация Sass
const gulpSass = gulpSassPlugin(sass);

/**
 * Сборка отдельного SCSS компонента
 */
export function buildScssComponent(scssPath) {
    const cssPath = scssPath.replace('.scss', '.css');
    const cssMinPath = scssPath.replace('.scss', '.min.css');
    const componentName = getComponentName(scssPath);

    // Основной pipeline для beautified CSS
    const mainBuild = gulp.src(scssPath)
        .pipe(gulpSass().on('error', gulpSass.logError))
        .pipe(autoprefixer(config.autoprefixer))
        .pipe(cleanCSS(config.cleanCss.beautify))
        .pipe(gulp.dest(path.dirname(cssPath)));

    // Pipeline для минифицированного CSS (только если включено)
    let minBuild = Promise.resolve();

    if (config.minification.enabled) {
        minBuild = gulp.src(scssPath)
            .pipe(gulpSass().on('error', gulpSass.logError))
            .pipe(autoprefixer(config.autoprefixer))
            .pipe(cleanCSS(config.cleanCss.minified))
            .pipe(rename({
                suffix: config.minification.suffix
            }))
            .pipe(gulp.dest(path.dirname(cssPath)));
    }

    // Возвращаем Promise, который ждет завершения обеих сборок
    return Promise.all([
        new Promise(resolve => mainBuild.on('end', resolve)),
        config.minification.enabled ? new Promise(resolve => minBuild.on('end', resolve)) : Promise.resolve()
    ]).then(() => {
        // Обновляем хеши для отслеживания изменений
        const hashes = loadHashes();
        const cssHash = getFileHash(cssPath);
        const cssMinHash = config.minification.enabled ? getFileHash(cssMinPath) : null;

        hashes[scssPath] = {
            css: cssHash,
            cssMin: cssMinHash,
            timestamp: Date.now()
        };

        saveHashes(hashes);

        console.log(`✅ Собран компонент: ${componentName}`.green);
        if (config.minification.enabled) {
            console.log(`   📦 Создан минифицированный файл: ${path.basename(cssMinPath)}`.gray);
        }
    });
}

/**
 * Сборка только минифицированных файлов
 */
export function buildMinifiedOnly(scssPath) {
    const cssMinPath = scssPath.replace('.scss', '.min.css');
    const componentName = getComponentName(scssPath);

    return new Promise((resolve) => {
        gulp.src(scssPath)
            .pipe(gulpSass().on('error', gulpSass.logError))
            .pipe(autoprefixer(config.autoprefixer))
            .pipe(cleanCSS(config.cleanCss.minified))
            .pipe(rename({
                suffix: config.minification.suffix
            }))
            .pipe(gulp.dest(path.dirname(cssMinPath)))
            .on('end', () => {
                console.log(`📦 Минифицирован: ${componentName}`.green);
                resolve();
            });
    });
}
