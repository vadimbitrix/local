import gulp from 'gulp';
import gulpSassPlugin from 'gulp-sass';
import * as sass from 'sass';
import autoprefixer from 'gulp-autoprefixer';
import cleanCSS from 'gulp-clean-css';
import rename from 'gulp-rename';
import path from 'path';
import config from '../config.js';

const gulpSass = gulpSassPlugin(sass);

/**
 * Сборка глобального SCSS файла
 */
export function buildGlobalScss(scssFile, cssFile, componentName) {
    const minCssFile = cssFile.replace('.css', '.min.css');

    const cssFileName = path.basename(cssFile); // template_style.css
    const cssBaseName = path.basename(cssFile, '.css'); // template_style

    // Основной pipeline для beautified CSS
    const mainBuild = gulp.src(scssFile)
        .pipe(gulpSass({
            includePaths: ['./scss/'] // Поддержка @use и @import
        }).on('error', gulpSass.logError))
        .pipe(autoprefixer(config.autoprefixer))
        .pipe(cleanCSS(config.cleanCss.beautify))
        .pipe(rename(cssFileName))
        .pipe(gulp.dest('./'));

    // Pipeline для минифицированного CSS
    let minBuild = Promise.resolve();

    if (config.minification.enabled) {
        minBuild = gulp.src(scssFile)
            .pipe(gulpSass({
                includePaths: ['./scss/']
            }).on('error', gulpSass.logError))
            .pipe(autoprefixer(config.autoprefixer))
            .pipe(cleanCSS(config.cleanCss.minified))
            .pipe(rename({
                basename: cssBaseName,
                suffix: config.minification.suffix,
                extname: '.css'
            }))
            .pipe(gulp.dest('./'));
    }

    return Promise.all([
        new Promise(resolve => mainBuild.on('end', resolve)),
        config.minification.enabled ? new Promise(resolve => minBuild.on('end', resolve)) : Promise.resolve()
    ]).then(() => {
        console.log(`✅ Собран глобальный файл: ${componentName}`.green);
        if (config.minification.enabled) {
            console.log(`   📦 Создан минифицированный файл: ${path.basename(minCssFile)}`.gray);
        }
    });
}

/**
 * Сборка всех глобальных файлов
 */
export async function buildAllGlobalFiles() {
    console.log('🌐 Сборка глобальных SCSS файлов...'.blue);

    const builds = [];

    // Собираем template.scss → template_styles.css
    if (config.globalFiles.template) {
        builds.push(
            buildGlobalScss(
                config.globalFiles.template.scss,
                config.globalFiles.template.css,
                'Template Styles'
            )
        );
    }

    // Собираем styles.scss → styles.css
    if (config.globalFiles.styles) {
        builds.push(
            buildGlobalScss(
                config.globalFiles.styles.scss,
                config.globalFiles.styles.css,
                'Global Styles'
            )
        );
    }

    await Promise.all(builds);
    console.log('✅ Все глобальные файлы собраны'.green);
}
