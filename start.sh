#!/usr/bin/env bash
set -e

cd "$(dirname "$0")"

# 1. Зависимости
if [ ! -d "vendor" ]; then
    echo ">>> Установка зависимостей..."
    php composer.phar install --no-interaction --prefer-dist
fi

# 2. Миграции
echo ">>> Применение миграций..."
php yii migrate --interactive=0

# 3. Права
chmod -R 775 runtime/ web/assets/ 2>/dev/null || true

# 4. Запуск
PORT=${1:-9888}
echo ""
echo ">>> Приложение доступно: http://localhost:$PORT"
echo ">>> Для остановки нажмите Ctrl+C"
echo ""
php yii serve --port="$PORT"
