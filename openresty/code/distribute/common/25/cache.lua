--https://github.com/thibaultcha/lua-resty-mlcache
local mlcache = require "resty.mlcache"
--url匹配
local key=ngx.re.match(ngx.var.request_uri,"/([0-9]+).html")
local id = key[1];
--ngx.say('访问来源',ngx.var.remote_addr)
--L3
local function fetch_shop(id)
    ngx.say(id,'=========')
    --    ngx.log(ngx.ERR,'请求到L3了')
    return 'id=1'
end


if type(key) == 'table' then
    local cache,err = mlcache.new('caceh_name','my_cache',{
        lru_size = 500, --  设置的缓存的个数
        ttl = 5, --缓存过期时间
        neg_ttl =6,--L3返回nil的保存时间
        ipc_shm ='ipc_cache' --用于将L2的缓存设置到L1
    })

    if not cache then
        ngx.log(ngx.ERR,'缓存创建失败',err)
    end
    --内容，错误信息， level 等级
    local shop_detail, err,level = cache:get(id,nil,fetch_shop,id)

--    if not shop_detail then
--        cache:set(id, nil, shop_detail)
--    end
    if level == 3 then
        ngx.say('等级3|正在设置',shop_detail)
        -- 随机种子，所有的key不在同一个时间失效(避免大规模缓存过期)
        math.randomseed(tostring(os.time()))
        local expire_time = math.random(1,6);
        cache:set(id, {ttl=expire_time}, shop_detail)
    end
--    ngx.say(shop_detail,level);
end






