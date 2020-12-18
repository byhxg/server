-- 获取动态负载,务逻辑，在/25/init.lua中增加
local flag=ngx.shared.load_25:get("load_25")
local load_blance=''
if tonumber(flag) == 1 then
    load_blance="upstream_server_25"
elseif tonumber(flag) == 2 then
    load_blance="upstream_server_25"
else
    load_blance="upstream_server_25"
end

return load_blance