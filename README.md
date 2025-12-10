# local
my working, local folder for developing in bitrix

### Social ###

* [https://vadim24.ru](https://vadim24.ru)

### Extensions ###

Подключение Bitrix\Main\UI\Extension::load("vv.test");

### Command Console BitrixCLI ###
* bitrix build (Сборка)
* bitrix build -e=main.core,ui.buttons,landing.main (Сборка только указанных экстеншнов)
* bitrix build --modules main,ui,landing (Сборка только указанных модулей)
* bitrix build --test (Сборка с запуском тестов)
* bitrix build --watch (Запуск с отслеживанием изменений)
* bitrix build -w (Сокращенный вариант --watch)
----
* bitrix test (Запуск тестов) 
* bitrix test --watch (Запуск тестов с отслеживанием изменений)
* bitrix test -w (Сокращенный вариант --watch)
----
* bitrix create (Создание расширения) 
