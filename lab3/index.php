<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №3</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 50%;
            margin-bottom: 30px;
            background: #fff;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .loops {
            background: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            max-width: 600px;
        }
    </style>
</head>
<body>

<h2>Задание 1: Таблица с расписанием (Условные конструкции)</h2>

<?php
$dayOfWeek = (int)date('N');

$daysNames = [
    1 => 'Понедельник',
    2 => 'Вторник',
    3 => 'Среда',
    4 => 'Четверг',
    5 => 'Пятница',
    6 => 'Суббота',
    7 => 'Воскресенье'
];

echo "<p><strong>Текущий день недели:</strong> " . $daysNames[$dayOfWeek] . " ($dayOfWeek)</p>";

if ($dayOfWeek === 1 || $dayOfWeek === 3 || $dayOfWeek === 5) {
    $johnSchedule = "8:00-12:00";
} else {
    $johnSchedule = "Нерабочий день";
}

if ($dayOfWeek === 2 || $dayOfWeek === 4 || $dayOfWeek === 6) {
    $janeSchedule = "12:00-16:00";
} else {
    $janeSchedule = "Нерабочий день";
}
?>

<table>
    <thead>
        <tr>
            <th>№</th>
            <th>Фамилия Имя</th>
            <th>График работы</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>John Styles</td>
            <td><?= $johnSchedule ?></td>
        </tr>
        <tr>
            <td>2</td>
            <td>Jane Doe</td>
            <td><?= $janeSchedule ?></td>
        </tr>
    </tbody>
</table>

<hr />

<h2>Задание 2: Циклы</h2>
<div class="loops">
    <h3>1. Цикл for</h3>
    <?php
    $a = 0;
    $b = 0;

    for ($i = 0; $i <= 5; $i++) {
        $a += 10;
        $b += 5;
        echo "Шаг $i: a = $a, b = $b<br />";
    }

    echo "<strong>End of the loop: a = $a, b = $b</strong><br />";
    ?>

    <h3>2. Цикл while</h3>
    <?php
    $a = 0;
    $b = 0;
    $i = 0;

    while ($i <= 5) {
        $a += 10;
        $b += 5;
        echo "Шаг $i: a = $a, b = $b<br />";
        $i++;
    }

    echo "<strong>End of the loop: a = $a, b = $b</strong><br />";
    ?>

    <h3>3. Цикл do-while</h3>
    <?php
    $a = 0;
    $b = 0;
    $i = 0;

    do {
        $a += 10;
        $b += 5;
        echo "Шаг $i: a = $a, b = $b<br />";
        $i++;
    } while ($i <= 5);

    echo "<strong>End of the loop: a = $a, b = $b</strong><br />";
    ?>
</div>

</body>
</html>
