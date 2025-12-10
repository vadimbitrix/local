import gulp from 'gulp';
import vueComponent from 'gulp-vue-single-file-component';
import rename from 'gulp-rename';
import babel from 'gulp-babel';
import terser from 'gulp-terser';
import path from 'path';
import through2 from 'through2';
import { camelCase } from 'lodash-es';
import { getComponentName, getVueComponentName } from '../utils/component.js';

/**
 * Сборка основного Vue шаблона (создает script.js и script.min.js)
 */
export function buildMainVueTemplate(vuePath) {
    const dirParts = path.dirname(vuePath).split('/');
    const folderName = dirParts[dirParts.length - 2];
    const globalVar = camelCase(folderName) + 'Tpl';
    const componentName = getComponentName(vuePath);

    return gulp.src(vuePath)
        .pipe(vueComponent({
            debug: false,
            loadCssMethod: false
        }))
        .pipe(babel({
            presets: [
                ['@babel/preset-env', {
                    targets: '> 1%, last 2 versions, IE 11',
                    modules: false
                }]
            ],
            plugins: [
                ['@babel/plugin-transform-modules-umd', {
                    globals: {
                        'vue': 'Vue'
                    },
                    exactGlobals: true
                }]
            ]
        }))
        .pipe(
            through2.obj((file, _, cb) => {
                let code = file.contents.toString();
                code = code.replace(/global\.template\s*=\s*mod\.exports/g, `global.${globalVar} = mod.exports`);
                file.contents = Buffer.from(code);
                cb(null, file);
            })
        )
        .pipe(rename('script.js'))
        .pipe(gulp.dest(path.dirname(vuePath)))
        .pipe(terser({
            compress: {
                drop_console: false,
                drop_debugger: false
            },
            mangle: true,
            format: {
                comments: false
            }
        }))
        .pipe(rename('script.min.js'))
        .pipe(gulp.dest(path.dirname(vuePath)))
        .on('end', () => {
            console.log(`✅ Собран Vue шаблон: ${componentName} (обычная и min версии)`.green);
        });
}

/**
 * Сборка Vue компонента (сразу минифицированная версия)
 */
export function buildSubVueComponent(vuePath) {
    const fileName = path.basename(vuePath, '.vue');
    const componentName = getVueComponentName(vuePath);

    return gulp.src(vuePath)
        .pipe(vueComponent({
            debug: false,
            loadCssMethod: false
        }))
        .pipe(babel({
            presets: [
                ['@babel/preset-env', {
                    targets: '> 1%, last 2 versions, IE 11',
                    modules: 'umd'
                }]
            ],
            plugins: [
                '@babel/plugin-syntax-dynamic-import'
            ]
        }))
        .pipe(terser({
            compress: {
                drop_console: true,
                drop_debugger: true
            },
            mangle: true,
            format: {
                comments: false
            }
        }))
        .pipe(rename(`${fileName}.js`))
        .pipe(gulp.dest(path.dirname(vuePath)))
        .on('end', () => {
            console.log(`✅ Собран Vue компонент: ${componentName} (минифицированная версия)`.green);
        });
}
