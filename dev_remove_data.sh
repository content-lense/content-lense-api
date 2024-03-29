#!/bin/bash
echo "Removing remaining messages in queue ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM messenger_messages CASCADE"
echo "Removing raw article analysis results ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM article_analysis_result CASCADE"
echo "Removing article complexity results ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM article_complexity CASCADE"
echo "Removing article topics ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM article_topic CASCADE"
echo "Removing article mentions ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM article_mention CASCADE"
echo "Removing persons ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM person CASCADE"
echo "Removing articles ... "
docker-compose exec php bin/console doctrine:query:sql "DELETE FROM article CASCADE"
