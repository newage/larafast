додай окремий контейнер в docker для cron.
для оточень prod і local будуть різні налаштування крону, тому в dcoker/crond зробити 2 файла для прода і локала, і в залежності від .env APP_ENV gslrk.xfnb ytj,[slybq afqk]
як краще зберігати крон команди в файлах? json?
перша крон команда для прода */5 * * * * php artisan schedule:run
