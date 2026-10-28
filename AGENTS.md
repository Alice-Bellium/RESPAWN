# Согласованный кодстайл

Правила согласованы 29.10.2026. Они имеют приоритет над стандартными пресетами форматтеров. Следовать им при новых изменениях и сохранять осмысленное существующее форматирование.

## PHP

- Отступ 4 пробела, без табуляции.
- Пробелы вокруг конкатенации: `'RSP-' . $number`.
- Нет пробела после отрицания и приведения типа: `!empty($value)`, `!$product`, `(int)$value`, `(float)$value`.
- Стрелочные функции: `fn(Builder $query) => ...`. Обычные анонимные функции: `function () { ... }`.
- Массивы бизнес-данных, ответы API, поля записей, `$fillable` и `casts()`: один элемент на строку, завершающая запятая. Короткие списки правил валидации и идентификаторов можно оставлять в строке.
- Каждая ветка `match` на отдельной строке.
- Длинные цепочки: каждый следующий метод на новой строке. Запросы начинать с `Model::query()`; короткие связи моделей можно оставить в строке.
- Отделять пустой строкой подготовку данных, проверки, запросы, обработку, сохранение и возврат. Сохранять существующие смысловые разделения.
- Длинные условия и аргументы переносить. Ориентир 110 символов, без искусственного разрыва строковых значений. Не соединять несколько самостоятельных действий в одну строку.
- Фигурные скобки у управляющих конструкций обязательны. У классов и методов открывающая скобка на следующей строке, у условий и циклов на той же.
- Указывать известные типы параметров и результатов. Сохранять PHPDoc моделей и каждого их метода; описывать сложные структуры массивов в PHPDoc.
- Классы PascalCase, методы и переменные camelCase, константы UPPER_SNAKE_CASE. Имена должны раскрывать назначение.

## Vue, JavaScript и файлы

- Сохранять осмысленные переносы и пустые строки в скриптах.
- Если атрибутов тега больше двух, каждый на отдельной строке. Уже разбитые теги не склеивать. Текущая настройка Prettier переносит и два атрибута.
- JavaScript: одинарные кавычки, точки с запятой, 4 пробела, ориентир 110 символов.
- Сохранять компактные фигурные скобки импортов и объектов, как в пользовательской правке Modal.vue: `import {ref} from 'vue'`, `{title: String}`.
- CSS: каждое свойство на отдельной строке.
- UTF-8, LF, завершающая новая строка, без конечных пробелов. Сохранять исключения Markdown и YAML из .editorconfig.
- Не форматировать vendor, node_modules, сборку, кэш и сгенерированные lock-файлы.
- PHP форматируется Pint с проектным pint.json, фронтенд Prettier с .prettierrc.json. Не применять стандартный пресет поверх согласованных правил. Смысловое разбиение массивов, запросов и блоков проверять дополнительно при ревью.


## Имена и данные в примерах

- Не использовать имя и другие персональные данные пользователя в коде, тестах, примерах, демоданных и документации.
- Если нужен женский персонаж, использовать Mary; если мужской, Marty. Для проверки полного имени допустимы Mary Example и Marty Example.
- В фабриках не генерировать случайные имена и адреса на основе имён. Использовать Mary/Marty и вымышленные адреса на домене example.test с уникальным техническим суффиксом.

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
