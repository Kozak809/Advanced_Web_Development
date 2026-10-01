# Лабораторная работа №4: Массивы и Функции

## Цель работы
Освоить работу с массивами в PHP, применяя различные операции: создание, добавление, удаление, сортировка и поиск. Закрепить навыки работы с функциями, включая передачу аргументов, возвращаемые значения и анонимные функции.

---

## Задание 1. Работа с массивами (Банковские транзакции)

### 1.1 и 1.2. Подготовка среды и создание массива
В начале файла включена строгая типизация `declare(strict_types=1);`.
Создан массив транзакций `$transactions`:

```php
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
    // ...
];
```

### 1.3. Вывод списка транзакций
Использован цикл `foreach` для построения строк таблицы с отображением ID, даты, дней с момента операции, суммы, описания и мерчанта, а также итоговой суммы в подвале таблицы (`calculateTotalAmount`).

### 1.4. Реализованные функции (со стандартом PHPDoc)

- **`calculateTotalAmount(array $transList): float`** — подсчет суммарной стоимости транзакций:
  ```php
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
  ```

- **`findTransactionByDescription(string $descriptionPart): array`** — регистронезависимый поиск по подстроке в описании:
  ```php
  /**
   * Ищет транзакции по части описания (регистронезависимо).
   *
   * @param string $descriptionPart Подстрока для поиска
   * @return array<int, array{...}> Найденные транзакции
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
  ```

- **`findTransactionById(int $id): ?array`** — поиск транзакции по ID через `foreach`:
  ```php
  /**
   * Ищет транзакцию по уникальному идентификатору с помощью foreach.
   *
   * @param int $id Идентификатор транзакции
   * @return array{...}|null Найденная транзакция или null
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
  ```

- **`findTransactionByIdFilter(int $id): ?array`** — поиск по ID через `array_filter` (на высшую оценку):
  ```php
  /**
   * Ищет транзакцию по идентификатору с помощью функции array_filter (на высшую оценку).
   *
   * @param int $id Идентификатор транзакции
   * @return array{...}|null Найденная транзакция или null
   */
  function findTransactionByIdFilter(int $id): ?array
  {
      global $transactions;
      $filtered = array_filter($transactions, fn(array $item): bool => $item['id'] === $id);
      return !empty($filtered) ? reset($filtered) : null;
  }
  ```

- **`daysSinceTransaction(string $date): int`** — расчет разницы в днях с текущей датой через `DateTime`:
  ```php
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
  ```

- **`addTransaction(int $id, string $date, float $amount, string $description, string $merchant): void`** — добавление новой транзакции в глобальный массив:
  ```php
  /**
   * Добавляет новую транзакцию в общий массив $transactions.
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
  ```

### 1.5. Сортировка транзакций
- По дате с использованием `usort()` и оператора «spaceship» (`<=>`):
  ```php
  usort($transactionsByDate, function (array $a, array $b): int {
      return strtotime($a['date']) <=> strtotime($b['date']);
  });
  ```
- По сумме (по убыванию):
  ```php
  usort($transactionsByAmount, function (array $a, array $b): int {
      return $b['amount'] <=> $a['amount'];
  });
  ```

---

## Задание 2. Работа с файловой системой (Галерея)

В каталоге `image/` сгенерировано 25 изображений `.jpg`.
Вывод галереи реализован с проверкой каталога, использованием `scandir()` и фильтрацией служебных записей `.` и `..`:

```php
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
}
```

Страница структурирована с тегами `<header>`, `<nav>`, `<main>` и `<footer>`, оформлена адаптивной CSS-сеткой (CSS Grid) и тенями карточек.

---

## Запуск проекта

Запуск встроенного сервера для лабораторной работы №4:
```cmd
php -S localhost:8000 -t lab4
```
Затем открыть в браузере: `http://localhost:8000`.

---

## Контрольные вопросы

### 1. Что такое массивы в PHP?
Массив в PHP — это упорядоченная структура данных, представляющая собой отображение (map), которое связывает *значения* с *ключами*. В отличие от многих других языков, массив в PHP объединяет возможности обычного индексного списка, ассоциативного массива (словаря/хеш-таблицы), стека и очереди. Элементами массива могут быть данные любых типов, включая другие массивы (многомерные массивы).

### 2. Каким образом можно создать массив в PHP?
1. **Короткий синтаксис (рекомендуемый):**
   ```php
   $arr = [1, 2, 3];
   $assoc = ["name" => "John", "age" => 25];
   ```
2. **Языковая конструкция `array()`:**
   ```php
   $arr = array(1, 2, 3);
   $assoc = array("name" => "John", "age" => 25);
   ```
3. **Поэлементная инициализация:**
   ```php
   $arr = [];
   $arr[] = "первый";
   $arr["key"] = "значение";
   ```
4. **С помощью встроенных функций:** `range(1, 10)`, `explode(',', 'a,b,c')`, `array_fill(...)` и др.

### 3. Для чего используется цикл foreach?
Цикл `foreach` предназначен исключительно для итерации (перебора) элементов массивов и объектов (реализующих интерфейс `Traversable`). Он избавляет от необходимости вручную управлять индексами или счетчиками и доступен в двух формах:
- Только значения: `foreach ($array as $value)`
- Ключи и значения: `foreach ($array as $key => $value)`
При каждой итерации внутренний указатель автоматически сдвигается к следующему элементу, пока массив не будет пройден целиком.
