FROM centos

#修改时区
RUN cp /usr/share/zoneinfo/Asia/Shanghai /etc/localtime && echo 'Asia/Shanghai' >/etc/timezone
#添加centos源(先下载wget)
COPY ./epel-7.repo  /etc/yum.repos.d/epel.repo

#安装相关依赖
#RUN yum -y update
RUN yum -y install unzip readline-devel gcc gcc-c++ autoconf automake make net-tools vim zlib zlib-devel openssl* pcre* wget

RUN  mkdir -p /usr/local/openresty/lualib/project && mkdir -p /usr/local/openresty/nginx/conf/user &&  cd /tmp \
 && wget http://labs.frickle.com/files/ngx_cache_purge-2.3.tar.gz \
 && tar -xzvf ngx_cache_purge-2.3.tar.gz \
 && rm -rf ngx_cache_purge-2.3.tar.gz   \

 && wget  https://github.com/weibocom/nginx-upsync-module/archive/v2.1.0.tar.gz \
 && tar -xzvf v2.1.0.tar.gz \
 && rm  -rf   v2.1.0.tar.gz \

 && wget https://openresty.org/download/openresty-1.15.8.2.tar.gz  \
 && tar -xzvf openresty-1.15.8.2.tar.gz \
 && cd openresty-1.15.8.2 \
 && ./configure --prefix=/usr/local/openresty  --with-luajit \
    --add-module=/tmp/nginx-upsync-module-2.1.0 \
 	--add-module=/tmp/ngx_cache_purge-2.3 \
    &&  make &&  make install \

 && wget https://github.com/ledgetech/lua-resty-http/archive/v0.14.zip \
 && unzip v0.14.zip \
 && mv lua-resty-http-0.14/lib/resty/* /usr/local/openresty/lualib/resty

RUN  ln -s /usr/local/openresty/bin/*  /usr/bin

CMD ["openresty","-c","/usr/local/openresty/nginx/conf/nginx.conf"]
