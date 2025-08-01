# Booking API

## Требования

- PHP >= 8.2
- MySQL >= 8.0
- Composer >= 2.x

## Установка

1. Клонировать репозиторий:  

```
git clone https://github.com/avlad96/fst.git
```

2. Установить зависимости:

```
composer install
```

3. Создать `.env` файл и настроить параметры окружения:

```
cp .env.example .env
```

4. Сгенерировать ключ приложения:

```
php artisan key:generate
```

5. Запустить миграции и сидер:

```
php artisan migrate --seed
```

## Тестирование

Для тестов необходимо настроить отдельный файл окружения .env.testing и выполнить:

```
php artisan test
```