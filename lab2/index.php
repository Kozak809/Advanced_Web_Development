<?php

echo "Привет, мир!";
echo "Hello, World with echo!";
print "Hello, World with print!";

$days = 228;
$message = "Все возвращаются на работу!";
// Через конкотенацию

echo "Через " . $days . " дней " . $message . "<br />";
// С помощью двойных кавычек
echo "Через $days дней $message" . "<br />";

// С помощью фигурных скобок
echo "Через {$days} дней {$message}<br />";
