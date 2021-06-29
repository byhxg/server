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