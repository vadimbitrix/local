.PHONY: help install test build check watch vue force exclude clean

# Переменные
NPM := npm
GULP := npx gulp

# Цвета для вывода
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m # Без цвета

help: ## Показать справку
	@echo "$(GREEN)Bitrix SCSS + Vue Builder$(NC)"
	@echo "Доступные команды:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(YELLOW)%-12s$(NC) %s\n", $$1, $$2}'

install: ## Установить зависимости
	@echo "$(GREEN)Установка зависимостей...$(NC)"
	$(NPM) install

test: ## Проверить работоспособность системы
	@echo "$(GREEN)Проверка системы...$(NC)"
	$(NPM) test

build: ## Полная сборка всех компонентов
	@echo "$(GREEN)Сборка компонентов...$(NC)"
	$(NPM) run build

check: ## Проверить изменения CSS
	@echo "$(GREEN)Проверка изменений...$(NC)"
	$(NPM) run check

watch: ## Отслеживание изменений (для разработки)
	@echo "$(GREEN)Запуск отслеживания изменений...$(NC)"
	$(NPM) run watch

vue: ## Сборка Vue компонентов
	@echo "$(GREEN)Сборка Vue компонентов...$(NC)"
	$(NPM) run build-vue

vue-main: ## Сборка только основных Vue шаблонов
	@echo "$(GREEN)Сборка основных Vue шаблонов...$(NC)"
	$(GULP) build-vue-main

vue-sub: ## Сборка только составляющих Vue компонентов
	@echo "$(GREEN)Сборка составляющих Vue компонентов...$(NC)"
	$(GULP) build-vue-sub

vue-all: vue ## Алиас для полной сборки Vue

min-toggle: ## Включить/выключить создание .min.css файлов
	@echo "$(YELLOW)Переключение минификации...$(NC)"
	$(GULP) toggle-minification

min-only: ## Собрать только .min.css файлы
	@echo "$(GREEN)Сборка минифицированных файлов...$(NC)"
	$(GULP) build-min-only

clean-min: ## Удалить все .min.css файлы
	@echo "$(YELLOW)Очистка минифицированных файлов...$(NC)"
	$(GULP) clean-min

force: ## Принудительная сборка (игнорирует проверки)
	@echo "$(YELLOW)Принудительная сборка...$(NC)"
	$(NPM) run force-build

exclude: ## Показать список исключенных компонентов
	@echo "$(GREEN)Список исключений:$(NC)"
	$(GULP) exclude:list

# Служебные команды
clean: ## Очистить сборку
	@echo "$(YELLOW)Очистка сборки...$(NC)"
	rm -rf .bitrix-build/
	rm -rf node_modules/
	rm -f package-lock.json

init: ## Инициализация проекта
	@echo "$(GREEN)Инициализация проекта...$(NC)"
	$(NPM) init -y
	$(MAKE) install
	$(NPM) test

dev: ## Режим разработки (build + watch)
	@echo "$(GREEN)Запуск режима разработки...$(NC)"
	$(MAKE) build && $(MAKE) watch

# Алиасы для удобства
scss: build ## Алиас для build

css: check ## Алиас для check

w: watch ## Алиас для watch

b: build ## Алиас для build

c: check ## Алиас для check

# По умолчанию показываем справку
.DEFAULT_GOAL := help
