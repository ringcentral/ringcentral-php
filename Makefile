.PHONY: docker-build
docker-build:
	docker build -t ringcentral-php-sdk -f Dockerfile .

.PHONY: docker-login
docker-login:
	docker run -v $(shell pwd):/opt/sdk -i -t ringcentral-php-sdk /bin/bash
