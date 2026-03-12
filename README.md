# Short Link Service + QR

Сервис коротких ссылок с QR-кодами на стеке **Yii2 + SQLite + jQuery + Bootstrap 5**.

## Быстрый старт

### Вариант 1 — скрипт (PHP + Composer на хосте)

```bash
git clone https://github.com/vrtc/qr.git short-link && cd short-link
./start.sh
```

Скрипт сам установит зависимости, применит миграции и запустит сервер.

```bash
./start.sh         # порт 8080 по умолчанию
./start.sh 9888    # или любой другой порт
```

### Вариант 2 — Docker

```bash
git clone https://github.com/vrtc/qr.git short-link && cd short-link
docker compose up --build
```

Приложение будет доступно на `http://localhost:8080`.  
База данных хранится в именованном Docker-томе `sqlite_data` и переживает пересборку контейнера.

## Функциональность

- Ввод URL, валидация и проверка доступности ресурса
- Генерация короткой ссылки вида `/s/{code}`
- Генерация QR-кода, сканирование которого ведёт на короткую ссылку
- AJAX-взаимодействие (без перезагрузки страницы)
- Хранение логов переходов: IP-адрес, User-Agent, время
- Счётчик переходов по каждой ссылке

## Требования

- PHP >= 7.4 (рекомендуется PHP 8.1+)
- SQLite (встроена в PHP через расширение `pdo_sqlite`)
- Composer
- Расширения PHP: `curl`, `gd`, `pdo_sqlite`, `mbstring`

## Установка

### 1. Клонировать репозиторий / распаковать проект

```bash
git clone <repo_url> short-link
cd short-link
```

### 2. Установить зависимости

```bash
composer install
```

### 3. База данных

SQLite используется без дополнительной настройки. Файл БД `db.sqlite` создаётся автоматически в корне проекта при первом запуске миграций.

Конфиг уже настроен в `config/db.php`:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . __DIR__ . '/../db.sqlite',
    'charset' => 'utf8',
];
```

### 4. Запустить миграции

```bash
php yii migrate
```

Будут созданы таблицы:
- `links` — хранит оригинальные URL, короткие коды, счётчики
- `click_logs` — хранит логи переходов (IP, User-Agent, время)

### 5. Запустить сервер

```bash
php yii serve --port=8080
```

Приложение будет доступно по адресу `http://localhost:8080`.

### 6. Права на папки

```bash
chmod -R 775 runtime/
chmod -R 775 web/assets/
```

## Использование

1. Откройте главную страницу приложения
2. Введите URL в поле ввода
3. Нажмите «ОК» — система проверит валидность и доступность URL
4. При успехе отобразится:
   - Короткая ссылка вида `http://your-domain/s/AbCdE`
   - QR-код — наведите камеру телефона для перехода
5. Переход по короткой ссылке:
   - Происходит редирект 301 на оригинальный URL
   - В базе фиксируется IP пользователя, User-Agent и время перехода

## Структура БД

### Таблица `links`

| Поле          | Тип           | Описание                    |
|---------------|---------------|-----------------------------|
| id            | INT PK AI     | Идентификатор               |
| short_code    | VARCHAR(10)   | Уникальный короткий код     |
| original_url  | VARCHAR(2048) | Оригинальный URL            |
| click_count   | INT           | Счётчик переходов           |
| ip_last       | VARCHAR(45)   | IP последнего перехода      |
| created_at    | DATETIME      | Дата создания               |

### Таблица `click_logs`

| Поле       | Тип          | Описание                      |
|------------|--------------|-------------------------------|
| id         | INT PK AI    | Идентификатор                 |
| link_id    | INT FK       | Ссылка на `links.id`          |
| ip         | VARCHAR(45)  | IP-адрес пользователя         |
| user_agent | VARCHAR(255) | User-Agent браузера           |
| created_at | DATETIME     | Дата и время перехода         |

## Стек технологий

- **Backend:** Yii2 Basic (PHP)
- **База данных:** SQLite (файл `db.sqlite`)
- **Frontend:** jQuery, Bootstrap 5
- **QR-генератор:** [endroid/qr-code](https://github.com/endroid/qr-code) v6 (без внешних API)


