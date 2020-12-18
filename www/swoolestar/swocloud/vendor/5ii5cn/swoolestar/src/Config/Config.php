<?php

namespace SwooleStar\Config;

class Config
{

    protected $itmes = [];

    protected $configPath = '';

    function __construct()
    {
        $this->configPath = APP_PATH.'/config';

        // 读取配置
        $this->itmes = $this->phpParser();
        // dd($this->itmes);
    }
    /**
     * 读取PHP文件类型的配置文件
     * 六星教育 @shineyork老师
     * @return [type] [description]
     */
    protected function phpParser($path='')
    {
        // 1. 找到文件
        if(!$path){
            $path = $this->configPath;
        }
        $files = scandir($path);
        $data = null;
        // 2. 读取文件信息
        foreach ($files as $key => $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            // 2.1 获取文件名
            $filename = \stristr($file, ".php", true);
            // 2.2 读取文件信息
            $data[$filename] = include $this->configPath."/".$file;

//                $file = $path . DIRECTORY_SEPARATOR . $file;
//                if (is_dir($file)) {
//                    // 利用递归把子文件也一并的写入
//                    $this->phpParser($file);
//                }

        }

        // 3. 返回
        return $data;
    }
    // key.key2.key3
    public function get($keys)
    {
        $data = $this->itmes;
        foreach (\explode('.', $keys) as $key => $value) {
            $data = $data[$value];
        }
        return $data;
    }
}
