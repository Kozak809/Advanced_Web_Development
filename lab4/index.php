<?php

declare(strict_types=1);

/**
 * Лабораторная работа №4: Массивы и Функции
 * Система управления банковскими транзакциями и Галерея изображений
 */

// ==========================================
// ЗАДАНИЕ 1.2: Создание массива транзакций
// ==========================================

/**
 * @var array<int, array{id: int, date: string, amount: float, description: string, merchant: string}> $transactions
 */
$transactions = [
    [
        "id" => 1,
        "date" => "2024-01-15",
        "amount" => 120.50,
        "description" => "Payment for groceries at SuperMart",
        "merchant" => "SuperMart",
    ],
    [
        "id" => 2,
        "date" => "2024-02-20",
        "amount" => 75.00,
        "description" => "Dinner with friends",
        "merchant" => "Local Restaurant",
    ],
    [
        "id" => 3,
        "date" => "2024-03-10",
        "amount" => 350.00,
        "description" => "Electronics store purchase",
        "merchant" => "TechWorld",
    ],
    [
        "id" => 4,
        "date" => "2024-04-05",
        "amount" => 24.99,
        "description" => "Monthly streaming subscription",
        "merchant" => "StreamNow",
    ],
];

// ==========================================
// ЗАДАНИЕ 1.4: Реализация функций с PHPDoc
// ==========================================

/**
 * Вычисляет общую сумму всех транзакций.
 *
 * @param array<int, array{amount: float, ...}> $transList Список транзакций
 * @return float Общая сумма транзакций
 */
function calculateTotalAmount(array $transList): float
{
    $total = 0.0;
    foreach ($transList as $item) {
        $total += $item['amount'];
    }
    return $total;
}

/**
 * Ищет транзакции по части описания (регистронезависимо).
 *
 * @param string $descriptionPart Подстрока для поиска
 * @return array<int, array{id: int, date: string, amount: float, description: string, merchant: string}> Найденные транзакции
 */
function findTransactionByDescription(string $descriptionPart): array
{
    global $transactions;
    $result = [];
    foreach ($transactions as $item) {
        if (stripos($item['description'], $descriptionPart) !== false) {
            $result[] = $item;
        }
    }
    return $result;
}

/**
 * Ищет транзакцию по уникальному идентификатору с помощью foreach.
 *
 * @param int $id Идентификатор транзакции
 * @return array{id: int, date: string, amount: float, description: string, merchant: string}|null Найденная транзакция или null
 */
function findTransactionById(int $id): ?array
{
    global $transactions;
    foreach ($transactions as $item) {
        if ($item['id'] === $id) {
            return $item;
        }
    }
    return null;
}

/**
 * Ищет транзакцию по идентификатору с помощью функции array_filter (на высшую оценку).
 *
 * @param int $id Идентификатор транзакции
 * @return array{id: int, date: string, amount: float, description: string, merchant: string}|null Найденная транзакция или null
 */
function findTransactionByIdFilter(int $id): ?array
{
    global $transactions;
    $filtered = array_filter($transactions, fn(array $item): bool => $item['id'] === $id);
    return !empty($filtered) ? reset($filtered) : null;
}

/**
 * Вычисляет количество дней между датой транзакции и текущим днем.
 *
 * @param string $date Дата транзакции в формате YYYY-MM-DD
 * @return int Количество прошедших дней
 */
function daysSinceTransaction(string $date): int
{
    $transDate = new DateTime($date);
    $currentDate = new DateTime();
    $interval = $currentDate->diff($transDate);
    return (int)$interval->format('%a');
}

/**
 * Добавляет новую транзакцию в общий массив $transactions.
 *
 * @param int $id Уникальный идентификатор транзакции
 * @param string $date Дата транзакции (YYYY-MM-DD)
 * @param float $amount Сумма транзакции
 * @param string $description Описание платежа
 * @param string $merchant Получатель платежа
 * @return void
 */
function addTransaction(int $id, string $date, float $amount, string $description, string $merchant): void
{
    global $transactions;
    $transactions[] = [
        "id" => $id,
        "date" => $date,
        "amount" => $amount,
        "description" => $description,
        "merchant" => $merchant,
    ];
}

// Добавим новую транзакцию через функцию
addTransaction(5, "2024-05-18", 89.20, "Books and stationery", "BookCity");

// ==========================================
// ЗАДАНИЕ 1.5: Сортировка транзакций
// ==========================================

// Копия для сортировки по дате (по возрастанию)
$transactionsByDate = $transactions;
usort($transactionsByDate, function (array $a, array $b): int {
    return strtotime($a['date']) <=> strtotime($b['date']);
});

// Копия для сортировки по сумме (по убыванию)
$transactionsByAmount = $transactions;
usort($transactionsByAmount, function (array $a, array $b): int {
    return $b['amount'] <=> $a['amount'];
});

// Демонстрация поиска
$searchSampleDescription = "Dinner";
$foundByDesc = findTransactionByDescription($searchSampleDescription);
$foundByIdForeach = findTransactionById(3);
$foundByIdFilter = findTransactionByIdFilter(3);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Лабораторная работа №4 - Массивы и Функции</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333;
        }
        header {
            background: #2c3e50;
            color: #fff;
            padding: 20px 40px;
        }
        nav {
            background: #34495e;
            padding: 10px 40px;
        }
        nav a {
            color: #ecf0f1;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }
        nav a:hover {
            color: #1abc9c;
        }
        main {
            padding: 30px 40px;
        }
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            margin-top: 30px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 25px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #e1e8ed;
            padding: 12px 15px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .total-row {
            font-weight: bold;
            background-color: #e2e8f0;
        }
        .code-box {
            background: #fff;
            padding: 15px 20px;
            border-left: 4px solid #3498db;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .gallery-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }
        .gallery-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        .gallery-item img {
            width: 100%;
            height: 130px;
            object-fit: cover;
            display: block;
        }
        .gallery-item p {
            margin: 8px 0;
            font-size: 13px;
            color: #555;
        }
        footer {
            background: #2c3e50;
            color: #bdc3c7;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<header>
    <h1>Лабораторная работа №4: Массивы и Функции</h1>
    <p>Тема: Банковские транзакции, сортировка, поиск и галерея изображений</p>
</header>

<nav>
    <a href="#transactions">Транзакции</a>
    <a href="#sort-date">Сортировка по дате</a>
    <a href="#sort-amount">Сортировка по сумме</a>
    <a href="#search">Поиск</a>
    <a href="#gallery">Галерея</a>
</nav>

<main>

    <!-- ЗАДАНИЕ 1.3: Вывод списка транзакций -->
    <h2 id="transactions">Задание 1.3: Основной список транзакций (с добавленной транзакцией #5)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Дней назад</th>
                <th>Сумма ($)</th>
                <th>Описание</th>
                <th>Организация (Merchant)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$t['id']) ?></td>
                    <td><?= htmlspecialchars($t['date']) ?></td>
                    <td><?= daysSinceTransaction($t['date']) ?> дн.</td>
                    <td><?= number_format($t['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['merchant']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="3">Итоговая сумма:</td>
                <td colspan="3">$<?= number_format(calculateTotalAmount($transactions), 2) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- ЗАДАНИЕ 1.5: Сортировка по дате -->
    <h2 id="sort-date">Задание 1.5: Транзакции, отсортированные по дате (usort)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Дней назад</th>
                <th>Сумма ($)</th>
                <th>Описание</th>
                <th>Организация</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactionsByDate as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= $t['date'] ?></td>
                    <td><?= daysSinceTransaction($t['date']) ?> дн.</td>
                    <td><?= number_format($t['amount'], 2) ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['merchant']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ЗАДАНИЕ 1.5: Сортировка по сумме -->
    <h2 id="sort-amount">Задание 1.5: Транзакции, отсортированные по сумме (по убыванию)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Сумма ($)</th>
                <th>Дата</th>
                <th>Описание</th>
                <th>Организация</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactionsByAmount as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><strong>$<?= number_format($t['amount'], 2) ?></strong></td>
                    <td><?= $t['date'] ?></td>
                    <td><?= htmlspecialchars($t['description']) ?></td>
                    <td><?= htmlspecialchars($t['merchant']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Демонстрация функций поиска -->
    <h2 id="search">Задание 1.4: Демонстрация работы функций поиска</h2>
    <div class="code-box">
        <p><strong>Поиск по описанию "<?= $searchSampleDescription ?>":</strong></p>
        <pre><?= htmlspecialchars(print_r($foundByDesc, true)) ?></pre>

        <p><strong>Поиск по ID = 3 (через foreach):</strong></p>
        <pre><?= htmlspecialchars(print_r($foundByIdForeach, true)) ?></pre>

        <p><strong>Поиск по ID = 3 (через array_filter - на высшую оценку):</strong></p>
        <pre><?= htmlspecialchars(print_r($foundByIdFilter, true)) ?></pre>
    </div>

    <!-- ЗАДАНИЕ 2: Работа с файловой системой и галерея -->
    <h2 id="gallery">Задание 2: Галерея изображений из каталога "image" (scandir)</h2>
    <div class="gallery">
        <?php
        $dir = __DIR__ . '/image/';
        $webDir = 'image/';
        $files = is_dir($dir) ? scandir($dir) : false;

        if ($files !== false) {
            for ($i = 0; $i < count($files); $i++) {
                if (($files[$i] != ".") && ($files[$i] != "..")) {
                    $path = $webDir . $files[$i];
                    ?>
                    <div class="gallery-item">
                        <img src="<?= htmlspecialchars($path) ?>" alt="<?= htmlspecialchars($files[$i]) ?>" loading="lazy">
                        <p><?= htmlspecialchars($files[$i]) ?></p>
                    </div>
                    <?php
                }
            }
        } else {
            echo "<p>Ошибка чтения директории с изображениями.</p>";
        }
        ?>
    </div>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> Лабораторная работа №4. Выполнено в рамках курса Advanced Web Development.</p>
</footer>

</body>
</html>
