# Deploy

Первый production-деплой рассчитан на VPS + Docker Compose.

Подробный пошаговый гайд: [Docker deploy guide](docker-deploy.md).

## Подготовка

1. Настроить домен и HTTPS на уровне reverse proxy или внешнего балансировщика.
2. Скопировать `.env.production.example` в `.env`.
3. Заполнить `APP_KEY`, PostgreSQL credentials, SMTP и T-Bank credentials.
4. Убедиться, что `TBANK_NOTIFICATION_URL` доступен по HTTPS.

## TLS для Т-Банка

Образ `app` собирает `TBANK_CA_BUNDLE` из системного набора CA и корневого сертификата Минцифры. Этот PEM-файл использует только клиент Т-Банка. В окружениях без `TBANK_CA_BUNDLE` клиент проверяет TLS через системное хранилище.

Источник `docker/certs/russian-trusted-root-ca.crt` — [сертификат Госуслуг в PEM](https://gu-st.ru/content/lending/russian_trusted_root_ca_pem.crt). Отпечаток SHA-256 сертификата — `D2:6D:2D:02:31:B7:C3:9F:92:CC:73:85:12:BA:54:10:35:19:E4:40:5D:68:B5:BD:70:3E:97:88:CA:8E:CF:31`.

Проверка соединения из контейнера без инициализации платежа:

```bash
docker compose exec -T app sh -c 'curl --cacert "$TBANK_CA_BUNDLE" --head --silent --show-error --max-time 10 --output /dev/null --write-out "HTTP=%{http_code} TLS=%{ssl_verify_result}\n" https://securepay.tinkoff.ru/v2/Init'
```

`TLS=0` подтверждает проверку сертификата. `HTTP=405` в этой проверке ожидаем, так как `Init` принимает `POST`.

## Запуск

```bash
# GITHUB_TOKEN снимает анонимный лимит GitHub API — без него composer
# ловит HTTP 504 на zipball-ах при сборке.
export GITHUB_TOKEN=ghp_xxx

COMPOSE_BAKE=1 docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan optimize
docker compose exec app php artisan db:seed --force
```

## Smoke-check

- Главная страница открывается.
- `/admin` доступен только пользователю с `is_admin=true`.
- Worker запущен и обрабатывает очередь.
- Тестовое уведомление Т-Банка доходит до `/payments/tbank/notifications`.
- Успешное уведомление Т-Банка переводит бронь в оплаченное состояние и отправляет email клиенту.

## Сборка

- `worker` переиспользует образ `app` (`image: shale-tickets-app:latest`), поэтому
  `composer install` выполняется один раз, а не в каждом сервисе параллельно.
- `nginx` собирает только `public/` и Vite-бандл, без composer-стейджа.
- `COMPOSE_BAKE=1` заставляет compose собирать все сервисы одним buildx-вызовом
  с общим кешем стейджей. Без него сборку лучше запускать последовательно:
  `docker compose build app && docker compose build nginx`.
- Кеши composer и npm живут в BuildKit cache mounts, повторная сборка не
  перекачивает зависимости.
- `composer install` повторяет попытку до 3 раз с паузой 20 секунд — переживает
  временные 5xx от GitHub.
- Первая сборка после изменения `Dockerfile` пересоберёт `base` (компиляция
  `intl` и др.), дальше этот слой берётся из кеша.
