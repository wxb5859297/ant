一. 此框架命名为ant，简易快速开发；
二. 框架结构如下：
    1. ant.php 框架主文件，启动框架需要加载该文件;
    2. antc.php 框架资源控制器，所有的业务逻辑代码会在该层展示；
    3. antr.php 框架过滤器，get/post/cookie会被封装过滤，获取值需要根据antr来获取；
    4. ante.php 框架错误控制器，所有的错误均为封装在此类中；
    5. antl.php 框架加载控制器，所有代码中禁止使用include、require字样，需要加载文件需要使用antl来加载；如果开启自动加载，则不需要关心该控制器；
    6. antp.php 框架模板控制器，模板加载需要通过该类来加载，默认会加载当前模板；
    7. antconst.php 框架常量定义；
    8. antinstall.php 框架安装脚本；
三. 框架命名规范：
    1. 变量命名：小写字母加下划线，如：$user_id,$user_name;
    2. 方法命名：小子字母加驼峰命名规则，如：getUserById,getUserByName;
    3. 类名命名：根据某模块下命名，如rs资源模块下的user用户模块的help帮助，命名为class rs_user_help extends antc{} ，而它的过滤模块应该是class request_user_help extends antr{}
    4. 业务代码变量命名：禁止使用$a,$b,此类无意义变量，应当使用有意义变量，如表示用户余额$user_balance；
四. 业务代码编写规则：
    1. 所有的过滤均由上层的控制器进行过滤，切忌编写业务代码；
    2. 业务代码均放在model层;
五. todo
    0. 修复框架$this->noView()的bug,当控制器使用$this->noView时，控制器中有输出还是会正常输出;  OK
    1. 错误控制器（ante）完善，将debug状态下所有所有错误都做展示，分析各个功能执行性能状态，此类将会更名为监控控制器
    2. 增加驱动控制器（antd.php），驱动控制器专门用来控制分析各个db、cache执行状态及性能；
    3. cache层分离，增加缓存控制器（antcache.php）,单独用来处理缓存模块
    4. 规范化ant核心类变量、方法；
    5. include_once/requrie_once.需要进行重写，将所有只进行一次加载。单独使用include来实现，不使用include_once  OK  autoload 能够自行加载一次，不需要判断
    6. 调试信息增加debug参数，通过debug=1就能查看调用栈使用情况
    7. 编写lib目录下面的工具类，封装代码；如db类；
    8. 增加python脚本对框架及业务代码进行全面扫描，除去危险代码。
    9. 该版本会增加很多lib库
六. 注意事项
    1. 必须为php5以上版本；
    2. 原生框架对url支持不好，如果需要对seo支持的url可以对url进行重写；
        如http://www.xxx.com/?rs=user&act=help 可以重写为http://www.xxx.com/user/help
七. 心得
    1. 最近使用了zf，感觉封装了太强大了，使得我们都不得知里面的东西，例如sql，太过笨重，当某些业务因为sql问题引起时，很难发现原因，因此尽量保证关键地方完全自己手写测试通过。
