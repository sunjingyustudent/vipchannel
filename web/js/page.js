/**
 * Created by huangjun on 16/7/3.
 */
var isFullScreen = 0; //是否在全屏状态
$(function () {
    //Page Initialized
    $.ajaxSetup ({ cache: false });
    //判断登录失效
    $(document).ajaxComplete( function(event, jqXHR, options){
        var code = jqXHR.getResponseHeader("Header-Error");
        if(code == 50001) {
            swal({title: "登录失效",
                text: "3秒后跳至登录页面...",
                type: "warning",
                timer: 3000,
                showConfirmButton: false}, function () {
                    $(window).unbind('beforeunload');
                    location.href = '/site/login';
            });
            return false;
        }
    });

    $(".body-chat-home").height(document.body.clientHeight-128);
    $(".body-edit-user").height(document.body.clientHeight-64);
    $(".body-modal").height(document.body.clientHeight-120);
    $(".body-modal-2").height(document.body.clientHeight-120);
    $(".body-modal .body-modal-content").height($(".body-modal").height()-100);
    $(".body-modal-2 .body-modal-content").height($(".body-modal-2").height()-100);
    $(".leftPanel .chat-bar").height(document.body.clientHeight-252);

    if(document.body.clientHeight-128 < 650) {
        $(".rightPanel .initChat img").css('height',document.body.clientHeight-128);
        $(".rightPanel .initChat img").css('line-height',document.body.clientHeight-128);
    }

    $(window).resize(function () {
        $(".body-chat-home").height(document.body.clientHeight-128);
        $(".body-edit-user").height(document.body.clientHeight-64);
        $(".body-modal").height(document.body.clientHeight-120);
        $(".body-modal-2").height(document.body.clientHeight-120);
        $(".body-modal .body-modal-content").height($(".body-modal").height()-160);
        $(".body-modal-2 .body-modal-content").height($(".body-modal-2").height()-100);

        $(".rightPanel .chat-body").height($(".body-modal").height()-175);
        $(".leftPanel .chat-bar").height(document.body.clientHeight-252);

        if(document.body.clientHeight-128 < 650) {
            $(".rightPanel .initChat img").css('height',document.body.clientHeight-128);
            $(".rightPanel .initChat img").css('line-height',document.body.clientHeight-128);
        }
    });

    //menu tooltip
    $("[data-toggle='tooltip']").tooltip({
        animation: true,
        placement: 'top',
        delay: { show: 300, hide: 100 }
    });

    var currentPage = 0;
    //menu操作
    $(document).on('click','.body-menu #hideMenu',function () {
        $('.body-menu').animate({width:'50px'},300);
        $('.body-menu img').css('width','28px');
        $('.body-menu .title').hide();
        $('.body-container').animate({'margin-left':'50px'},300);
    });

    $(document).on('click','.body-menu img',function () {
        $('.body-menu').animate({width:'240px'},316);
        $('.body-menu img').css('width','36px');
        $('.body-menu .title').show();
        $('.body-container').animate({'margin-left':'220px'},300);
    });


    /**************************销售视角*******************************/
    //用户名单
    $(document).on('click','.list .user-list', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }
        currentPage = 1;
        $(".show-calendar").remove();
        $(".body-modal .title").text("用户名单");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/user-list',function () {

            $(".user-list #list-content").load("/user/user-list-page?type=1&keyword=&studentPhone=", function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });

    //推广效果列表
    $(document).on('click','.list .promotion-effect', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }
        currentPage = 2;
        $(".show-calendar").remove();
        $(".body-modal .title").text("推广效果列表");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/promotion-effect',function () {
            var start = $('.promotion-effect-list .head #time-range').val().split(' - ')[0],
                end = $('.promotion-effect-list .head #time-range').val().split(' - ')[1];

            $(".promotion-effect-list  #list-content").load("/sale/promotion-effect-page?start=" + start + "&end=" + end, function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });

    //员工管理
    $(document).on('click','.list .employe', function () {

        if($(this).parent().hasClass('selected')){
            return false;
        }
        currentPage = 42;
        $(".show-calendar").remove();
        $(".body-modal .title").text("员工管理");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/employe',function () {

            var param = {
                "keyword":'',
                "status":0,
            };
            $(".employe #sales-table").load('/sale/employe-page', param, function (res) {});
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });


    //全部用户 VIP微课的管理视角
    $(document).on('click','.list .all-user', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }

        currentPage = 43;
        $(".show-calendar").remove();
        $(".body-modal .title").text("全部用户");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/all-user-list',function () {

            $(".all-user-list #list-content").load('/user/all-user-list-page?type=0&kefutype=0&keyword=&studentPhone=', function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });


    //体验课报表
    $(document).on('click','.list .ex-class-report', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }
        currentPage = 44;
        $(".show-calendar").remove();
        $(".body-modal .title").text("体验课报表");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/report/ex-class-report',function () {

            $(".ex-class-report #list-content").load('/report/ex-class-report-page?type=0&date=0&status=0&kefuid=0', function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });

    //专属服务
    $(document).on('click','.list .personal-server', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }
        currentPage = 47;
        $(".show-calendar").remove();
        $(".body-modal .title").text("专属服务");
        $(".body-modal .body-modal-content").text("正在加载数据...").load('/chat/personal-server',function () {

            $(".personal-server .personal-server-content").load('/chat/personal-server-page?start=0&end=0', function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });

    //月月活动奖励明细
    $(document).on('click','.list .month-gift', function () {
        if($(this).parent().hasClass('selected')){
            return false;
        }

        currentPage = 48;
        $(".show-calendar").remove();
        $(".body-modal .title").text("月月活动奖励明细");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/chat/month-gift',function () {

            $(".month-gift .month-gift-content").load('/chat/month-gift-page?start=0&end=0&usertype=1&kefuId=0', function (res) {

            });
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });
    //待跟进名单
    $(document).on('click','.navInfo #todo-stuff', function () {
        currentPage = 45;

        $(".show-calendar").remove();
        $(".body-modal .title").text("待跟进名单");

        $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/todo-index',function () {
            var start = $('.body-modal .body-modal-content #todo-time').val().split(' ')[0],
                end = $('.body-modal .body-modal-content #todo-time').val().split(' ')[2];
            $(".todo-content .todo-body").load('/sale/todo-list?start='+ start +'&end='+ end,function(){

            });
        });
        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $('.list').find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
    });

    //转渠道列表
    $(document).on('click','.channel_transfer_list',function(){
        //判断是否需要再次加载
        if($(this).hasClass('selected')){
            return false;
        }
        currentPage = 46;//公用的标记
        $(".show-calendar").remove();

        $(".body-modal .title").text("转渠道列表");
        $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/transfer-index',function () {
            $(".channel-transfer-list .list-transfer").load('/user/transfer-page');
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });  
    
    /*
     * 点击查看未读消息
     */
    $(document).on('click','.wait_statistics',function(){
        //判断是否需要再次加载
        if($(this).hasClass('selected')){
            return false;
        }
        currentPage = 49;//公用的标记
        $(".show-calendar").remove();

        $(".body-modal .title").text("未读消息统计");
        $(".body-modal .body-modal-content").text("正在加载数据...").load('/channel/wait-statistics',function () {
                $(".channel-waitstatistics-list .list-waitstatistics").load('/channel/wait-statistics-page');
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });

    //羊毛党列表
    $(document).on('click','.wool_party',function(){
        //判断是否需要再次加载
        if($(this).hasClass('selected')){
            return false;
        }
        currentPage = 50;//公用的标记
        $(".show-calendar").remove();

        $(".body-modal .title").text("羊毛党列表");
        $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/wool-party',function () {
            $(".wool-party #list-content").load('/user/wool-party-page?kefuId=0');
        });

        $(".body-modal").removeClass('fadeOutDown').addClass('fadeInUp').show();
        $(this).parent().parent().find('li').removeClass();
        $(".navInfo").find("a").removeClass('selected');
        $(this).parent().addClass('selected');
    });



    //课程监控
    var mtype = 1;
    $(document).on("click",'.configMenu',function () {
        mtype = 1;
        $.getJSON('/course/get-kefu-list', function (res) {
            
            if (res.error == '')
            {
                var str = '';
                for(var i = 0; i < res.data.length; i++)
                {
                    str += '<option value="' + res.data[i].id + '">' + res.data[i].nickname + '</option>';
                }
                
                $(".body-class-alert #monitor-kefu-select").append(str);
            }
            
            $(".body-container .body-class-alert").removeClass('slideInLeft').addClass('slideInRight').show();
            $(".body-class-content .choice li").removeClass("selected");
            $(".body-class-content .choice li:first").addClass("selected");
            $(".body-class-content #monitorFilter").val("");

            var url = "/course/monitor?type=1&date=" + $(".datepicker_box #dateTime").val();
            $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
        });
    });

    $(document).on('click',".body-container .body-class-alert .fa-close",function () {
        $(".body-container .body-class-alert").removeClass('slideInRight').hide();
        $(".datepicker_box #dateTime").val($("#hideDate").val());
    });

    $(document).on('click',".body-container .body-class-alert .fa-refresh",function () {
        mtype = 1;
        $(".body-class-content .choice li").removeClass("selected");
        $(".body-class-content .choice li:first").addClass("selected");
        $(".body-class-content #monitorFilter").val("");

        var url = "/course/monitor?type=1&date=" + $(".datepicker_box #dateTime").val();
        $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
    });

    $(document).on('click',".choice-filter .choice li",function () {
        if($(this).hasClass("selected")){
            return false;
        }

        $(".body-class-content .choice li").removeClass("selected");
        $(this).addClass("selected");

        mtype = this.id;      //
        var url = "/course/monitor?type=" + this.id
            + "&date=" + $(".datepicker_box #dateTime").val()
            + "&keyword=" + $(".body-class-content #monitorFilter").val()
            + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
            + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
        $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
    });

    $(document).on('click',".choice-filter .btn-sm",function () {
        var url = "/course/monitor?type=" + mtype
            + "&date=" + $(".datepicker_box #dateTime").val()
            + "&keyword=" + $(".body-class-content #monitorFilter").val()
            + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
            + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
        $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
    });

    $(document).on('keydown', '.choice-filter #monitorFilter', function(e){
        // 兼容FF和IE和Opera
        var theEvent = e || window.event;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;

        if (code == 13) {
            e.preventDefault();
            var url = "/course/monitor?type=" + mtype
                + "&date=" + $(".datepicker_box #dateTime").val()
                + "&keyword=" + $(".body-class-content #monitorFilter").val()
                + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
                + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
            $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
        }
    });

    $(".choice-filter .datepicker_box #dateTime").datepicker({
        autoclose:true,
        format: "yyyy-mm-dd",
        language: "zh-CN" }).on('changeDate', function(ev){
            var url = "/course/monitor?type=" + mtype
                + "&date=" + $(".datepicker_box #dateTime").val()
                + "&keyword=" + $(".body-class-content #monitorFilter").val()
                + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
                + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
            $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
        });

    $(document).on('change', ".choice-filter #monitor-kefu-select", function () {
        var url = "/course/monitor?type=" + mtype
            + "&date=" + $(".datepicker_box #dateTime").val()
            + "&keyword=" + $(".body-class-content #monitorFilter").val()
            + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
            + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
        $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
    });

    $(document).on('change', ".choice-filter #monitor-courseType", function () {
        var url = "/course/monitor?type=" + mtype
            + "&date=" + $(".datepicker_box #dateTime").val()
            + "&keyword=" + $(".body-class-content #monitorFilter").val()
            + "&kefu_id=" + $(".choice-filter #monitor-kefu-select>option:selected").val()
            + "&monitor_courseType=" + $(".choice-filter #monitor-courseType>option:selected").val();
        $(".body-class-alert .body-class-content .monitorPage").text("正在加载数据...").load(url);
    });

    //关闭modal
    $(document).on('click','.body-modal .close_it',function () {
        $(".body-menu .menu-list").find("li").removeClass('selected');
        $(".navInfo").find("a").removeClass('selected');
        $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
    });

    $(document).on('click','.body-modal-2 .close_it',function () {
        var url = $(".body-modal-2 .body-modal-content").find('#class-url').val();
        
        if(url != undefined)
        {
            $(".body-modal .coursePage #detailPage").load(url);
        }
        $(".body-modal-2").removeClass('fadeInUp').addClass('fadeOutDown');
    });

    //关闭modal-quick
    $(document).on('click','.body-header .fa-comments',function () {
        $(".body-menu .menu-list").find("li").removeClass('selected');
        $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
    });

    //refresh
    $(document).on('click','.body-modal .refresh_it',function () {
        switch (currentPage)
        {
            case 1:
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/user-list',function () {

                    $(".user-list #list-content").load('/user/user-list-page?type=1&keyword=&studentPhone=', function (res) {

                    });
                });
                break;
            case 2:
                $(".body-modal .title").text("推广效果列表");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/promotion-effect',function () {
                    var start = $('.promotion-effect-list .head #time-range').val().split(' - ')[0],
                        end = $('.promotion-effect-list .head #time-range').val().split(' - ')[1];

                    $(".promotion-effect-list  #list-content").load("/sale/promotion-effect-page?start=" + start + "&end=" + end, function (res) {

                    });
                });
                break;
            case 42:
                $(".body-modal .title").text("员工管理");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/employe',function () {
                    var param = {
                        "keyword":'',
                        "status":0,
                    };
                    $(".employe #sales-table").load('/sale/employe-page', param, function (res) {

                    });
                });
                break;
            case 43:
                $(".body-modal .title").text("全部用户");

                $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/all-user-list',function () {

                    $(".all-user-list #list-content").load('/user/all-user-list-page?type=0&kefutype=0&keyword=&studentPhone=', function (res) {

                    });
                });
                break;
            case 44:
                $(".body-modal .title").text("体验课报表");

                $(".body-modal .body-modal-content").text("正在加载数据...").load('/report/ex-class-report',function () {

                    $(".ex-class-report #list-content").load('/report/ex-class-report-page?type=0&date=0&status=0&kefuid=0', function (res) {

                    });
                });
                break;
            case 45:
                $(".body-modal .title").text("待跟进名单");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/todo-index',function () {
                    var start = $('.body-modal .body-modal-content #todo-time').val().split(' ')[0],
                        end = $('.body-modal .body-modal-content #todo-time').val().split(' ')[2];

                    $(".todo-content .todo-body").load('/sale/todo-list?start='+ start +'&end='+ end,function(){

                    });
                });
                break;
            case 46:
                $(".show-calendar").remove();
                $(".body-modal .title").text("转渠道列表");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/transfer-index',function () {
                    $(".channel-transfer-list .list-transfer").load('/user/transfer-page');
                });
                break;
            case 47:
                $(".show-calendar").remove();
                $(".body-modal .title").text("专属服务");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/chat/personal-server',function () {
                    $(".personal-server-content").load('/chat/personal-server-page', function (res) {});
                });
                break;
            case 48:
                $(".show-calendar").remove();
                $(".body-modal .title").text("月月活动奖励明细");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/chat/month-gift',function () {
                    $(".month-gift .month-gift-content").load('/chat/month-gift-page?start=0&end=0&usertype=1&kefuId=0', function (res) {

                    });
                });
                break;
            case 49:
                $(".show-calendar").remove();
                $(".body-modal .title").text("未读消息统计");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/channel/wait-statistics', function () {
                    $(".channel-waitstatistics-list .list-waitstatistics").load('/channel/wait-statistics-page');
                });
                break ;
            case 50:
                $(".show-calendar").remove();
                $(".body-modal .title").text("羊毛党列表");
                $(".body-modal .body-modal-content").text("正在加载数据...").load('/user/wool-party',function () {
                    $(".wool-party #list-content").load('/user/wool-party-page?kefuId=0');
                });
                break ;
            default:
                break;
        };
    });

    //全屏
    $(document).on('click','.body-header .fa-arrows-alt',function () {
        if(isFullScreen == 0){
            fullScreen();
        }else{
            exitFullScreen();
        }

    });

    $(document).on('click','.body-header .userInfo',function () {
        $('.body-header .userConfig').show();
    });

    //修改密码
    $(document).on('click','.body-header .userConfig #passwd',function () {
        $("#commonModal").modal({ backdrop: "static" });
        $("#commonModal .modal-title").text("修改密码");
        $("#commonModal .modal-footer .confirm_btn").attr('id','change_pwd');
        $("#commonModal .modal-body").load('/home/password');

        $('.body-header .userConfig').fadeOut();
    });

    $(document).on('click','#commonModal .modal-footer #change_pwd',function () {
        var pwd = $('#commonModal .modal-body #pwd').val();
        var pwd1 = $('#commonModal .modal-body #pwd1').val();

        if($.trim(pwd) == ""){
            $('#commonModal .modal-body .error-tip').text('新密码不能为空').fadeIn();
            return false;
        }

        if($.trim(pwd) != $.trim(pwd1)){
            $('#commonModal .modal-body .error-tip').text('两次密码不一致').fadeIn();
            return false;
        }

        var elem = $(this);
        $(elem).text('正在提交...').attr('disabled',true);
        $.post('/home/update-password',{'pwd':$.trim(pwd)},function (res) {
            if(res == 1){
                $('#commonModal .modal-body .error-tip').text('密码修改成功,3秒后重新登录...').fadeIn();
                $(window).unbind('beforeunload');
                setTimeout("window.location.href = '/site/login'",2000);

            }else{
                $(elem).text('确 定').removeAttr('disabled');
                $('#commonModal .modal-body .error-tip').text(res).fadeIn();
                return false;
            }
        });
    });

    //页面任意点击
    $(document).on('click','.body-content',function () {
        $('.body-header .userConfig').fadeOut();
    });

    $(document).on('click','.chat-center textarea',function () {
        $('.chat-center .chat-footer .emoji-content').hide();
    });

    $(document).on('click','.chat-center .chat-body',function () {
        $('.chat-center .chat-footer .emoji-content').hide();
    }); 
});

var getTimeStamp = function(timeStr){
    var date = Date.parse(timeStr);
    var timeStamp = date/1000;
    if(!isNaN(timeStamp)){
        return timeStamp;
    }else{
        return 0 ;
    }
}

// 全屏判断各种浏览器，找到正确的方法
function fullScreen() {
    isFullScreen = 1;
    var el = document.documentElement;
    var rfs = el.requestFullScreen || el.webkitRequestFullScreen ||
        el.mozRequestFullScreen || el.msRequestFullScreen;
    if(typeof rfs != "undefined" && rfs) {
        rfs.call(el);

    } else if(typeof window.ActiveXObject != "undefined") {
        //for IE，这里其实就是模拟了按下键盘的F11，使浏览器全屏
        var wscript = new ActiveXObject("WScript.Shell");
        if(wscript != null) {
            wscript.SendKeys("{F11}");
        }
    }
}

//退出全屏 判断浏览器种类
function exitFullScreen() {
    isFullScreen = 0;
    var exitMethod = document.exitFullscreen || //W3C
        document.mozCancelFullScreen ||    //Chrome等
        document.webkitExitFullscreen || //FireFox
        document.webkitExitFullscreen; //IE11
    if (exitMethod) {
        exitMethod.call(document);
    }
    else if (typeof window.ActiveXObject !== "undefined") {//for Internet Explorer
        var wscript = new ActiveXObject("WScript.Shell");
        if (wscript !== null) {
            wscript.SendKeys("{F11}");
        }
    }
}

function getType() {
    var btn_id, student_type, time_type, time_start, time_end, keyword, param;
    btn_id = $(".studentPage .active").attr("id");
    student_type = $(".studentPage #select-student-type").val();
    time_type = $(".studentPage #select-time-type").val();
    time_start = $(".studentPage #student-date").val().length > 0 ? $("input[name='daterangepicker_start']").val() : '2014/01/01';
    time_end = $(".studentPage #student-date").val().length > 0 ? $("input[name='daterangepicker_end']").val() : '2020/01/01';
    keyword = $(".studentPage #studentFilter").val();

    param = {
        "btn_id":btn_id,
        "student_type":student_type,
        "time_type":time_type,
        "time_start":time_start,
        "time_end":time_end,
        "visit_time":0,
        "keyword":keyword,
        "is_export":0

    };

    return param;
}

function getOrderType() {
    var order_type, order_time, keyword;

    order_type = $(".orderPage  #select-order-type").val();
    order_time = $(".orderPage  #datePicker").val();
    keyword = $(".orderPage #order-student").val();

    param = {
        "order_type":order_type,
        "order_time":order_time,
        "keyword":keyword
    };

    return param;
}

function getHistoryType(){
    var time_start, time_end, keyword;

    time_start = $(".chistoryPage #chat-date").val().length > 0 ? $("input[name='daterangepicker_start']").val() : '2014/01/01';
    time_end = $(".chistoryPage #chat-date").val().length > 0 ? $("input[name='daterangepicker_end']").val() : '2020/01/01';
    keyword = $(".chistoryPage #kefu-name").val();

    param = {
        "time_start":time_start,
        "time_end":time_end,
        "keyword":keyword
    };

    return param;
}