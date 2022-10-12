#!/bin/bash
rm api/var/log/*
docker-compose exec php bin/console cache:clear
docker-compose exec php kill -USR2 1