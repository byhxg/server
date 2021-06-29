##一、go-mysql-elasticsearch 安装

本文深入详解了插件的安装、使用、增删改查同步测试。
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

2.2执行同步操作
cd $GOPATH/src/github.com/siddontang/go-mysql-elasticsearch
./bin/go-mysql-elasticsearch -config=./etc/river.toml
