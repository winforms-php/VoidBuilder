# VoidBuilder

**VoidBuilder** - консольная **PHP** утилита для сборки приложений, созданных на [**VoidFramework**](https://github.com/winforms-php/VoidFramework)

## Установка

1. Скачайте и распакуйте репозиторий
2. Скопируйте [**Qero**](https://github.com/KRypt0nn/Qero) в папку сборщика
3. Установите **Qero**-зависимости
> php Qero.phar install

## Использование

> php build.php [аргументы]

Список доступных аргументов:

| Аргумент | Алиас | Описание |
|-|-|-|
--app-dir | -d | Путь до папки **app**, в которой содержится само **VoidFramework**-приложение. Обязательно
--output-dir | -o | Путь до директории сохранения собранного проекта
--icon-path | -i | Путь до иконки собираемого проекта
--join | -j | Файл или папка, с которой необходимо склеить собранный проект *(если не установлен параметр --compress)*
--compress | -c | Сжать ли весь проект до исполняемого файла и **PHP** библиотеки

## Примеры

Обычная сборка:
> php build.php -d "C:\Users\\%username%\Desktop\Test App\app" -o C:\Users\\%username%\Desktop

Сборка со склейкой папки **ext**:
> php build.php -d "C:\Users\\%username%\Desktop\Test App\app" -o C:\Users\\%username%\Desktop -j ext

Сборка со склейкой всех библиотек и папки **ext**:
> php build.php -d "C:\Users\\%username%\Desktop\Test App\app" -o C:\Users\\%username%\Desktop -j *.dll -j ext

Сборка со сжатием всего проекта:
> php build.php -d "C:\Users\\%username%\Desktop\Test App\app" -o C:\Users\\%username%\Desktop --compress

Автор: [Подвирный Никита](https://vk.com/technomindlp). Специально для [Enfesto Studio Group](https://vk.com/hphp_convertation)