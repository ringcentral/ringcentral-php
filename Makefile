.PHONY: docker-build-5
docker-build-5:
	docker build -t ringcentral-php-sdk-5 -f Dockerfile5 .

.PHONY: docker-build-7
docker-build-7:
	docker build -t ringcentral-php-sdk-7 -f Dockerfile7 .

.PHONY: docker-login-5
docker-login-5:
	docker run -v $(shell pwd):/opt/sdk --name ringcentral-php-sdk-5 -i ringcentral-php-sdk-5 /bin/bash

.PHONY: docker-login-7
docker-login-7:
	docker run -v $(shell pwd):/opt/sdk -i -t ringcentral-php-sdk-7 /bin/bash
