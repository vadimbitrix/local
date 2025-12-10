import { fileURLToPath } from 'url';
import path from 'path';

// Эмуляция __dirname для ES modules
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export const config = {
    // Пути
    components: './components/**/',
    scss: './scss/',
    buildDir: './.bitrix-build',

    // Файлы конфигурации
    hashFile: './.bitrix-build/hashes.json',
    excludeFile: './.bitrix-build/exclude.json',

    // Паттерны файлов
    patterns: {
        scss: '**/style.scss',
        css: '**/style.css',
        cssMin: '**/style.min.css',
        globalScss: '*.scss',               // template.scss, styles.scss
        globalCss: '*.css',                 // template_style.css, styles.css
        vueMain: '**/template.vue',
        vueSub: '**/vue-component/*.vue',
        vueOld: '**/component.vue',
        js: '**/script.js'
    },

    globalFiles: {
        template: {
            scss: './scss/template.scss',
            css: './template_styles.css'
        },
        styles: {
            scss: './scss/styles.scss',
            css: './styles.css'
        }
    },

    // Настройки минификации
    minification: {
        enabled: true,
        suffix: '.min'
    },

    // Настройки автопрефиксера
    autoprefixer: {
        cascade: false,
        overrideBrowserslist: ['> 1%', 'last 2 versions', 'IE 11']
    },

    // Настройки Clean-CSS
    cleanCss: {
        beautify: {
            level: 1,
            format: 'beautify'
        },
        minified: {
            level: 2,
            compatibility: 'ie8'
        }
    },

    // Список исключений по умолчанию
    defaultExcludes: {
        patterns: [
            // 'old.*', 'test.*', 'deprecated.*'
        ],
        paths: [
            // 'components/old/**'
        ]
    }
};

export default config;
