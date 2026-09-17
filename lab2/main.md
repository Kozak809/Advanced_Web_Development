# Шаг 1-2 Установка PHP
Уже установлен так что пропускаем
![alt text](image.png)
# Шаг 3
Создайте файл [index.php](index.php) и запускаю
```cmd
PS C:\Users\Kozak\Desktop\labs\year2\Advanced_Web_Development> php lab2/index.php
Привет, мир!
```
# Шаг 4
Добавте в index.php 
```php
<?php
echo "Hello, World with echo!";
print "Hello, World with print!";
```
И запустите
```cmd
PS C:\Users\Kozak\Desktop\labs\year2\Advanced_Web_Development> php lab2/index.php
Привет, мир!Hello, World with echo!Hello, World with print!
```

# Шаг 5

Создайте две переменные:

    Целочисленную переменную $days со значением 288.
    Строковую переменную $message с текстом: Все возвращаются на работу!.

Выведите значения переменных на экран несколькими способами:

    С использованием конкатенации. Конкатенация - это объединение строк, в PHP используется оператор .:
    С использованием двойных кавычек.

Используйте переход на новую строку в выводе используя тэг <br />.

Запускаю через

```cmd
php -S localhost:8000 -t lab2
```

И переходу на `localhost:8000` в браузере.

Результат:

![alt text](image-1.png)
