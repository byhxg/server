--进程启动触发
ngx.log(ngx.ERR,"25/init进程启动、设置切换标志，从consul中获取")
local delay = 5

local handler25
handler25 = function (premature)
    local resty_consul = require('resty.consul')
    local consul = resty_consul:new({
        host = "192.168.5.51",
        port = 8500,
        connect_timeout = (60*1000), -- 60s
        read_timeout    = (60*1000), -- 60s
    })
    local res, err = consul:get_key("load_25") --获取value值
    if not res then
        ngx.log(ngx.ERR,'25/init.lua', err)
        return
    end
    ngx.log(ngx.ERR, "获取到是否要切换的标记",res.body[1].Value)
    ngx.shared.load_25:set('load_25',res.body[1].Value)
end

if  0 == ngx.worker.id() then
    --第一次立即执行
    local ok, err = ngx.timer.at(0, handler25)
    if not ok then
        ngx.log(ngx.ERR, "failed to create the timer: ", err)
        return
    end

    --第二次定时执行
    local ok, err = ngx.timer.every(delay, handler25)
    if not ok then
        ngx.log(ngx.ERR, "failed to create the timer: ", err)
        return
    end
    ngx.log(ngx.ERR,"-----进程启动")
end

