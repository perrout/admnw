#!/bin/sh

docker-compose -f docker-compose.yml -f docker/docker-compose-dev.yml build
