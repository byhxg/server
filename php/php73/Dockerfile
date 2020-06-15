#docker build -t  php73 . --nocache
FROM php:7.3-fpm-alpine

# Version
ENV PHPREDIS_VERSION 4.0.0
ENV SWOOLE_VERSION 4.4.12
ENV KAFKA_VERSION 4.0.2

# Libs
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
RUN apk add wget
RUN apk add gcc
RUN apk add g++
RUN apk add libc-dev
RUN apk add zlib
RUN apk add wget
RUN apk add vim
RUN apk add openssl-dev
RUN apk add make
RUN apk add unzip
RUN apk add bash
RUN apk add git
RUN apk add make
RUN apk add autoconf
RUN rm -rf /var/cache/apk/*

#RUN curl -sS https://getcomposer.org/installer | php \
#    && mv composer.phar /usr/local/bin/composer \
#    && composer self-update --clean-backups

RUN wget https://install.phpcomposer.com/composer.phar && mv composer.phar /usr/local/bin/composer

# docker方式安装PDO extension
RUN docker-php-ext-install pdo_mysql

# docker方式安装pcntl extension
RUN docker-php-ext-install pcntl

RUN wget https://github.com/swoole/swoole-src/archive/v${SWOOLE_VERSION}.tar.gz -O swoole.tar.gz \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && rm swoole.tar.gz \
    && ( \
        cd swoole \
        && phpize \
        && ./configure  --enable-mysqlnd  --enable-openssl  --enable-http2 \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r swoole \
    && docker-php-ext-enable swoole

########################### swoole 异步扩展
RUN wget https://github.com/swoole/ext-async/archive/v4.4.12.zip -O /tmp/swoole_asvnc.zip && cd /tmp &&  unzip swoole_asvnc.zip && cd ext-async-4.4.12 \
    && phpize  \
    &&./configure \
    && make -j 4 \
    &&  make install \
    && rm /tmp/swoole_asvnc* \
    && docker-php-ext-enable swoole_async

#切换镜像
RUN chmod 755 /usr/local/bin/composer && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/

# Redis extension
RUN wget http://pecl.php.net/get/redis-${PHPREDIS_VERSION}.tgz -O /tmp/redis.tar.tgz \
    && pecl install /tmp/redis.tar.tgz \
    && rm -rf /tmp/redis.tar.tgz \
    && docker-php-ext-enable redis
 # 修改php.ini的文件 extension=redis.so

########################## kafka memcache
# rdkafka extension
RUN wget https://github.com/edenhill/librdkafka/archive/v1.3.0.zip -O /tmp/librdkafka.zip && cd /tmp && unzip librdkafka.zip  && cd librdkafka-1.3.0 && ./configure && make && make install && rm /tmp/librdkafka.zip
RUN wget http://pecl.php.net/get/rdkafka-${KAFKA_VERSION}.tgz -O /tmp/rdkafka.tar.tgz \
    && pecl install /tmp/rdkafka.tar.tgz \
    && rm -rf /tmp/rdkafka.tar.tgz \
    && docker-php-ext-enable rdkafka
# 修改php.ini的文件 extension=rdkafka.so

RUN apk add zlib-dev
RUN apk add libmemcached-dev
RUN pecl install memcache-4.0.5.2
RUN pecl install memcached-3.1.5
RUN docker-php-ext-enable memcache && docker-php-ext-enable memcached

RUN docker-php-ext-install -j$(nproc) bcmath calendar exif sockets dba shmop sysvmsg sysvsem sysvshm iconv  mysqli

# xdebug  扩展
RUN cd /tmp/ && wget http://xdebug.org/files/xdebug-2.7.2.tgz &&  pecl install xdebug-2.7.2.tgz && rm xdebug-2.7.2.tgz &&  docker-php-ext-enable xdebug