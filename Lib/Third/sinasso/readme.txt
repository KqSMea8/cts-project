1、代码由junjie2@staff，李俊杰维护，任何修订请联系他
2、sinasso相关说明：http://wiki.internal.sina.com.cn/moin/SSO/doc/ssoclient
3、所有文件均转码为utf-8
4、SSOWeiboCooki.php，34行，增加代码：$config = APPPATH.'/config/cookie.conf'; 重新定义cookie.conf的文件位置，适应swift目录结构安排
5、SSOWeiboClient.php，121行，增加两个@避免报错