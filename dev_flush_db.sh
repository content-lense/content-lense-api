#!/bin/bash
#./dev_clear_cache.sh
#docker-compose exec php supervisorctl stop all 
docker-compose exec php bin/console doctrine:schema:drop --force
#docker-compose exec php bin/console dbal:run-sql 'DROP table if exists messenger_messages; DROP table if exists doctrine_migration_versions'
docker-compose exec php bin/console doctrine:schema:create --no-interaction
#docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec -T php bin/console doctrine:fixtures:load --no-interaction --group=dev
#docker-compose exec php supervisorctl start all
#docker-compose exec php bin/console lexik:jwt:generate-keypair
