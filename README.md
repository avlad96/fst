# Booking API

## Установка

1. Клонировать репозиторий:  

```
git clone https://github.com/avlad96/fst.git
```

2. Установить зависимости:

```
composer install
```

3. Создать `.env` файл и настроить его:

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