DBNAME=authenticate
DBPASSWD=root

install: resources/keys/public.key

docker: image
	docker run -d --rm \
		--name oauth-mysql \
		-e MYSQL_ROOT_PASSWORD=$(DBPASSWD) \
		-e MYSQL_DATABASE=$(DBNAME) \
		mysql:5.6 \
		&& \
		docker run -d --rm \
		--name oauth-server \
		-p 8080:80 \
		-v ${PWD}:/var/www/html \
		--link oauth-mysql:mysql \
		oauth-image

start: install docker provision

provision:
	sleep 10 && docker exec oauth-server bin/init

image: Dockerfile
	docker build -t oauth-image .

clean:
	docker rm -f oauth-server oauth-mysql

.PHONY: start provision docker image clean

resources/keys/private.key:
	openssl genrsa -out resources/keys/private.key 4096

resources/keys/public.key: resources/keys/private.key
	openssl rsa -in resources/keys/private.key -pubout > resources/keys/public.key

