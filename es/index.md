##安装集群注意
1、给目录777的权限
```
[root@localhost opt]# chmod -R 777 es
[root@localhost opt]# ll es
总用量 16
-rwxrwxrwx. 1 root root 1945 7月   2 08:04 docker-compose.yml
-rwxrwxrwx  1 root root  761 7月   2 08:12 index.md
drwxrwxrwx  4 root root   32 6月  30 20:43 master_10
-rwxrwxrwx  1 root root  945 9月  26 2020 river.toml
-rwxrwxrwx  1 root root  934 9月  26 2020 rivert.toml
drwxrwxrwx  4 root root   32 6月  30 20:43 slave_11
drwxrwxrwx  4 root root   32 6月  30 20:43 slave_12
```
2、调整系统配置
```
执行命令：
sysctl -w vm.max_map_count=262144
查看结果：
sysctl -a|grep vm.max_map_count
显示：
vm.max_map_count = 262144
上述方法修改之后，如果重启虚拟机将失效，所以：
解决办法：
在  /etc/sysctl.conf文件最后添加一行
vm.max_map_count=262144
```

##elasticsearch-head使用说明
###一、概念介绍
* 节点

  > 安装ES时，创建的ES实例就是节点，集群中部署了几个ES实例，就会有几个节点。
单机部署为一个节点，集群部署时，节点数通常为2n+1个，意思是奇数个节点。
* 索引
  >ES中索引是指的逻辑存储，对应关系型数据库中“数据库”；索引类型对应 关系型数据库的“表”；
文档 对应关系型数据中“记录”。
* 分片
  >ES可以将索引细分在不同的分片上，分片根据一定的算法分布在不同的节点上；
  >ES创建索引时，默认主分片为5，并自动为每个主分片创建副本分片；
单节点部署时，副本分片均为unassigned状态，健康状态为 yellow。

* 集群健康度
    > 1、 green
     所有的主分片和副本分片都已分配。你的集群是 100% 可用的。
    
    > 2、yellow
    所有的主分片已经分片了，但至少还有一个副本是缺失的。不会有数据丢失，所以搜索结果依 然是完整的。不过，你的高可用性在某种程度上被弱化。如果 更多的 分片消失，你就会丢数据了。把 yellow 想象成一个需要及时调查的警告。
    
    > 3、red
    至少一个主分片（以及它的全部副本）都在缺失中。这意味着你在缺少数据：搜索只能返回部分数据，而分配到这个分片上的写入请求会返回一个异常。



##使用方法
* 删除单个:
```
curl -XDELETE 'http://192.169.1.666:9200/index
#你也可以这样删除多个索引：
DELETE /index_one,index_two
curl -XDELETE 'http://192.169.1.666:9200/index_one,index_two
DELETE /index_*
curl -XDELETE 'http://192.169.1.666:9200/index_*
```

* 删除 全部 索引(强烈不建议)：
```
DELETE /_all
curl -XDELETE 'http://192.169.1.666:9200/_all
DELETE /*
curl -XDELETE 'http://192.169.1.666:9200/*
````

* 删除全部索引操作非常危险，禁止措施

```
elasticsearch.yml 做如下配置：
action.destructive_requires_name: true
这个设置使删除只限于特定名称指向的数据, 而不允许通过指定 _all 或通配符来删除指定索引库
```
