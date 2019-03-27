# Variables
DBNAME=authenticate
DBPASSWD=root

docker rm -f oauth-mysql oauth-server

docker run -d --rm \
    --name oauth-mysql \
    -e MYSQL_ROOT_PASSWORD=$DBPASSWD \
    -e MYSQL_DATABASE=$DBNAME \
    -v $(pwd)/data:/var/lib/mysql \
    -v $(pwd)/backups:/var/backups \
    mysql:latest

docker build -t oauth-image .
docker run -d --rm -p 8080:80 --link oauth-mysql:mysql --name oauth-server oauth-image
