--定时器 https://github.com/openresty/lua-nginx-module
ngx.log(ngx.ERR, '----------init.lua')
local delay = 5
local handler
handler = function (premature)
    ngx.log(ngx.ERR, "++运行定时器")
    local resty_consul = require('resty.consul')
    local consul = resty_consul:new({
        host = "192.168.5.51",
        port = 8500,
        connect_timeout = (60 * 1000), -- 60s
        read_timeout = (60 * 1000), -- 60s
        default_args = {
        token = "my-default-token"
        },
        ssl = false,
        ssl_verify = true,
        sni_host = nil,
    })
    --匹配redis-cluster前缀
    local res, err = consul:list_keys('redis-cluster') -- Get all keys
    --local res, err = consul:list_keys() -- Get all keys
    if not res then
        ngx.log(ngx.ERR, err)
        return
    end

    local keys = {}
    if res.status == 200 then
        keys = res.body
    end

    --引入分隔函数
    local ngx_re_split = require("ngx.re").split

    local ip_addr = '';
     for key, value in ipairs(keys) do
        --    获取value值
        local res, err = consul:get_key(value)
        if not res then
            ngx.log(ngx.ERR, err)
            return
        end

        --如果最后一个不适用逗号分隔
        if table.getn(keys)==key then
            ip_addr = ip_addr..res.body[1].Value
        else
            ip_addr = ip_addr..res.body[1].Value..','
        end
    end
    --写入到内存中 nginx.conf 中声明
    --server\openresty\conf\nginx\application\nginx.conf:35
    ngx.shared.redis_cluster_addr:set('redis-addr',ip_addr)

end


if( 0== ngx.worker.id() ) then
    --第一次立即执行
    local ok, err = ngx.timer.at(0, handler)
    if not ok then
        ngx.log(ngx.ERR, "failed to create the timer: ", err)
        return
    end

    --第二次定时执行、使用every函数
    local ok, err = ngx.timer.every(delay, handler)
    if not ok then
        ngx.log(ngx.ERR, "failed to create the timer: ", err)
        return
    end
end


