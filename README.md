# YoPrint Assignment


## Stack

- Laravel + React

## Real Time Data

- Laravel Reverb

## Background Jobs

- Laravel Queues
- Redis

PS> Not using Horizon because of a issue with Horizon extensions requirement on windows.

## Database

- SQLite

## Installation

1. Install the required dependencies:
   ```bash
   npm install
   composer install
   ```

2. Initiate storage link:
   ```bash
   php artisan storage:link
   ```

3. Copy .env and generate key:
    ```bash
    cp .env.example .env
    php artisan key:generate
   ```

4. Migrate Database:
    ```bash
    php artisan migrate
   ```

4. Start Redis

5. Start vite:
    ```bash
    npm run dev
   ```

6. Start laravel (optional if using Herd):
    ```bash
    php artisan serve
   ```

7. Start Reverb:
    ```bash
    php artisan reverb:start
   ```

8. Start Queues:
    ```bash
    php artisan queue:work
    ```