#!/bin/sh
echo "
#cluster-announce-ip  $REALIP
#cluster-announce-port $PORT
#cluster-announce-bus-port $PORT2
" >> /usr/src/redis/conf/redis.conf


#支持集群方式
sed -i "s/#cluster-enabled yes/cluster-enabled yes/g" /usr/src/redis/conf/redis.conf
sed -i "s/bind 0.0.0.0/bind 0.0.0.0/g" /usr/src/redis/conf/redis.conf

redis-server /usr/src/redis/conf/redis.conf


