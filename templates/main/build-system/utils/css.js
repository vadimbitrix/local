import * as sass from 'sass';
import postcss from 'postcss';
import autoprefixerLib from 'autoprefixer';
import CleanCSS from 'clean-css';
import * as diff from 'diff';
import config from '../config.js';

/**
 * Нормализация CSS для корректного сравнения
 */
export function normalizeCss(css) {
    return css
        // убираем комментарии
        .replace(/\/\*[\s\S]*?\*\//g, '')
        // убираем вендор-префиксы
        .replace(/-(webkit|moz|ms|o)-[^:;]+[^;]*;?\s*/g, '')
        // убираем точки с запятой и лишние пробелы
        .replace(/[;]\s*/g, '')
        .replace(/\s+/g, ' ')
        .replace(/\s*{\s*/g, '{')
        .replace(/\s*}\s*/g, '}')
        .trim();
}

/**
 * Показ различий между файлами
 */
export function showDifferences(scssCompiled, currentCss, componentName) {
    const differences = diff.diffLines(scssCompiled, currentCss);

    console.log(`\n📋 Различия в ${componentName}:`.yellow);

    differences.forEach(part => {
        if (part.added) {
            console.log(`+ ${part.value}`.green);
        } else if (part.removed) {
            console.log(`- ${part.value}`.red);
        }
    });
}

/**
 * Компиляция SCSS с полным pipeline (Sass + Autoprefixer + Clean-CSS)
 */
export async function compileScssTempContent(scssPath) {
    try {
        // 1. Sass
        const sassResult = sass.compile(scssPath, { style: 'expanded' });
        let css = sassResult.css.toString();

        // 2. Autoprefixer
        const prefixed = await postcss([
            autoprefixerLib(config.autoprefixer)
        ]).process(css, { from: undefined });

        // 3. Clean-CSS (beautify формат для сравнения)
        const cleanCssInstance = new CleanCSS(config.cleanCss.beautify);
        const cleanResult = cleanCssInstance.minify(prefixed.css);

        if (cleanResult.errors && cleanResult.errors.length > 0) {
            console.log(`⚠️ Ошибки Clean-CSS для ${scssPath}:`, cleanResult.errors);
        }

        return cleanResult.styles;

    } catch (error) {
        console.log(`❌ Ошибка компиляции SCSS ${scssPath}: ${error.message}`.red);
        return '';
    }
}
