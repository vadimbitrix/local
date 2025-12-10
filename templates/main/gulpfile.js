import gulp from 'gulp';
import colors from 'colors';

// Импорты модулей системы сборки
import { buildAllGlobalFiles } from './build-system/builders/global.js';
import { checkCssChanges, findComponentScss } from './build-system/tasks/check.js';
import { buildWithCheck, forceBuild, buildMinOnly, buildForWatch, createWatchSnapshot } from './build-system/tasks/build.js';
import {
    toggleMinification,
    cleanMinFiles,
    showExcludeList,
    initializeSystem,
    testSystem
} from './build-system/tasks/utils.js';

// Импорты для Vue (пока оставляем здесь, можно вынести отдельно)
import { findFiles } from './build-system/utils/files.js';
import { isComponentExcluded, getComponentName, getVueComponentName } from './build-system/utils/component.js';
import { buildMainVueTemplate, buildSubVueComponent } from './build-system/builders/vue.js';
import config from './build-system/config.js';

// =============================================================================
// SCSS ЗАДАЧИ
// =============================================================================

gulp.task('build', async function(done) {
    try {
        await buildWithCheck();
        done();
    } catch (error) {
        done(error);
    }
});

gulp.task('build-global', async function(done) {
    try {
        await buildAllGlobalFiles();
        done();
    } catch (error) {
        done(error);
    }
});

gulp.task('force-build', async function(done) {
    try {
        await forceBuild();
        done();
    } catch (error) {
        done(error);
    }
});

gulp.task('check', async function(done) {
    console.log('🔍 Проверка синхронизации SCSS и CSS файлов...'.blue);
    await checkCssChanges();
    done();
});

// =============================================================================
// VUE ЗАДАЧИ (упрощенные)
// =============================================================================

function findMainVueTemplates() {
    const vueFiles = findFiles(config.patterns.vueMain);
    return vueFiles.filter(vuePath => !isComponentExcluded(vuePath));
}

function findSubVueComponents() {
    const vueFiles = findFiles(config.patterns.vueSub);
    return vueFiles.filter(vuePath => !isComponentExcluded(vuePath));
}

gulp.task('build-vue', function(done) {
    console.log('🔨 Сборка Vue компонентов...'.blue);

    const mainTemplates = findMainVueTemplates();
    const subComponents = findSubVueComponents();
    const allVueFiles = [...mainTemplates, ...subComponents];

    if (allVueFiles.length === 0) {
        console.log('⚠️  Vue файлы не найдены'.yellow);
        return done();
    }

    const buildPromises = [
        ...mainTemplates.map(vuePath => {
            return new Promise((resolve) => {
                buildMainVueTemplate(vuePath).on('end', resolve);
            });
        }),
        ...subComponents.map(vuePath => {
            return new Promise((resolve) => {
                buildSubVueComponent(vuePath).on('end', resolve);
            });
        })
    ];

    Promise.all(buildPromises).then(() => {
        console.log('✅ Все Vue компоненты собраны'.green);
        done();
    });
});

// =============================================================================
// СЛУЖЕБНЫЕ ЗАДАЧИ
// =============================================================================

gulp.task('toggle-minification', function(done) {
    toggleMinification();
    done();
});

gulp.task('clean-min', function(done) {
    cleanMinFiles();
    done();
});

gulp.task('build-min-only', async function(done) {
    if (!config.minification.enabled) {
        console.log('⚠️  Минификация отключена. Включите её командой: npx gulp toggle-minification'.yellow);
        return done();
    }

    try {
        await buildMinOnly();
        done();
    } catch (error) {
        done(error);
    }
});

gulp.task('exclude:list', function(done) {
    showExcludeList();
    done();
});

gulp.task('init', function(done) {
    initializeSystem();
    done();
});

gulp.task('test', function(done) {
    testSystem();
    done();
});

// =============================================================================
// WATCH ЗАДАЧА
// =============================================================================
gulp.task('build-watch', async function(done) {
    try {
        await buildForWatch();
        done();
    } catch (error) {
        console.log('⚠️  Ошибка в watch режиме, продолжаем отслеживание...'.yellow);
        done(); // Не останавливаем watch
    }
});
gulp.task('watch', async function () {
    console.log('👀 Запуск режима отслеживания изменений...'.blue.bold);
    console.log('🔄 Файлы будут автоматически пересобираться при изменении'.gray);

    await createWatchSnapshot();
    console.log('📸 Снапшот создан. Теперь будем отслеживать внешние изменения'.green);

    const scssFiles = findComponentScss();
    const mainVueFiles = findMainVueTemplates();
    const subVueFiles = findSubVueComponents();
    const globalScssFiles = ['./scss/**/*.scss'];

    if (scssFiles.length > 0) {
        gulp.watch(scssFiles, gulp.series('build-watch'));
        console.log(`📂 Отслеживаются SCSS компоненты: ${scssFiles.length}`.gray);
    }


    if (globalScssFiles.length > 0) {
        gulp.watch(globalScssFiles, gulp.series('build-global'));
        console.log(`🌐 Отслеживаются глобальные SCSS файлы`.gray);
    }

    if (mainVueFiles.length > 0 || subVueFiles.length > 0) {
        gulp.watch([...mainVueFiles, ...subVueFiles], gulp.series('build-vue'));
        console.log(`📂 Отслеживаются Vue файлы: ${mainVueFiles.length + subVueFiles.length}`.gray);
    }

    console.log('\n✨ Умный watch режим активен:'.green);
    console.log('   📸 При запуске создается снапшот CSS файлов'.gray);
    console.log('   🔍 Отслеживаются внешние изменения (админка, другие разработчики)'.gray);
    console.log('   💾 Внешние изменения автоматически backup\'ятся'.gray);
    console.log('   🔄 Watch продолжает работать с уведомлениями'.gray);

    if (scssFiles.length === 0 && mainVueFiles.length === 0 && subVueFiles.length === 0) {
        console.log('⚠️  Нет файлов для отслеживания'.yellow);
        console.log('💡 Создайте SCSS или Vue файлы в папках компонентов'.gray);
    } else {
        console.log('\n✨ Режим отслеживания активен. Нажмите Ctrl+C для остановки'.green);
    }
});
gulp.task('snapshot', async function(done) {
    await createWatchSnapshot();
    console.log('📸 Снапшот пересоздан'.green);
    done();
});
gulp.task('build-all', gulp.series('build-global', 'build', 'build-vue'));

// =============================================================================
// ЗАДАЧА ПО УМОЛЧАНИЮ
// =============================================================================

gulp.task('default', gulp.series('init'));
