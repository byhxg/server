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

##使用方法
删除单个:
```
curl -XDELETE 'http://192.169.1.666:9200/index
#你也可以这样删除多个索引：
DELETE /index_one,index_two
curl -XDELETE 'http://192.169.1.666:9200/index_one,index_two
DELETE /index_*
curl -XDELETE 'http://192.169.1.666:9200/index_*
```

删除 全部 索引(强烈不建议)：
```
DELETE /_all
curl -XDELETE 'http://192.169.1.666:9200/_all
DELETE /*
curl -XDELETE 'http://192.169.1.666:9200/*
````

删除全部索引操作非常危险，禁止措施

elasticsearch.yml 做如下配置：
action.destructive_requires_name: true

这个设置使删除只限于特定名称指向的数据, 而不允许通过指定 _all 或通配符来删除指定索引库