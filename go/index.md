##go-mysql-elasticsearch 安装

* go 同步mysql数据至 es
```
1）  读取mysql的binlog日志，获取指定表的日志信息；
2）  将读取的信息转为MQ；
3）  编写一个MQ消费程序；
4）  不断消费MQ，每消费完一条消息，将消息写入到ES中。
```

优点：
> 没有代码侵入、没有硬编码；
原有系统不需要任何变化，没有感知；
性能高；业务解耦，不需要关注原来系统的业务逻辑。

缺点：
> 构建Binlog系统复杂；




* 本文深入详解了插件的安装、使用、增删改查同步测试。
1. go-mysql-elasticsearch 插件安装
步骤1：安装go
yum install go

步骤2：安装godep
go get github.com/tools/godep

步骤3：获取go-mysql-elastisearch插件
go get github.com/siddontang/go-mysql-elasticsearch

步骤4：安装go-mysql-elastisearch插件
cd $GOPATH/src/github.com/siddontang/go-mysql-elasticsearch
make

2.go-mysql-elasticsearch 插件使用
2.1修改配置文件
```
#mysql配置
my_addr = "192.168.40.103:3307"
my_user = "root"
my_pass = "123456"
my_charset = "utf8"

# Set true when elasticsearch use https
#es_https = false
# Elasticsearch address
es_addr = "192.168.40.8:9200"
# Elasticsearch user and password, maybe set by shield, nginx, or x-pack
es_user = ""
es_pass = ""

# Path to store data, like master.info, if not set or empty,
# we must use this to support breakpoint resume syncing.
# TODO: support other storage, like etcd.
data_dir = "./var"

# Inner Http status address
stat_addr = "192.168.40.103:12800"
stat_path = "/metrics"

# pseudo server id like a slave
server_id = 1001

# mysql or mariadb
flavor = "mysql"

# mysqldump execution path
# if not set or empty, ignore mysqldump.
mysqldump = "mysqldump"

# if we have no privilege to use mysqldump with --master-data,
# we must skip it.
#skip_master_data = false

# minimal items to be inserted in one bulk
bulk_size = 128

# force flush the pending requests if we don't have enough items >= bulk_size
flush_bulk_time = "200ms"

# Ignore table without primary key
skip_no_pk_table = false

# MySQL data source
[[source]]
schema = "xiu_user"

# Only below tables will be synced into Elasticsearch.
# "t_[0-9]{4}" is a wildcard table format, you can use it if you have many sub tables, like table_0000 - table_1023
# I don't think it is necessary to sync all tables in a database.
tables = ["user_base_info_[0-9]{2}"]


# Wildcard table rule, the wildcard table must be in source tables
# All tables which match the wildcard format will be synced to ES index `test` and type `t`.
# In this example, all tables must have same schema with above table `t`;
[[rule]]
schema = "xiu_user"
table = "user_base_info_[0-9]{2}"
index = "xiu_user"
```

### mysql 
查看binlog日志是否开启，开启之后显示ON
show variables like 'log_bin'; 

编辑mysql的/etc/my.cnf文件，在[mysqld]添加以下
log_bin = mysql-bin
binlog_format = ROW
expire-logs-days = 7
max-binlog-size = 500M
# 需要注意的是MySQL 5.7以下要加上server-id，数字任意，5.7以上无需此配置。此外log_bin的位置如果未配环境变量，需要固定，即log_bin=/var/lib/mysql/mysql-bin,否则会报错

重启MySQL
systemctl restart mysqld 
或者
service mysqld restart

查看日志状态 日志已开启为ROW模式
show variables like 'binlog_format%';

赋予账户日志权限两种方式：
创建新用户并赋权

    -- % 代表是所有ip,即可以远程控制，localhost就是只能本地连接
    create user 'hhy'@'%' IDENTIFIED BY '123';
    FLUSH PRIVILEGES;
    GRANT SELECT, SHOW VIEW, REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO 'hhy'@'%' IDENTIFIED BY '123';
    FLUSH PRIVILEGES;

或者直接给已存在的用户赋权

    GRANT SELECT, SHOW VIEW, REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO 'hhy'@'%' IDENTIFIED BY '123';
    FLUSH PRIVILEGES;

查看用户权限
    show grants for 'hhy'@'%';

测试是否赋权成功
登录已赋权的账户，运行
show binary logs;

密码过于简单或者长度不够，可以运行，更改密码长度
    set global validate_password_length=6;
    flush privileges;

更改用户密码
    update mysql.user set authentication_string=password('123') where user='root';
    flush privileges;

2.2执行同步操作
cd $GOPATH/src/github.com/siddontang/go-mysql-elasticsearch
./bin/go-mysql-elasticsearch -config=./etc/river.toml

