# Bitrix SCSS + Vue Builder

Система сборки для 1С-Битрикс с отслеживанием изменений CSS и поддержкой Vue.js

## Установка

```bash
npm install
```

## Команды

| Команда | Описание |
|---------|----------|
| `make test` | Проверка системы |
| `make build` | Полная сборка |
| `make check` | Проверка изменений CSS |
| `make watch` | Отслеживание изменений |
| `make vue` | Сборка Vue компонентов |
| `make force` | Принудительная сборка |
| `make exclude` | Список исключений |

## Структура
```
components/
└── news/
    └── list/
├── style.scss # Исходные стили
├── style.css # Собранные стили
└── component.vue # Vue компонент
```

## Workflow

1. Создаем SCSS: `components/news/list/style.scss`
2. Собираем: `make build`
3. Отслеживаем: `make watch`
4. Проверяем изменения: `make check`
