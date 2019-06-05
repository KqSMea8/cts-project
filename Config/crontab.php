<?php
/*
 * 定时任务配置文件, 格式: 启动时间 脚本类型 脚本路径 进程数 版本号
 * 
 * 启动时间格式与Linux Crotab 格式一致: 分 时 日 月 周
 * 
 * 版本号可以用任意数字串表示, 不能包含除了数字和点之外的其他字符.
 * 
 * 以下操作会导致某个进程重启:
 * 
 * 1. 修改某个任务的版本号.
 * 2. 修改某个任务的进程数.
 * 
 * TODO: 支持全量重启 
 * 
 */

return array(

    "* * * * * php Cron/Token/Refresh.php  1  1.0",//tauth token 刷新

    "* * * * * php Cron/Product/Check.php  1 1.0", //商品检查自动上架
    "* * * * * php Cron/Banner/Del.php  1 1.0", //banner自动下架
    "* * * * * php Cron/Product/Redis.php    3   1.1",//每分钟统计商品访问记录


    "00 */2 * * * php Cron/Order/Message.php    1   1.4",//未填写收货地址私信提醒
    "*/5 * * * * php Cron/Order/Fix.php    1   1.0",//订单修正
    "* * * * * php Cron/Order/Overdue.php    1   1.0",//每分钟订单超过30天过期



    "*/5 * * * * php Cron/Dxj/Fix.php               1   1.0",//订单修正
    "* * * * * php Cron/Dxj/Pay2validate.php        1   1.1",//送去安全部门校验
    "*/5 * * * * php Cron/Dxj/Validate.php          1   1.",//安全部门校验结果
    "* * * * * php Cron/Dxj/Bonus.php               1   1.1",//发红包
    "*/5 * * * * php Cron/Dxj/BonusFix.php          1   1.1",//红包修正
    "35 * * * * php Cron/Dxj/Verify.php             1   1.0",//积分核销




    "00 */2 * * * php Cron/Product/Stock.php  1 1.0", //商品库存预警


    "* * * * * php Cron/TemplateMsgSend.php  1 1.0", //发私信


    "30 03 * * * php Cron/Stats/Daily.php  1  1.2",//日报
    "00 03 * * * php Cron/Stats/Dailytotal.php  1  1.2",//日报 历史数据
    "00 02 * * * php Cron/Stats/Dailyuser.php  1  1.2",//日报 新用户
    "00 05 * * * php Cron/Stats/Dailydata.php  1  1.1",//日报 读取生成文件入数据库

    "00 04 * * 5 php Cron/Stats/Week/User.php  1  1.1",//周报 新用户
    "30 04 * * 5 php Cron/Stats/Week/Stats.php  1  1.2",//周报


    "10 05 * * * php Cron/Dxj/Stats/User.php  1  1.0",//兑红包日报 新用户
    "30 05 * * * php Cron/Dxj/Stats/Total.php  1  1.0",//兑红包日报 历史数据
    "00 06 * * * php Cron/Dxj/Stats/Daily.php  1  1.0",//兑红包 日报
    "30 06 * * * php Cron/Dxj/Stats/Data.php  1  1.0",//兑红包日报 读取生成兑文件入库

    "00 07 * * 5 php Cron/Dxj/Stats/Week/User.php  1  1.0",//兑红包周报 新用户
    "30 07 * * 5 php Cron/Dxj/Stats/Week/Stats.php  1  1.0",//兑红包周报



    "30 01 * * * php Cron/ApiData/Bonus.php  1  1.1",//新统计需求--兑红包统计
    "30 01 * * * php Cron/ApiData/Exchange.php  1  1.1",//新统计需求--兑换统计

    "00 01 * * * sh Shell/rsync_data.sh  1  1.1",//日志推送

    "30 02 * * * php Cron/Dxj/Stats/Money.php  1  1.0",//兑红包分金额统计订单

    "* * * * * php Cron/Push/Msg.php  1 1.0", //小喇叭

    "* * * * * php Cron/Luck/AccountLog.php  1 1.0", //推送消耗积分

    "00 */1 * * * php Cron/Product/Guess.php    1   1.0",//推荐商品到大首页
);
