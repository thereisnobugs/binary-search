# binary-search

Binary Search Library for php

Реализация алгоритма бинарного поиска на PHP

Умеет работать с большими файлами без чтения файла целиком в память.

---

# Пример использования

1. Получаем файл при помощи 

> wget http://www.fms.gov.ru/upload/expired-passports/list_of_expired_passports.csv.bz2

2. Распаковываем 

> bzip2 -d list_of_expired_passports.csv.bz2

3. Сортируем

> sort list_of_expired_passports.csv > source.csv

4. Консольная утилита для поиска просроченного файла в БД недействительных паспортов ФМС

> touch fms.php

```php

include_once 'vendor/autoload.php';

if (count($argv) < 3) {
	// Вывод справки
	print "Используйте php5 test.php файл искомая строка\n";
	exit;
} else {
	// Берем атрибуты переданные с коммандной строки
	$filepath = $argv[1];
	$pattern = $argv[2];
}

// Создаем объект источник данных для поиска
$dataSource = new \BinarySearch\DataSource\FileData(filepath);

// Инициализируем класс бинарного поиска
$searcher = new \BinarySearch\BinarySearch($dataSource);

// Для отладки можем инжектировать объект логгер
//$searcher->injectLogger(new \BinarySearch\ConsoleLog());

// Производим поиск позиции в источники данных, в которой находится искомое значение
$position = $searcher->search($pattern);

if ( is_null($position) ) {
	print 'Не найдено'."\n";
	exit;
} 

print 'Найдено на позиции: '.$position."\n";

// Идем на указаную позицию и читаем найденое
$dataSource->moveTo($position);
print 'Значение: ['.$dataSource->getData().']'."\n";

```