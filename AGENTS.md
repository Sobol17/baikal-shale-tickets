# AGENTS.md

Инструкции для AI-агентов, работающих с репозиторием Shale Tickets.

## Что прочитать первым

Перед изменениями в проекте прочитайте:

- `docs/project-overview.md` - продукт, сценарий v1 и домены;
- `docs/architecture.md` - модульный монолит, границы модулей и целевая структура;
- `docs/code-style.md` - правила code-style, тестов и интеграций;
- `docs/deploy.md` - production-деплой на VPS + Docker;
- `README.md` - команды запуска и краткий обзор.

## Проектный контекст

Shale Tickets - Laravel 13 приложение для бронирования мест на экскурсии. v1 строится вокруг гостевого бронирования: пользователь выбирает слот, вводит контакты, оплачивает через API Т-Банка и получает email с организационной информацией.

Frontend v1: Blade + Alpine. Backend: Laravel, PostgreSQL, очередь Laravel. Тесты: Pest. Форматирование PHP: Laravel Pint.

Админка v1: Filament, доступ только для пользователей с `is_admin=true`.

## Архитектурные правила

- Развивайте проект как модульный монолит внутри `app/Modules`.
- Создавайте модульные папки только когда появляется реальный код для модуля.
- Держите контроллеры тонкими: HTTP-запрос, вызов action/service, ответ.
- Бизнес-сценарии оформляйте в action-классах соответствующего модуля.
- Не вызывайте внешние API из контроллеров и Eloquent-моделей.
- Интеграцию с Т-Банком держите за контрактом `PaymentGateway`.
- Читайте env-значения через config-файлы и `config()`, не через `env()` в прикладном коде.
- Если изменение меняет архитектурное правило, обновите документ в `docs/`.

## Целевая структура

```text
app/
  Modules/
    Excursions/
    Bookings/
    Payments/
    Notifications/
    Shared/
database/
  migrations/
  seeders/
resources/
  views/
    excursions/
    bookings/
docs/
```

Подробная структура описана в `docs/architecture.md`.

## Команды

Установка:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Разработка:

```bash
composer run dev
```

Проверки:

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Сверка платежа вручную:

```bash
php artisan payments:sync {payment}
```

Форматирование PHP:

```bash
vendor/bin/pint
```

## Работа с базой и сессиями

Проект настроен на PostgreSQL. Если включен `SESSION_DRIVER=database`, убедитесь, что создана таблица `sessions`:

```bash
php artisan make:session-table
php artisan migrate
```

Для простой локальной разработки можно переключить сессии на файловое хранилище:

```env
SESSION_DRIVER=file
```

## Тестирование

- Для пользовательских сценариев бронирования и оплаты используйте feature-тесты Pest.
- Для расчетов доступных мест, переходов статусов и нормализации платежных статусов используйте unit- или feature-тесты по уровню интеграции.
- Не обращайтесь к реальному API Т-Банка в тестах. Используйте fake gateway или HTTP fake.

## Ограничения v1

Не добавляйте личный кабинет, аккаунты клиентов, админ-панель, промокоды, отчеты и возвраты платежей без явного запроса. Эти возможности считаются будущими расширениями.
