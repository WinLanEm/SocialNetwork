Перед сборкой контейнера выполните

mkdir -p tmp/db tmp/redis tmp/minio tmp/mongo tmp/elasticsearch
chmod -R 775 storage tmp

docker-compose build

После сборки контейнера

docker exec -it project_app bash

composer install </br>
php artisan migrate <br>
php artisan elasticsearch:create-index users --force<br>

npm install <br>
npm run dev <br>
php artisan queue:work <br>
php artisan db:seed (будет длится в районе минуты, потому что в seed 10000 записей для демонстрации работы поиска через elasticsearch) <br>
php artisan mongo:indexes<br> 
php artisan key:generate<br>
php artisan storage:link

