#!/bin/bash
#docker run --rm  -p 9301:9200 -e ES_JAVA_OPTS="-Xms512m -Xmx512m"  -e "xpack.security.enabled=false" docker.elastic.co/elasticsearch/elasticsearch:5.6.2
#####构建脚本
systemctl restart docker
#serv=("mysql/5.7" "consul" "nginx" "openresty" "php/php73" "redis/redis-cluster-5.0-bulong")
serv=("mysql/5.7" "consul" "openresty" "php/php73" "redis/redis-cluster-5.0-bulong")
path=$(pwd)

for i in ${serv[@]}; do
#    num=`ps -fe | grep $i | grep -v grep | wc -l`
#    if [ $num -eq 0 ]
#    then
        echo "构建中 $i ......"
        cd $path/$i && docker-compose up -d >> /tmp/docker.log &
        sleep 25;
#    else
#        echo "$i runing......"
#    fi
done
