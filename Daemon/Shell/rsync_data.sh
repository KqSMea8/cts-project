#!/bin/sh
# Author cts(haoman@staff.weibo.com)
# 日志推送脚本

#数据存放根目录
base_path='/data1/www/data/exchange.sc.weibo.com'

#当天推送地址前一天日期存放
data_path=`date -d yesterday +%Y%m`"/"`date -d yesterday +%d`

#日志存放目录
log_path="${base_path}/log"
log_file=`date -d yesterday +%Y%m%d`".log"
#密钥存放文件
rsync_pass_push="/data1/www/data/exchange.sc.weibo.com/exchange.rs.pass"

#准备推送的数据存放文件
now_date=`date -d yesterday +%Y%m%d`

rsync_ip='nGtFOG0z@r2.data.sina.com.cn'

function check_dir() {
    if [ ! -d $1 ];
    then
        mkdir -p $1
        chmod 755 $1
    fi
}

function check_rsync_pass() {
    if [ ! -f $1 ];
    then
        touch $1
        chmod 600 $1
        echo $2 >> $1
    fi
}

check_dir "$log_path"
check_rsync_pass "$rsync_pass_push" "jb4JH0u8pRN+ZfPoQBvdu8sF09cFg8ottrKFUFcDSsY="

echo "-------------------------------  查询数据库创建文件开始 ------------------------------" >> ${log_path}/${log_file}
#积分兑换商品日志
/usr/local/sinasrv2/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Product.php

#仿真执行用这个
#/usr/local/php7/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Product.php

#积分兑换订单日志
/usr/local/sinasrv2/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Order.php

#仿真执行用这个
#/usr/local/php7/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Order.php

#积分兑红包订单日志
/usr/local/sinasrv2/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Bonus.php

#仿真执行用这个
#/usr/local/php7/bin/php /data1/www/htdocs/exchange.sc.weibo.com/Daemon/Cron/PushData/Bonus.php

echo "-------------------------------  查询数据库创建文件结束 ------------------------------\r\n" >> ${log_path}/${log_file}


echo "-------------------------------  开始推送积分兑换商品日志 ------------------------------" >> ${log_path}/${log_file}
product_target_path="${base_path}/product/${data_path}"
#product_result_name="exchange_product_${now_date}.txt"
product_rsync_ip='nGtFOG0z@r2.data.sina.com.cn'
product_rsync_mode_push='weibo_bound_exchange_product_343520'
product_rsync_path="/usr/bin/rsync -avP --append --timeout=180 --password-file=${rsync_pass_push}"

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
${product_rsync_path} ${product_target_path} ${rsync_ip}::${product_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
if [ $? -eq 0 ];
then
    echo "传输文件成功" >> ${log_path}/${log_file}
    #删除前天的数据
    data_ago_path=$(date +%Y%m -d 'last month' )"/"$(date +%d -d 'last month' )
    rm -rf ${base_path}/product/${data_ago_path}

else
    echo "传输文件失败" >> ${log_path}/${log_file}
    ${product_rsync_path} ${product_target_path} ${rsync_ip}::${product_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
fi

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
echo "-------------------------------  推送结束积分兑换商品日志 ------------------------------\r\n" >> ${log_path}/${log_file}



echo "-------------------------------  开始推送积分兑换订单日志 ------------------------------" >> ${log_path}/${log_file}
order_target_path="${base_path}/order/${data_path}"
#order_result_name="exchange_order_${now_date}.txt"
order_rsync_mode_push='weibo_bound_exchange_order_343516'
order_rsync_path="/usr/bin/rsync -avP --append --timeout=180 --password-file=${rsync_pass_push}"

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
${order_rsync_path} ${order_target_path} ${rsync_ip}::${order_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
if [ $? -eq 0 ];
then
    echo "传输文件成功" >> ${log_path}/${log_file}
    #删除前天的数据
    data_ago_path=$(date +%Y%m -d 'last month' )"/"$(date +%d -d 'last month' )
    rm -rf ${base_path}/order/${data_ago_path}

else
    echo "传输文件失败" >> ${log_path}/${log_file}
    ${order_rsync_path} ${order_target_path} ${rsync_ip}::${order_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
fi

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
echo "-------------------------------  推送结束积分兑换订单日志 ------------------------------\r\n" >> ${log_path}/${log_file}



echo "-------------------------------  开始推送积分兑换红包日志 ------------------------------" >> ${log_path}/${log_file}
bonus_target_path="${base_path}/bonus/${data_path}"
#bonus_result_name="exchange_bonus_${now_date}.txt"
bonus_rsync_mode_push='weibo_bound_exchange_bonus_343538'
bonus_rsync_path="/usr/bin/rsync -avP --append --timeout=180 --password-file=${rsync_pass_push}"

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
${bonus_rsync_path} ${bonus_target_path} ${rsync_ip}::${bonus_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
if [ $? -eq 0 ];
then
    echo "传输文件成功" >> ${log_path}/${log_file}
    #删除前天的数据
    data_ago_path=$(date +%Y%m -d 'last month' )"/"$(date +%d -d 'last month' )
    rm -rf ${base_path}/order/${data_ago_path}

else
    echo "传输文件失败" >> ${log_path}/${log_file}
    ${bonus_rsync_path} ${bonus_target_path} ${rsync_ip}::${bonus_rsync_mode_push}/`date -d yesterday +%Y%m`/ >> ${log_path}/${log_file}
fi

echo `date +'%Y-%m-%d %H:%M:%S'` >> ${log_path}/${log_file}
echo "-------------------------------  推送结束积分兑换红包日志 ------------------------------\r\n" >> ${log_path}/${log_file}