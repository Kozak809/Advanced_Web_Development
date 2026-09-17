<?php

echo "Привет, мир!";
echo "Hello, World with echo!";
print "Hello, World with print!";

$days = 228;
$message = "Все возвращаются на работу!";

echo "Через " . $days . " дней " . $message . "<br />";

echo "Через $days дней $message" . "<br />";

echo "Через {$days} дней {$message}<br />";
