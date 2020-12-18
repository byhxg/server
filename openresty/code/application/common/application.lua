--获取getpost
--local args = ngx.req.get_uri_args();
--local act=args["act"]
--ngx.print(act);

--引入分隔函数
local ngx_re_split = require("ngx.re").split
-- 从内存中获取
local appAddrStr = ngx.shared.redis_cluster_addr:get('redis-addr')
local ip_addrs = ngx_re_split(appAddrStr, ",")

local redis_addr = {}
--192.168.0.88:6388,192.168.0.88:6389
ngx.log(ngx.ERR, '从内存中获取redis节点信息 === ',appAddrStr)

for _, value in ipairs(ip_addrs) do
--    使用 ：分隔数据
    local ip_addr = ngx_re_split(value, ":")
-- 在末尾插入
    table.insert(redis_addr,{ ip = ip_addr[1], port = ip_addr[2] })
end

local config = {
    name = "testCluster", --rediscluster name
    serv_list=redis_addr,
    keepalive_timeout = 60000, --redis connection pool idle timeout
    keepalive_cons = 1000, --redis connection pool size 连接池个数
    connection_timout = 1000, --timeout while connecting
    max_redirection = 5, --maximum retry attempts for redirection
    auth = '123456'
}

local redis_cluster = require"rediscluster"
local red_c = redis_cluster:new(config)

local id = ngx.var.key
local v, err = red_c:get(id)
if err then
    ngx.log(ngx.ERR, "redis 连接失败: ", err)
end

if v == ngx.null or v== nil then
    ngx.log(ngx.ERR,'缓存不存在，请求php文件 ',id)
else
    ngx.say(ngx.ERR,'从缓存中获取到值 ',v)
    return
end

---子请求
res = ngx.location.capture("/index.php",{method=ngx.HTTP_GET,body="name=test",args={id=id,server_addr=ngx.var.remote_addr,server_uri=ngx.var.request_uri}})
for key,val in pairs(res) do
    if type(val) == "table" then
        ngx.log(ngx.ERR,key,"=>",table.concat(val,","))
        ngx.say(key,key,"=>",table.concat(val,","))
    else
        ngx.say(key,"=>>",val)
        ngx.log(ngx.ERR,key,"=>>",val)
    end
end
