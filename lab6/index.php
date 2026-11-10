<?php

declare(strict_types=1);

require_once __DIR__ . '/src/RecipeOptions.php';
require_once __DIR__ . '/src/Recipe.php';
require_once __DIR__ . '/src/ValidatorInterface.php';
require_once __DIR__ . '/src/Validators/RequiredValidator.php';
require_once __DIR__ . '/src/Validators/LengthValidator.php';
require_once __DIR__ . '/src/Validators/DateValidator.php';
require_once __DIR__ . '/src/Validators/EnumValidator.php';
require_once __DIR__ . '/src/Validators/IntegerRangeValidator.php';
require_once __DIR__ . '/src/Validators/TagsValidator.php';
require_once __DIR__ . '/src/RecipeValidator.php';
require_once __DIR__ . '/src/RecipeRepository.php';
require_once __DIR__ . '/src/RecipeService.php';
require_once __DIR__ . '/src/RecipeSorter.php';
require_once __DIR__ . '/src/RecipeTableRenderer.php';

session_start();

/**
 * Экранирует значение для безопасного вывода в HTML.
 *
 * @param mixed $value Исходное значение.
 * @return string Экранированная строка.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = (string) $_SESSION['csrf_token'];

$repository = new RecipeRepository(__DIR__ . '/data.json');
$validator = new RecipeValidator();
$service = new RecipeService($repository, $validator);
$sorter = new RecipeSorter();
$renderer = new RecipeTableRenderer();

$errors = [];
$successMessage = '';

$sort = isset($_GET['sort']) && is_string($_GET['sort']) && in_array($_GET['sort'], RecipeSorter::FIELDS, true)
    ? $_GET['sort']
    : 'created_at';
$direction = isset($_GET['dir']) && $_GET['dir'] === 'asc' ? 'asc' : 'desc';

$formData = $service->emptyData();

if (isset($_SESSION['flash'])) {
    $successMessage = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $postedToken = (string) ($_POST['csrf_token'] ?? '');

    if (!hash_equals($csrfToken, $postedToken)) {
        $errors['form'] = 'Сессия истекла. Обновите страницу и попробуйте снова.';
    } else {
        try {
            $result = $service->create($_POST);
            $formData = $result['data'];

            if ($result['success'] && $result['recipe'] !== null) {
                $_SESSION['flash'] = 'Рецепт «' . $result['recipe']->getTitle() . '» успешно добавлен.';

                $query = http_build_query(['sort' => $sort, 'dir' => $direction]);
                header('Location: index.php?' . $query);
                exit;
            }

            $errors = $result['errors'];
        } catch (RuntimeException $exception) {
            error_log('Recipe app: ' . $exception->getMessage());
            $errors['form'] = 'Не удалось сохранить рецепт. Попробуйте ещё раз.';
        }
    }
}

try {
    $recipes = $sorter->sort($repository->all(), $sort, $direction);
} catch (RuntimeException $exception) {
    error_log('Recipe app: ' . $exception->getMessage());
    $recipes = [];
    $errors['form'] = $errors['form'] ?? 'Не удалось загрузить список рецептов.';
}

$totalRecipes = count($recipes);
$categoryCount = count(array_unique(array_map(
    static fn (Recipe $recipe): string => $recipe->getCategory(),
    $recipes
)));
$averagePrepTime = $totalRecipes > 0
    ? (int) round(array_sum(array_map(
        static fn (Recipe $recipe): int => $recipe->getPrepTime(),
        $recipes
    )) / $totalRecipes)
    : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лабораторная работа №6 — Управление рецептами</title>
    <style>
        :root {
            --bg: #f6f3ee;
            --surface: #fffdf9;
            --text: #292524;
            --muted: #78716c;
            --brand: #166534;
            --brand-dark: #14532d;
            --accent: #d97706;
            --border: #e7e0d8;
            --error: #b91c1c;
            --success: #15803d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        header {
            padding: 36px 40px 82px;
            background: linear-gradient(135deg, #14532d, #166534 55%, #3f6212);
            color: #fff;
        }

        .header-inner {
            max-width: 1180px;
            margin: 0 auto;
        }

        .eyebrow {
            margin: 0 0 10px;
            color: #bbf7d0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 32px;
            line-height: 1.2;
        }

        .subtitle {
            margin: 0;
            color: #dcfce7;
        }

        main {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px 40px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-top: -50px;
        }

        .stat-card {
            padding: 18px 20px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(41, 37, 36, 0.08);
        }

        .stat-card span {
            display: block;
            margin-bottom: 6px;
            color: var(--muted);
            font-size: 13px;
        }

        .stat-card strong {
            font-size: 24px;
        }

        .panel {
            margin-top: 24px;
            padding: 26px 28px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(41, 37, 36, 0.05);
        }

        .panel-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel-heading h2 {
            margin: 0 0 4px;
            color: #1c1917;
            font-size: 21px;
        }

        .panel-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
        }

        .alert-error {
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }

        .alert-success {
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 22px;
        }

        .field-wide {
            grid-column: 1 / -1;
        }

        .field label,
        .tags-field legend {
            display: block;
            margin-bottom: 6px;
            color: #44403c;
            font-size: 14px;
            font-weight: 600;
        }

        .required {
            color: var(--error);
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 11px 13px;
            border: 1px solid #d6cec5;
            border-radius: 9px;
            background: #fff;
            color: var(--text);
            font: inherit;
            font-size: 14px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .field textarea {
            min-height: 96px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: var(--brand);
            outline: none;
            box-shadow: 0 0 0 3px rgba(22, 101, 52, 0.12);
        }

        .field input::placeholder,
        .field textarea::placeholder {
            color: #a8a29e;
        }

        .hint {
            margin: 5px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .field-error {
            margin: 6px 0 0;
            color: var(--error);
            font-size: 13px;
        }

        .tags-field {
            margin: 0;
            padding: 14px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        .tags-field legend {
            padding: 0 6px;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 18px;
        }

        .checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #44403c;
            font-size: 14px;
            font-weight: 400;
            cursor: pointer;
        }

        .checkbox input {
            width: 16px;
            height: 16px;
            accent-color: var(--brand);
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 22px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 20px;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .btn-primary {
            border: 1px solid var(--brand);
            background: var(--brand);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            border-color: var(--brand-dark);
        }

        .btn-ghost {
            border: 1px solid #d6cec5;
            background: transparent;
            color: var(--text);
        }

        .btn-ghost:hover {
            border-color: #a8a29e;
            background: #faf7f2;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .recipes-table {
            width: 100%;
            min-width: 1000px;
            border-collapse: collapse;
            background: var(--surface);
        }

        .recipes-table th,
        .recipes-table td {
            padding: 13px 14px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 13.5px;
            vertical-align: top;
        }

        .recipes-table th {
            background: #faf7f2;
            color: #57534e;
            font-weight: 600;
            white-space: nowrap;
        }

        .recipes-table th a {
            color: inherit;
            text-decoration: none;
        }

        .recipes-table th a:hover {
            color: var(--brand);
        }

        .recipes-table th a.sorted {
            color: var(--brand);
        }

        .recipes-table tbody tr:hover {
            background: #fdfbf7;
        }

        .recipes-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            white-space: nowrap;
        }

        .badge-category {
            background: #dcfce7;
            color: #166534;
        }

        .badge-easy {
            background: #ecfccb;
            color: #3f6212;
        }

        .badge-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-hard {
            background: #fee2e2;
            color: #991b1b;
        }

        .tag {
            display: inline-block;
            margin: 1px 3px 3px 0;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .muted {
            color: #a8a29e;
        }

        details summary {
            color: var(--brand);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .details {
            margin-top: 9px;
            padding: 10px 12px;
            border-radius: 8px;
            background: #faf7f2;
            color: #57534e;
            font-size: 12.5px;
            max-width: 430px;
        }

        .details p {
            margin: 0 0 9px;
        }

        .details p:last-child {
            margin-bottom: 0;
        }

        .details .meta {
            color: #a8a29e;
            font-size: 11.5px;
        }

        .empty {
            padding: 26px !important;
            color: var(--muted);
            text-align: center !important;
        }

        footer {
            padding: 24px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 860px) {
            .stats {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .form-grid,
            .checkbox-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 22px 18px;
            }
        }

        @media (max-width: 560px) {
            header {
                padding: 28px 18px 76px;
            }

            h1 {
                font-size: 25px;
            }

            main {
                padding: 0 14px 32px;
            }

            .stats {
                margin-top: -46px;
                gap: 10px;
            }

            .stat-card {
                padding: 14px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <p class="eyebrow">Advanced Web Development · Лабораторная работа №6</p>
        <h1>Управление рецептами</h1>
        <p class="subtitle">Форма с серверной валидацией, JSON-хранилище и сортируемая таблица</p>
    </div>
</header>

<main>
    <section class="stats" aria-label="Статистика">
        <div class="stat-card">
            <span>Рецептов</span>
            <strong><?= $totalRecipes ?></strong>
        </div>
        <div class="stat-card">
            <span>Категорий</span>
            <strong><?= $categoryCount ?></strong>
        </div>
        <div class="stat-card">
            <span>Среднее время</span>
            <strong><?= $averagePrepTime ?> мин</strong>
        </div>
        <div class="stat-card">
            <span>Хранилище</span>
            <strong>JSON</strong>
        </div>
    </section>

    <section class="panel" id="add-recipe">
        <div class="panel-heading">
            <div>
                <h2>Новый рецепт</h2>
                <p>Поля со звёздочкой обязательны. Данные проверяются и на клиенте, и на сервере.</p>
            </div>
        </div>

        <?php if (isset($errors['form'])): ?>
            <div class="alert alert-error" role="alert"><?= e($errors['form']) ?></div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success" role="status"><?= e($successMessage) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?<?= e(http_build_query(['sort' => $sort, 'dir' => $direction])) ?>">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

            <div class="form-grid">
                <div class="field field-wide">
                    <label for="title">Название рецепта <span class="required">*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= e($formData['title']) ?>"
                        required
                        minlength="3"
                        maxlength="100"
                        placeholder="Например, тыквенный крем-суп"
                        autocomplete="off"
                    >
                    <?php if (isset($errors['title'])): ?>
                        <p class="field-error"><?= e($errors['title']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="author">Автор <span class="required">*</span></label>
                    <input
                        type="text"
                        id="author"
                        name="author"
                        value="<?= e($formData['author']) ?>"
                        required
                        minlength="2"
                        maxlength="80"
                        placeholder="Имя автора"
                        autocomplete="name"
                    >
                    <?php if (isset($errors['author'])): ?>
                        <p class="field-error"><?= e($errors['author']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="category">Категория <span class="required">*</span></label>
                    <select id="category" name="category" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach (RecipeOptions::CATEGORIES as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $formData['category'] === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <p class="field-error"><?= e($errors['category']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="difficulty">Сложность <span class="required">*</span></label>
                    <select id="difficulty" name="difficulty" required>
                        <option value="">Выберите сложность</option>
                        <?php foreach (RecipeOptions::DIFFICULTIES as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= $formData['difficulty'] === $value ? 'selected' : '' ?>>
                                <?= e($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['difficulty'])): ?>
                        <p class="field-error"><?= e($errors['difficulty']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="prep_time">Время приготовления, мин <span class="required">*</span></label>
                    <input
                        type="number"
                        id="prep_time"
                        name="prep_time"
                        value="<?= e($formData['prep_time']) ?>"
                        required
                        min="1"
                        max="1440"
                        step="1"
                        inputmode="numeric"
                    >
                    <?php if (isset($errors['prep_time'])): ?>
                        <p class="field-error"><?= e($errors['prep_time']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field">
                    <label for="created_at">Дата создания <span class="required">*</span></label>
                    <input
                        type="date"
                        id="created_at"
                        name="created_at"
                        value="<?= e($formData['created_at']) ?>"
                        required
                        max="<?= e(date('Y-m-d')) ?>"
                    >
                    <?php if (isset($errors['created_at'])): ?>
                        <p class="field-error"><?= e($errors['created_at']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field field-wide">
                    <label for="ingredients">Ингредиенты <span class="required">*</span></label>
                    <textarea
                        id="ingredients"
                        name="ingredients"
                        rows="4"
                        required
                        minlength="10"
                        maxlength="2000"
                        placeholder="Каждый ингредиент — с новой строки"
                    ><?= e($formData['ingredients']) ?></textarea>
                    <p class="hint">От 10 до 2000 символов.</p>
                    <?php if (isset($errors['ingredients'])): ?>
                        <p class="field-error"><?= e($errors['ingredients']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="field field-wide">
                    <label for="instructions">Приготовление <span class="required">*</span></label>
                    <textarea
                        id="instructions"
                        name="instructions"
                        rows="5"
                        required
                        minlength="20"
                        maxlength="5000"
                        placeholder="Опишите шаги приготовления"
                    ><?= e($formData['instructions']) ?></textarea>
                    <p class="hint">От 20 до 5000 символов.</p>
                    <?php if (isset($errors['instructions'])): ?>
                        <p class="field-error"><?= e($errors['instructions']) ?></p>
                    <?php endif; ?>
                </div>

                <fieldset class="field field-wide tags-field">
                    <legend>Теги</legend>
                    <div class="checkbox-grid">
                        <?php foreach (RecipeOptions::TAGS as $value => $label): ?>
                            <label class="checkbox">
                                <input
                                    type="checkbox"
                                    id="tag-<?= e($value) ?>"
                                    name="tags[]"
                                    value="<?= e($value) ?>"
                                    <?= in_array($value, (array) $formData['tags'], true) ? 'checked' : '' ?>
                                >
                                <span><?= e($label) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="hint">Необязательно. Можно выбрать несколько вариантов.</p>
                    <?php if (isset($errors['tags'])): ?>
                        <p class="field-error"><?= e($errors['tags']) ?></p>
                    <?php endif; ?>
                </fieldset>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить рецепт</button>
                <a href="index.php" class="btn btn-ghost">Сбросить</a>
            </div>
        </form>
    </section>

    <section class="panel" id="recipes-list">
        <div class="panel-heading">
            <div>
                <h2>Все рецепты</h2>
                <p>Нажмите на заголовок столбца, чтобы изменить сортировку.</p>
            </div>
        </div>

        <div class="table-wrap">
            <?php echo $renderer->render($recipes, $sort, $direction); ?>
        </div>
    </section>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> Лабораторная работа №6. Курс Advanced Web Development.</p>
</footer>

</body>
</html>
