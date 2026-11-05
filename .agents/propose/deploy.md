додай білд/деплой для github actions. докер і локальні папки деплоїти не потрібно, тільки папки з laravel
"composer install --no-dev --optimize-autoloader",
"npm ci",
"npm run build",
"rsync -avz --delete --exclude=.git --exclude=node_modules --exclude=.env --exclude=storage --exclude=tests ./ deploy@your-server:/var/www/site/",
"ssh deploy@your-server 'cd /var/www/larafast && php artisan migrate --force && php artisan optimize'"
