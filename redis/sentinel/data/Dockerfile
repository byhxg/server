FROM  alpine
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories \
  && apk update && apk add  gcc g++ libc-dev  wget vim  openssl-dev make  linux-headers  bash \
  && rm -rf /var/cache/apk/*

#通过选择更小的镜像，删除不必要文件清理不必要的安装缓存，从而瘦身镜像
#创建相关目录能够看到日志信息跟数据跟配置文件
RUN mkdir -p /usr/src/redis \
      && mkdir -p /usr/src/redis/data \
      && mkdir -p /usr/src/redis/conf \
      && mkdir -p /usr/src/sh         \
      && mkdir -p /usr/src/module     \
      && mkdir -p /usr/src/redis/log


RUN wget  -O /usr/src/redis/redis-5.0.7.tar.gz  "https://github.com/antirez/redis/archive/5.0.7.tar.gz" \
   && tar -xzf /usr/src/redis/redis-5.0.7.tar.gz  -C /usr/src/redis \
   && cp  /usr/src/redis/redis-5.0.7/src/redis-trib.rb  /usr/src/redis/ \
   && rm -rf /usr/src/redis/redis-5.0.7.tar.tgz

RUN cd /usr/src/redis/redis-5.0.7 &&  make && make PREFIX=/usr/local/redis install \
&& ln -s /usr/local/redis/bin/*  /usr/local/bin/  && rm -rf /usr/src/redis/redis-5.0.7

COPY ./module/   /usr/src/module/RedisBloom-2.0.3/
RUN  cd  /usr/src/module/RedisBloom-2.0.3 &&  make

EXPOSE 6379
CMD ["redis-server","/redis/conf/redis.conf","--loadmodule", "/usr/src/module/RedisBloom-2.0.3/redisbloom.so"]