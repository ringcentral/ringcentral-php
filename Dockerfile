FROM		ubuntu:latest
RUN			apt-get update -y
RUN			apt-get install -y \
	autoconf \
	build-essential \
	curl \
	make \
	wget \
	libcurl4-openssl-dev \
	libssl-dev \
	libxml2-dev \
	openssl \
	zlib1g-dev
RUN			wget http://in1.php.net/distributions/php-5.3.29.tar.bz2
RUN			tar -xvf php-5.3.29.tar.bz2
WORKDIR		/php-5.3.29
RUN			./configure --with-zlib --with-openssl --with-curl
RUN			make
RUN			make install
RUN			curl -sS https://getcomposer.org/installer | php
RUN			mv composer.phar /usr/local/bin/composer