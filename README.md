抄袭几个框架，组合成小众框架:nffq
参考框架：Yii，kcmvc，知心框架,CI
总体目标：
1，支持web路由
    1.1 使用smatry引擎
    1.2 使用mysql 
    1.3 集成邮件功能
2，支持cmd启动
4，支持cache：redis
5，设计的时候，预留出钩子


入口文件为：nffq.php

路由规则： 通过"_"来分割路径
路由分发：

数据库层：封装成一个统一的接口，下层可以是不同的db引擎。
        目前支持mysql

配置文件，分成系统配置。应用配置分为test/dev/pro三等

邮件使用phpmailer
提供对称加密/解密,64位整型签名
提供JSONAPI
自动加载，
数据层封装
提供性能分析时间消耗统计

目录结构:
nffq:
    index.php
    system:
        nffq.php
        core:
            env.class.php
            app.class.php
        web:
        cmd:
        conf：
        componse:       //一些扩展
            time.class.php
        libs:
    app:        //具体应用


