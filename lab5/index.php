<?php

declare(strict_types=1);

require_once __DIR__ . '/src/Transaction.php';
require_once __DIR__ . '/src/TransactionStorageInterface.php';
require_once __DIR__ . '/src/TransactionRepository.php';
require_once __DIR__ . '/src/TransactionManager.php';
require_once __DIR__ . '/src/TransactionTableRenderer.php';

/**
 * Экранирует значение для вывода в HTML.
 *
 * @param mixed $value Значение.
 * @return string
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$repository = new TransactionRepository(__DIR__ . '/data.json');
$manager = new TransactionManager($repository);
$renderer = new TransactionTableRenderer();

$categories = [
    'Продукты',
    'Транспорт',
    'Развлечения',
    'Здоровье',
    'Коммунальные услуги',
    'Рестораны',
    'Образование',
    'Связь',
    'Спорт',
    'Путешествия',
    'Прочее',
];

$message = '';
$error = '';
$form = [
    'date' => date('Y-m-d'),
    'amount' => '',
    'description' => '',
    'merchant' => '',
    'category' => $categories[0],
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    try {
        if ($action === 'add') {
            $form = [
                'date' => isset($_POST['date']) && is_string($_POST['date']) ? trim($_POST['date']) : '',
                'amount' => isset($_POST['amount']) && is_string($_POST['amount']) ? trim($_POST['amount']) : '',
                'description' => isset($_POST['description']) && is_string($_POST['description']) ? trim($_POST['description']) : '',
                'merchant' => isset($_POST['merchant']) && is_string($_POST['merchant']) ? trim($_POST['merchant']) : '',
                'category' => isset($_POST['category']) && is_string($_POST['category']) ? trim($_POST['category']) : '',
            ];

            $repository->addTransaction(new Transaction(
                $repository->getNextId(),
                $form['date'],
                (float) $form['amount'],
                $form['description'],
                $form['merchant'],
                $form['category']
            ));

            $message = 'Транзакция добавлена.';
            $form = [
                'date' => date('Y-m-d'),
                'amount' => '',
                'description' => '',
                'merchant' => '',
                'category' => $categories[0],
            ];
        } elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($repository->findById($id) === null) {
                $error = 'Транзакция с таким ID не найдена.';
            } else {
                $repository->removeTransactionById($id);
                $message = 'Транзакция #' . $id . ' удалена.';
            }
        }
    } catch (Exception $exception) {
        $error = $exception->getMessage();
    }
}

$searchId = isset($_GET['search_id']) && is_string($_GET['search_id']) && ctype_digit($_GET['search_id'])
    ? (int) $_GET['search_id']
    : null;
$searchDescription = isset($_GET['search_description']) && is_string($_GET['search_description'])
    ? trim($_GET['search_description'])
    : '';
$searchMerchant = isset($_GET['search_merchant']) && is_string($_GET['search_merchant'])
    ? trim($_GET['search_merchant'])
    : '';
$searchCategory = isset($_GET['search_category']) && is_string($_GET['search_category'])
    && in_array($_GET['search_category'], $categories, true)
    ? $_GET['search_category']
    : '';

$sort = isset($_GET['sort']) && $_GET['sort'] === 'amount' ? 'amount' : 'date';

$transactions = $sort === 'amount'
    ? $manager->sortTransactionsByAmountDesc()
    : $manager->sortTransactionsByDate();

$hasSearch = $searchId !== null
    || $searchDescription !== ''
    || $searchMerchant !== ''
    || $searchCategory !== '';

if ($hasSearch) {
    $transactions = array_values(array_filter(
        $transactions,
        static function (Transaction $transaction) use (
            $searchId,
            $searchDescription,
            $searchMerchant,
            $searchCategory
        ): bool {
            if ($searchId !== null && $transaction->getId() !== $searchId) {
                return false;
            }

            if ($searchDescription !== '' && mb_stripos($transaction->getDescription(), $searchDescription) === false) {
                return false;
            }

            if ($searchMerchant !== '' && mb_stripos($transaction->getMerchant(), $searchMerchant) === false) {
                return false;
            }

            if ($searchCategory !== '' && $transaction->getCategory() !== $searchCategory) {
                return false;
            }

            return true;
        }
    ));
}

$count = count($repository->getAllTransactions());
$total = $manager->calculateTotalAmount();
$average = $count > 0 ? $total / $count : 0.0;
$shown = count($transactions);

$rangeTotal = null;
$rangeStart = isset($_GET['start']) && is_string($_GET['start']) ? trim($_GET['start']) : '';
$rangeEnd = isset($_GET['end']) && is_string($_GET['end']) ? trim($_GET['end']) : '';

if ($rangeStart !== '' && $rangeEnd !== '') {
    try {
        $rangeTotal = $manager->calculateTotalAmountByDateRange($rangeStart, $rangeEnd);
    } catch (Exception $exception) {
        $error = 'Проверьте даты периода.';
    }
}

$merchant = isset($_GET['merchant']) && is_string($_GET['merchant']) ? trim($_GET['merchant']) : '';
$merchantCount = $merchant !== '' ? $manager->countTransactionsByMerchant($merchant) : null;

$searchParams = array_filter([
    'search_id' => $searchId !== null ? (string) $searchId : '',
    'search_description' => $searchDescription,
    'search_merchant' => $searchMerchant,
    'search_category' => $searchCategory,
], static fn (string $value): bool => $value !== '');

$dateUrl = 'index.php?' . http_build_query($searchParams + ['sort' => 'date']);
$amountUrl = 'index.php?' . http_build_query($searchParams + ['sort' => 'amount']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №5 — Банковские транзакции</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f4f6f9;
            color: #1e293b;
            line-height: 1.5;
        }

        header {
            padding: 28px 32px;
            background: #1e293b;
            color: #fff;
        }

        header h1 {
            max-width: 1100px;
            margin: 0 auto 4px;
            font-size: 25px;
        }

        header p {
            max-width: 1100px;
            margin: 0 auto;
            color: #cbd5e1;
            font-size: 14px;
        }

        main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px 20px 40px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .card,
        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
        }

        .card {
            padding: 15px 18px;
        }

        .card span {
            display: block;
            margin-bottom: 4px;
            color: #64748b;
            font-size: 13px;
        }

        .card strong {
            font-size: 22px;
        }

        .panel {
            margin-bottom: 20px;
            padding: 20px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 18px;
        }

        .panel h3 {
            margin: 20px 0 8px;
            font-size: 14px;
        }

        .two-columns {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }

        .fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .wide {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 4px;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }

        input,
        select {
            width: 100%;
            padding: 9px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: #fff;
            color: #1e293b;
            font: inherit;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            border-color: #2563eb;
            outline: none;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 14px;
        }

        .btn {
            display: inline-block;
            padding: 9px 15px;
            border: 1px solid #2563eb;
            border-radius: 7px;
            background: #2563eb;
            color: #fff;
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover { background: #1d4ed8; }

        .btn-secondary {
            border-color: #cbd5e1;
            background: #fff;
            color: #334155;
        }

        .btn-secondary:hover { background: #f8fafc; }

        .btn-danger {
            border-color: #dc2626;
            background: #dc2626;
        }

        .btn-danger:hover { background: #b91c1c; }

        .message {
            margin-bottom: 16px;
            padding: 11px 15px;
            border-radius: 7px;
            font-size: 14px;
        }

        .success {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .sort-links {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sort-link {
            padding: 7px 11px;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .sort-link.active {
            border-color: #2563eb;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            padding: 11px 13px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            font-size: 13.5px;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            white-space: nowrap;
        }

        tbody tr:last-child td { border-bottom: 0; }
        tbody tr:hover { background: #f8fbff; }
        td.amount { font-weight: 600; white-space: nowrap; }
        td.empty { padding: 24px; text-align: center; color: #64748b; }

        .category {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 12px;
            white-space: nowrap;
        }

        .muted {
            color: #64748b;
            font-size: 13px;
        }

        .tools {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .result {
            margin: 12px 0 0;
            color: #1d4ed8;
            font-size: 21px;
            font-weight: 700;
        }

        footer {
            padding: 20px;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 850px) {
            .stats,
            .two-columns,
            .tools {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .fields { grid-template-columns: 1fr; }
            header { padding: 24px 18px; }
            main { padding: 18px 12px 30px; }
        }
    </style>
</head>
<body>

<header>
    <h1>Лабораторная работа №5: банковские транзакции</h1>
    <p>ООП в PHP: классы, инкапсуляция, репозиторий и интерфейс хранилища</p>
</header>

<main>
    <section class="stats">
        <div class="card">
            <span>Всего транзакций</span>
            <strong><?= $count ?></strong>
        </div>
        <div class="card">
            <span>Общая сумма</span>
            <strong>$<?= number_format($total, 2, '.', ' ') ?></strong>
        </div>
        <div class="card">
            <span>Средний платёж</span>
            <strong>$<?= number_format($average, 2, '.', ' ') ?></strong>
        </div>
    </section>

    <?php if ($message !== ''): ?>
        <div class="message success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="message error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="two-columns">
        <section class="panel">
            <h2>Добавить транзакцию</h2>
            <form method="post" action="index.php">
                <input type="hidden" name="action" value="add">

                <div class="fields">
                    <div class="wide">
                        <label for="description">Описание</label>
                        <input type="text" id="description" name="description" value="<?= e($form['description']) ?>" required>
                    </div>

                    <div>
                        <label for="date">Дата</label>
                        <input type="date" id="date" name="date" value="<?= e($form['date']) ?>" required max="<?= date('Y-m-d') ?>">
                    </div>

                    <div>
                        <label for="amount">Сумма, $</label>
                        <input type="number" id="amount" name="amount" value="<?= e($form['amount']) ?>" required min="0.01" step="0.01">
                    </div>

                    <div>
                        <label for="merchant">Получатель</label>
                        <input type="text" id="merchant" name="merchant" value="<?= e($form['merchant']) ?>" required>
                    </div>

                    <div>
                        <label for="category">Категория</label>
                        <select id="category" name="category" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category) ?>" <?= $form['category'] === $category ? 'selected' : '' ?>>
                                    <?= e($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Добавить</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <h2>Поиск и сортировка</h2>
            <form method="get" action="index.php">
                <div class="fields">
                    <div>
                        <label for="search-id">ID</label>
                        <input type="number" id="search-id" name="search_id" min="1" value="<?= $searchId !== null ? e($searchId) : '' ?>" placeholder="Например, 10">
                    </div>

                    <div>
                        <label for="search-category">Категория</label>
                        <select id="search-category" name="search_category">
                            <option value="">Все категории</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= e($category) ?>" <?= $searchCategory === $category ? 'selected' : '' ?>>
                                    <?= e($category) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wide">
                        <label for="search-description">Описание</label>
                        <input type="text" id="search-description" name="search_description" value="<?= e($searchDescription) ?>" placeholder="Например, кофе">
                    </div>

                    <div class="wide">
                        <label for="search-merchant">Получатель</label>
                        <input type="text" id="search-merchant" name="search_merchant" value="<?= e($searchMerchant) ?>" placeholder="Например, CoffeeHouse">
                    </div>
                </div>

                <p class="muted" style="margin: 8px 0 0;">Поля можно комбинировать: условия объединяются через «И».</p>

                <div class="actions">
                    <button type="submit" class="btn">Найти</button>
                    <a href="index.php" class="btn btn-secondary">Сбросить</a>
                </div>
            </form>

            <h3>Сортировка</h3>
            <div class="sort-links">
                <a class="sort-link <?= $sort === 'date' ? 'active' : '' ?>" href="<?= e($dateUrl) ?>">По дате</a>
                <a class="sort-link <?= $sort === 'amount' ? 'active' : '' ?>" href="<?= e($amountUrl) ?>">По сумме ↓</a>
            </div>
        </section>
    </div>

    <section class="panel">
        <h2>Транзакции</h2>
        <p class="muted">Показано: <?= $shown ?> из <?= $count ?></p>

        <div class="table-wrap">
            <?php echo $renderer->render($transactions); ?>
        </div>
    </section>

    <section class="panel">
        <h2>Сервис</h2>
        <div class="tools">
            <div>
                <h3>Удалить транзакцию</h3>
                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="delete">
                    <label for="delete-id">ID транзакции</label>
                    <input type="number" id="delete-id" name="id" min="1" required>
                    <div class="actions">
                        <button type="submit" class="btn btn-danger">Удалить</button>
                    </div>
                </form>
            </div>

            <div>
                <h3>Сумма за период</h3>
                <form method="get" action="index.php">
                    <label for="start">С</label>
                    <input type="date" id="start" name="start" value="<?= e($rangeStart) ?>">
                    <label for="end" style="margin-top: 8px;">По</label>
                    <input type="date" id="end" name="end" value="<?= e($rangeEnd) ?>">
                    <div class="actions">
                        <button type="submit" class="btn">Рассчитать</button>
                    </div>
                </form>
                <p class="result"><?= $rangeTotal !== null ? '$' . number_format($rangeTotal, 2, '.', ' ') : '—' ?></p>
            </div>

            <div>
                <h3>Количество по получателю</h3>
                <form method="get" action="index.php">
                    <label for="merchant-filter">Получатель</label>
                    <input type="text" id="merchant-filter" name="merchant" value="<?= e($merchant) ?>" placeholder="Например, SuperMart">
                    <div class="actions">
                        <button type="submit" class="btn">Посчитать</button>
                    </div>
                </form>
                <p class="result"><?= $merchantCount ?? '—' ?></p>
            </div>
        </div>
    </section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Лабораторная работа №5. Курс Advanced Web Development.</p>
</footer>

</body>
</html>
