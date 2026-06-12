# Shale Tickets

Shale Tickets - Laravel-приложение для бронирования билетов на экскурсии. Пользователь выбирает экскурсию, дату и количество взрослых/детских билетов, оформляет гостевое бронирование, оплачивает его через API Т-Банка и получает на почту организационную информацию.

## Стек

- PHP 8.4
- Laravel 13
- PostgreSQL
- Blade + Alpine для frontend v1
- Filament для админ-панели
- Vite + Tailwind CSS
- Pest для тестов
- Laravel Pint для форматирования PHP-кода

## Документация

Основная проектная документация находится в `docs/`:

- [Описание проекта](docs/project-overview.md)
- [Архитектура](docs/architecture.md)
- [Code-style](docs/code-style.md)
- [Deploy](docs/deploy.md)
- [Docker deploy guide](docs/docker-deploy.md)

Перед изменениями в доменной логике сначала проверьте эти документы. Если изменение меняет архитектурное правило или границы модуля, обновите соответствующий документ вместе с кодом.

## Быстрый старт

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Запуск локальной разработки:

```bash
composer run dev
```

Команда запускает Laravel server, queue listener, pail и Vite dev server.

Отдельные команды:

```bash
npm run dev
npm run build
php artisan test
vendor/bin/pint
```

После `php artisan db:seed` создается локальный администратор:

```text
admin@example.com / password
```

## Конфигурация

По умолчанию проект ориентирован на PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_DATABASE=shale_tickets
```

Если используется `SESSION_DRIVER=database`, в базе должна существовать таблица `sessions`. Ее можно добавить командой:

```bash
php artisan make:session-table
php artisan migrate
```

Для локальной разработки без хранения сессий в базе можно использовать:

```env
SESSION_DRIVER=file
```

## Архитектурный ориентир

Проект развивается как модульный монолит. Целевые домены:

- `Excursions` - экскурсии, описание, сезонные категории, цены и диапазоны доступных дат;
- `Bookings` - гостевые бронирования, данные покупателя, сопровождающие, выбранная дата, статусы и фиксация заказа;
- `Payments` - платежные намерения, callbacks/webhooks и статусы оплаты;
- `Notifications` - email-уведомления после успешной оплаты;
- `Shared` - общие value objects, исключения, контракты и вспомогательные классы.

Платежная интеграция должна идти через контракт `PaymentGateway`. Домен бронирования не должен напрямую зависеть от API Т-Банка.

## Текущий фокус v1

В первом фокусе важен критический путь: создать гостевое бронирование, принять оплату, подтвердить заказ и отправить email. Личный кабинет, аккаунты клиентов, админ-панель и возвраты платежей рассматриваются как будущие расширения.
