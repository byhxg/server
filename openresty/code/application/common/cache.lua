--https://github.com/thibaultcha/lua-resty-mlcache
local mlcache = require "resty.mlcache"
local common = require "resty.common"
local template = require "resty.template"

template.render("index.html",{
    title = "peter的商城",
    category = {"首页","团购促销","名师荟萃","艺品驿站","欧式摆件"}
})

--url匹配
local key=ngx.re.match(ngx.var.request_uri,"/([0-9]+).html")
local id = key[1];
--ngx.say('访问来源',ngx.var.remote_addr)
--L3 的回调
local function fetch_shop(id)
    ngx.say(id,' commom/cache.lua',' 请求到L3了')
    -- 布隆过滤器 (相当于白名单)
    if (common.filter('shop_list',id) == 1 ) then
        local content=common.send('/index.php')
        if content==nil then
            ngx.say('无数据返回')
            return
        end
        return content
    end
    ngx.say('布隆过滤器：无访问权限')
    return
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
    ngx.say('等级',level)
    if level == 3 then
        ngx.say('等级3|正在设置',shop_detail)
        -- 随机种子，所有的key不在同一个时间失效(避免大规模缓存过期)
        math.randomseed(tostring(os.time()))
        local expire_time = math.random(1,6);
        cache:set(id, {ttl=expire_time}, shop_detail)
    end
--    ngx.say(shop_detail,level);
end



