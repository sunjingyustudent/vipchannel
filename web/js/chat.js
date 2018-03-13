    /**
 * Created by huangjun on 16/7/3.
 */
$(function () {

    loaction = {
        "sbu_loc":-1
    };

    var socket = '0',
        timeout = false;

    var initWebSocket = function () {
        socket = new WebSocket(WS_CONFIG);
        socket.onopen = function (event) {
            timeout = false; //启动及关闭按钮
            time();
            $(".userInfo .userPrompt ").css('color', 'green');
        };

        socket.onmessage = function (event) {
            var jsonObj = JSON.parse(event.data);

            if (jsonObj.event == 'CONNECT') {
                var sendData;
                var user_id = $(".chat-hidden #user-id").val();
                $(".chat-hidden #page_id").val(jsonObj.page_id);
                sendData = '{"event":"CONNECT","kefu_id":"' + user_id + '"}';
                socket.send(sendData);
            }

            if (jsonObj.event == 'CLOSE') {
                socket.close();
                swal('当前连接已断开', '', 'warning');
            }

            if (jsonObj.event == 'ACCESS' || jsonObj.event == 'VIEW' || jsonObj.event == 'BODY') {

                var list = '<div class="left-message" >',
                    domain = $("#address_static").val();

                list = list + '<div class="avatar"><img src=' + jsonObj.message.head + '></div>';

                if (jsonObj.message.type == 1)
                {
                    list = list + '<div class="content"><p>' + jsonObj.message.message + '</p>';
                } else if (jsonObj.message.type == 2)
                {
                    list = list + '<div class="content"><img class="wechat-img" src=' + domain + jsonObj.message.message + '>';
                } else if (jsonObj.message.type == 3)
                {
                    list = list + '<div class="content"><img name=' + jsonObj.message.id + ' class="voice-img" src="/images/voice.png"><audio id=' + jsonObj.message.id + ' src="http://vip-video.pnlyy.com/' + jsonObj.message.message + '"></audio>';
                }

                list = list + '<p class="grey">' + jsonObj.message.date + '</p></div>';
                list = list + '<div class="clearAll"></div>';
                $.getJSON('/chat/get-link', {"open_id": jsonObj.message.open_id}, function (data) {
                    sortIndex(data.link);
                });
                $(".chat-body").append(list);
                $('.chat-body').scrollTop(1000000);
            }

            if (jsonObj.event == 'HEAD') {
                $(".navInfo .wait-count").remove();
                if (jsonObj.message[0].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[0].counts + '</div>';
                    $('.navInfo #all-wait').prepend(count);
                }
                if (jsonObj.message[1].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[1].counts + '</div>';
                    $('.navInfo #new-wait').prepend(count);
                }
                if (jsonObj.message[2].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[2].counts + '</div>';
                    $('.navInfo #worth-wait').prepend(count);
                }
                if (jsonObj.message[4].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[4].counts + '</div>';
                    $('.navInfo #withdrawals-wait').prepend(count);
                }
                if (jsonObj.message[3].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[3].counts + '</div>';
                    $('.navInfo #no-worth-wait').prepend(count);
                }
                if (jsonObj.message[5].counts != 0) {
                    var count = '<div class="wait-count">' + jsonObj.message[5].counts + '</div>';
                    $('.navInfo #user-of-the-day').prepend(count);
                }
            }

            if (jsonObj.event == 'LEFT')
            {

                var linkCell = $(".leftPanel #link_" + jsonObj.message.id).clone();
                $(".leftPanel #link_" + jsonObj.message.id).remove();
                $(".leftPanel .chat-bar").prepend(linkCell);

                var text = '<div class="new-message">' + 'New' + '</div>';
                if ($(".leftPanel #link_" + jsonObj.message.id).find(".new-message").length == 0)
                {
                    $(".leftPanel #link_" + jsonObj.message.id).append(text);
                }
            }

            if (jsonObj.event == 'USER')
            {
                $(".body-content .user-number").remove();
                var text = '<span class="user-number label label-default"> 今日接待人数: ' + jsonObj.message.count + '</span>';
                $(".body-chat-home").prepend(text);
            }

            if (jsonObj.event == 'ALERT') {
                var message_count = jsonObj.message.count>99?99:jsonObj.message.count;
                $(".norepay").find('span').html(message_count);
                if(message_count>0){
                    $('.norepay').animate({opacity:1,right:"-15px"}); 
                    $.post('chat/get-no-repay-info','',function(data){
                        if(data.count>0){
                            var content = '';
                            //列表
                            for (var i = 0; i < data.data.length; i++) {
                                var nickname = data.data[i].nickname == null ? '无' : data.data[i].nickname;
                                content += '<span id="' + data.data[i].bind_openid + '" class="alert-message label label-warning" >' + data.data[i].name + '(' + nickname + ')</span>';
                            }
                            content += '<div class="clear"></div>';
                            $(".body-alert-message .alert-content").empty();
                            $(".body-alert-message .alert-content").append(content);
                        }else{               
                            $(".body-alert-message").removeClass('slideInRight').hide();
                        }
                    },'JSON');　
                }else{
                    $(".body-alert-message").removeClass('slideInRight').hide();
                }
            }

            if (jsonObj.event == 'TRANSFER') {

                swal({
                    title: '转接消息',
                    text: '用户 ' + jsonObj.message + ' 已转接给您',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "查 看",
                    cancelButtonText: "忽 略",
                    closeOnCancel: true
                }, function (isConfirm) {

                    //$(".body-content .leftPanel .chat-bar").load('/chat/left-user');

                        if (isConfirm) {
                            $("#showModal").modal("hide");
                            $("#commonModal").modal("hide");
                            $(".body-menu .menu-list").find("li").removeClass('selected');
                            $(".body-modal").css('display', 'none');
                            $(".body-modal-2").css('display', 'none');
                            $(".navInfo").find("a").removeClass('selected');
                        }
                });
                $(".body-content .leftPanel .chat-bar").load('/chat/left-user');
            }

            if (jsonObj.event == 'RTRANSFER') {

                swal({
                    title: '转接请求',
                    text: '(' + jsonObj.message.kefu_name + ')请求转接用户(' + jsonObj.message.user_nick + ')',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "同意转接",
                    cancelButtonText: "拒 绝",
                    closeOnConfirm: false,
                    closeOnCancel: false
                }, function (isConfirm) {

                    if (isConfirm) {
                        var param = {
                            'kefu_id': jsonObj.message.kefu_id,
                            'link_id': jsonObj.message.link_id
                        };

                        $.post('/chat/do-transfer', param, function (res) {
                            var result = JSON.parse(res);

                            if (result.error == '') {
                                swal("转接成功", "", "success");
                                sendData = '{"event":"TRANSFER","page_id":"' + result.data.page_id + '", "open_id":"' + result.data.open_id + '"}';
                                socket.send(sendData);
                                $(".body-content .leftPanel .chat-bar").load('/chat/left-user');
                            } else {
                                swal("转接失败", result.error, "error");
                            }
                        });
                    } else {
                        sendData = '{"event":"REFUSE_TRANSFER","page_id":"' + jsonObj.message.page_id + '"}';
                        socket.send(sendData);
                        swal("拒绝成功", "", "success");
                    }
                });
            }

            if (jsonObj.event == 'REFUSE_TRANSFER') {
                swal('转接请求被拒绝', '', 'error');
            }
        };

        socket.onclose = function (event) {
            var page_id = $(".chat-hidden #page_id").val();
            $.post("/chat/close-socket", {"page_id": page_id}, function (res) {
                timeout = true;
                $(".rightPanel").html('<div class="initChat" style="height: 100%;"><img src="/images/network.png" ></div>');
                $(".userInfo .userPrompt").css('color', '#C67605');
                swal('当前连接已断开', '', 'warning');
            });
        }

    };

    function time()
    {
        if(timeout) return;

        if (socket != '0')
        {
            socket.send('{"event":"HEART_BEAT"}');
        }
        setTimeout(time, 50000); //time是指本身,延时递归调用自己,100为间隔调用时间,单位毫秒
    }

    // initWebSocket();

    /**
     * 重新连接
     */
    $(document).on('click', '.body-header .userConfig #online', function () {
        var page_id = $(".chat-hidden #page_id").val();
        $.get('/chat/check-connect?pageId=' + page_id, function (res) {
            if (res == 0) {
                swal('您已连接,请勿重新连接', '', 'warning');
                return false;
            }
            initWebSocket();

            $(".body-header .userConfig").hide();
            swal('连接成功', '', 'success');
            //链接后提示
            $.post('chat/get-no-repay-info','',function(data){
                var message_count = data.count>99?99:data.count;
                $(".norepay").find('span').html(message_count);
                $(".body-alert-message").removeClass('slideInRight').hide();
            },'JSON');
        });
    });

    /**
     * 断开连接
     */
    $(document).on('click', '.body-header .userConfig #offline', function () {
        if (socket != '0')
        {
            socket.close();
            timeout = true;
            $(".body-header .userConfig").hide();
            swal('离线成功', '', 'success');
        }else {
            swal('当前已离线', '', 'warning');
        }
    });

    /**
     * 刷新事件
     * @param event
     * @returns {string}
     */
    $(window).bind('beforeunload', function () {
        return "刷新页面会断开当前连接用户\n确认要刷新页面吗";
    });

    /**
     * 聊体加载更多
     */
    $(document).on('click', '.chat-body #load-more a', function () {
        $(".chat-body #load-more").text('正在加载历史数据...');
        var count = $(".chat-body").find(".avatar").length,
            domain = $("#address_static").val(),
            domainVideo = 'http://vip-video.pnlyy.com/';

        $.getJSON('/chat/load-more', {"offset": count}, function (res) {
            if (res.length == 0) {
                $(".chat-body #load-more").text('没有更多历史记录');
                return false;
            }

            $.each(res, function (index, row) {
                if (row.tag == 0) {
                    var list = '<div class="left-message" >';
                    list = list + '<div class="avatar"><img src=' + row.head + '></div>';
                    if (row.type == 1) {
                        list = list + '<div class="content"><p>' + row.message + '</p>';
                    } else if (row.type == 2) {
                        list = list + '<div class="content"><img class="wechat-img" src=' + domain + row.message + '>';
                    } else if (row.type == 3) {
                        list = list + '<div class="content"><img name=' + row.id + ' class="voice-img" src="/images/voice.png"><audio id=' + row.id + ' src=' + domainVideo + row.message + '></audio>';
                    }
                    list = list + '<p class="grey">' + getDat(row.time_created) + '</p></div>';
                    list = list + '<div class="clearAll"></div>';

                    $(".chat-body #load-more").after(list);

                } else {
                    var list = '<div class="right-message" >';
                    list = list + '<div class="avatar"><img src=' + row.kefu_head + '></div>';
                    if (row.type == 1) {
                        list = list + '<div class="content"><p>' + row.message + '</p>';
                    } else if (row.type == 2) {
                        list = list + '<div class="content"><img class="wechat-img" src=' + domain + row.message + '>';
                    } else if (row.type == 3) {
                        list = list + '<div class="content"><img name=' + row.id + ' class="voice-img" src="/images/voice.png"><audio id=' + row.id + ' src=' + domainVideo + row.message + '></audio>';
                    }
                    list = list + '<p class="grey">' + row.kefu_name + ' ' + getDat(row.time_created) + '</p></div>';
                    list = list + '<div class="clearAll"></div>';

                    $(".chat-body #load-more").after(list);
                }
            });

            $(".chat-body #load-more").html('<a href="javascript:void(0);"><span class="fa fa-history"> 点击获得历史记录</span></a>');
        });
    });


    /**
     * 点击接入
     */

    $(document).on('click', '#showModal .access_btn', function () {

        var wait_id = $(this).attr("id").split("_")[1],
            page = $("#chat-wait-type").val(),
            page_id = $(".chat-hidden #page_id").val();

        $.get('/chat/check-connect?pageId=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }
            $.getJSON('/chat/check-access', {"wait_id": wait_id, "page": page}, function (res) {
                if (res.data == 1) {
                    $(".rightPanel").html('<div class="initChat" style="height: 100%;"><img src="/images/network.png" style="height: 205px; line-height: 205;"></div>');
                    $("#showModal").modal("hide");
                    $(".body-menu .menu-list").find("li").removeClass('selected');
                    $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                    $(".body-content .rightPanel").load('/chat/access-right?waitId=' + wait_id, function () {
                        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function (e) {

                            $(".body-content .tool-bar .link-list").remove();
                            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

                            $(".body-content .tool-bar a").append(list);
                            $(".body-content .leftPanel").attr('name', 'connecting');


                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);

                            var sendData, open_id, user_id;
                            open_id = $("#open_id").val();
                            user_id = $(".chat-hidden #user-id").val();
                            sendData = '{"event":"ACCESS","kefu_id":"' + user_id + '", "open_id":"' + open_id + '"}';
                            socket.send(sendData);
                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                                sortIndex(data.link);
                                $(".chat-right #transfer-server").attr('linkid', data.link.id);
                            });
                        $('#user-input').focus();
                        });
                    });
                } else {
                    swal('用户已接入其他客服', '', 'error');
                    $("#showModal .modal-body").load(res.page);
                }
            });
        });
    });

    /**
     * 切换聊天窗口
     */
    $(document).on('click', '.leftPanel .link-user', function () {

        link_id = $(this).attr("id").split("_")[1];

        $(".body-content .rightPanel").load('/chat/link-right?linkId=' + link_id, function () {
            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
            $('.rightPanel .chat-body').scrollTop(1000000);
            $(".link-user").removeClass("bgchange");
            $("#link_" + link_id).addClass("bgchange");
            $(".chat-right #transfer-server").attr('linkid', link_id);
            $(".leftPanel #link_" + link_id + " .new-message").remove();
            if (socket != '0')
            {
                var sendData, open_id, user_id;
                open_id = $("#openid").val();
                user_id = $(".chat-hidden #user-id").val();
                sendData = '{"event":"VIEW","kefu_id":"' + user_id + '", "open_id":"' + open_id + '"}';
                socket.send(sendData);
            }
            $('#user-input').focus();
        });
    });

    /**
     * 图片放大
     */
    $(document).on('click', '.chat-body .wechat-img', function () {
        var img = $(this).attr('src'), list;

        list = '<div class="img-view-content"><img src="' + img + '"></div>';

        $("#showModal .modal-body").empty().append(list);
        $("#showModal .modal-title").text('图片信息');
        $("#showModal").modal('show');

    });

    /**
     * 点击按钮发送
     */
    $(document).on('click', '.chat-center .chat-footer .send-btn span', function (e) {

        var page_id = $(".chat-hidden #page_id").val();

        $.get('/chat/check-connect?pageId=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }

            if ($(this).hasClass('vip_btn_disable')) {
                return false;
            }

            $(this).addClass('vip_btn_disable');

            var theEvent = e || window.event;
            var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
            if (code == 13 && e.shiftKey) {
                e.preventDefault();
                $("#user-input").val($("#user-input").val() + "\n");

            }
            e.preventDefault();
            var inputValue = $("#user-input").val();
            // var inputValue = $("#user-input").html();
            var open_id = $("#open_id").val();
            var user_head = $(".chat-hidden #user-head").val();
            if (inputValue != '') {
                $.getJSON('/chat/check-talk', {"open_id": open_id}, function (res) {
                    if (res.error == '') {
                        $(".body-menu .menu-list").find("li").removeClass('selected');
                        $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                        $(".body-content .rightPanel").load('/chat/access-talk?openId=' + open_id, function () {
                            $(".body-content .leftPanel").attr('name', 'connecting');

                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);
                            sendMessageToWechat(inputValue,open_id);
                           $('#user-input').focus();
                        });
                    } else {

                        swal({
                            title: res.error,
                            text: "",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "请求转接",
                            cancelButtonText: "取 消",
                            closeOnConfirm: false
                        }, function () {
                            sendData = '{"event":"RTRANSFER","link_id":"' + res.data.link_id + '", "kefu_id":"' + res.data.kefu_id + '","page_id":"' + res.data.page_id + '","user_nick":"' + res.data.user_nick + '","kefu_name":"' + res.data.kefu_name + '"}';
                            socket.send(sendData);

                            swal('请求已发送', '', 'success');
                        });
                    }
                });
            } else {
                swal('发送内容不能为空', '', 'error');
                $(".chat-center .chat-footer .send-btn span").removeClass("vip_btn_disable");
            }
        });
    });

    /**
     * 回车发送
     */
    $(document).on('keydown', '#user-input', function (e) {
        // 兼容FF和IE和Opera
        var theEvent = e || window.event;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;

        if (code == 13) {
            e.preventDefault();
            $(".chat-center .chat-footer .send-btn span").click();
        }

    });


    /**
     * 点击聊天
     */
    $(document).on('click', '#detailPage  .talk_btn', function () {
        var id = $(this).attr('id').split('_')[1],
            open_id = $("#detailPage #user_hidden_" + id + " #open_id").val(),
            page_id = $(".chat-hidden #page_id").val();

        $.get('/chat/check-connect?pageId=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }

            $.getJSON('/chat/check-talk', {"open_id": open_id}, function (res) {
                if (res.error == '') {
                    $(".body-menu .menu-list").find("li").removeClass('selected');
                    $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                    $(".body-content .rightPanel").load('/chat/access-talk?openId=' + open_id, function () {
                        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function () {

                            $(".body-content .tool-bar .link-list").remove();
                            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

                            $(".body-content .tool-bar a").append(list);
                            $(".body-content .leftPanel").attr('name', 'connecting');

                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);

                            $('#user-input').focus();
                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                                sortIndex(data.link);
                                $(".chat-right #transfer-server").attr('linkid', data.link.id);
                            });
                        });
                    });
                } else {

                    swal({
                        title: res.error,
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "请求转接",
                        cancelButtonText: "取 消",
                        closeOnConfirm: false
                    }, function () {
                        sendData = '{"event":"RTRANSFER","link_id":"' + res.data.link_id + '", "kefu_id":"' + res.data.kefu_id + '","page_id":"' + res.data.page_id + '","user_nick":"' + res.data.user_nick + '","kefu_name":"' + res.data.kefu_name + '"}';
                        socket.send(sendData);

                        swal('请求已发送', '', 'success');
                    });

                }
            });
        });
    });


    /**
     * 专属客服 点击聊天
     */
    $(document).on('click', '#personal-server-list  .talk_btn_personal, ' +
        '.month-gift-content .talk_btn_gift', function () {
        var open_id =  $(this).attr('sid');
            page_id = $(".chat-hidden #page_id").val();

            // console.log(open_id);

        $.get('/chat/check-connect?pageId=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }

            $.getJSON('/chat/check-talk', {"open_id": open_id}, function (res) {
                if (res.error == '') {
                    $(".body-menu .menu-list").find("li").removeClass('selected');
                    $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                    $(".body-content .rightPanel").load('/chat/access-talk?openId=' + open_id, function () {
                        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function () {

                            $(".body-content .tool-bar .link-list").remove();
                            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

                            $(".body-content .tool-bar a").append(list);
                            $(".body-content .leftPanel").attr('name', 'connecting');

                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);

                            $('#user-input').focus();
                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                                sortIndex(data.link);
                                $(".chat-right #transfer-server").attr('linkid', data.link.id);
                            });
                        });
                    });
                } else {

                    swal({
                        title: res.error,
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "请求转接",
                        cancelButtonText: "取 消",
                        closeOnConfirm: false
                    }, function () {
                        sendData = '{"event":"RTRANSFER","link_id":"' + res.data.link_id + '", "kefu_id":"' + res.data.kefu_id + '","page_id":"' + res.data.page_id + '","user_nick":"' + res.data.user_nick + '","kefu_name":"' + res.data.kefu_name + '"}';
                        socket.send(sendData);

                        swal('请求已发送', '', 'success');
                    });

                }
            });
        });
    });


    /**
     * 历史聊天
     */
    $(document).on('click', '.body-content .tool-bar #link-history', function () {
        $(this).removeAttr('id');
        $(".body-content .leftPanel .chat-bar").empty().text('loading...');
        $(".body-content .leftPanel .chat-bar").load('/chat/left-user?isHistory=1', function () {
            $(".body-content .tool-bar .link-list").remove();
            var list = '<span id="link-connect" class="link-list fa fa-arrow-right"> 返回接待</span>';

            $(".body-content .tool-bar a").append(list);
            $(".body-content .leftPanel").attr('name', 'history');
        });
    });

    /**
     * 返回正在接待
     */
    $(document).on('click', '.body-content .tool-bar #link-connect', function () {
        $(this).removeAttr('id');
        $(".body-content .leftPanel .chat-bar").empty().text('loading...');
        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function () {
            $(".body-content .tool-bar .link-list").remove();
            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

            $(".body-content .tool-bar a").append(list);
            $(".body-content .leftPanel").attr('name', 'connecting');
        });
    });

    /**
     *转接客服
     */
    $(document).on('click', '.chat-right #transfer-server', function () {
        //var link_id = $(".chat-center").attr('linkid');

        $("#showModal .modal-body").load('/chat/transfer-server', function () {
            $("#showModal .modal-title").text("转接客服");
            $("#showModal").modal("show");
        })
    });

    /**
     *退出接待
     */
    $(document).on('click', '.chat-right #off-chat', function () {
        var link_id = $(".chat-right #transfer-server").attr('linkid'),
            response,
            param = {
                "link_id": link_id
            };

        $.post('/chat/off-chat', param, function (res) {
            response = JSON.parse(res);

            if (response.error == '') {
                swal('操作成功', '', 'success');
                $(".leftPanel .chat-bar #link_" + link_id).addClass('hide');
                $(".rightPanel").html('<div class="initChat" style="height: 100%;"><img src="/images/network.png" ></div>');
            } else {
                swal(response.error, '', 'error');
            }
        });
    });

    /**
     * 转接
     */
    $(document).on('click', '#showModal .transfer-server .transfer-btn', function () {

        var link_id = $(".chat-right #transfer-server").attr('linkid'),
            kefu_id = $(this).attr('id'),
            result;

        var param = {
            "link_id": link_id,
            "kefu_id": kefu_id
        };

        swal({
            title: "你确认要转接吗?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确 定",
            cancelButtonText: "取 消",
            closeOnConfirm: false
        }, function () {
            $.post('/chat/do-transfer', param, function (res) {
                result = JSON.parse(res);

                if (result.error == '') {
                    swal("转接成功", "", "success");
                    sendData = '{"event":"TRANSFER","page_id":"' + result.data.page_id + '", "open_id":"' + result.data.open_id + '"}';
                    socket.send(sendData);
                    $(".body-content .leftPanel .chat-bar").load('/chat/left-user');
                    $("#showModal").modal('hide');
                } else {
                    swal("转接失败", result.error, "error");
                }
            });
        });
    });

    /**
     *搜索聊天列表
     */
    $(document).on('change', '.body-content .search-bar #user-search', function () {
        var type = $(".body-content .leftPanel").attr('name'),
            keyword = $(this).val(),
            is_history;

        is_history = type == 'history' ? 1 : 0;

        $(".body-content .leftPanel .chat-bar").load('/chat/left-user?isHistory=' + is_history + '&keyword=' + keyword);
    });

    /**
     *选择表情
     */
    $(document).on('click', '.chat-center .chat-footer #emotion', function () {
        var list = '<div class="emoji-content"><table class="table table-bordered"><tr>',
            map = wechatMap();
        for (var i = 1; i <= 75; i++) {
            list += '<td><img name="[' + map[i] + ']" id="[em_' + i + ']" class="emoji-img" src="/images/face/' + i + '.gif"></td>';

            if (i % 15 == 0) {
                list += '</tr><tr>';
            }
        }
        list += '</tr></table></div>';

        $(".chat-center .chat-footer .quick-bar").append(list);
    });

    /**
     * 选择表情
     */
    $(document).on('click', '.chat-center .chat-footer .emoji-img', function () {
        var name = $(this).attr('name'),
            text = $('.chat-center .chat-footer #user-input').val();

        $('.chat-center .chat-footer #user-input').val(text + name);

        $('.chat-center .chat-footer #user-input').removeAttr('contenteditable');
        $('.chat-center .chat-footer #user-input').attr('contenteditable','true');
        $('.chat-center .chat-footer #user-input').focus();
        $(".chat-footer .quick-bar .emoji-content").remove();
    });


    /**
     * 未回复用户接入
     */
    $(document).on('click', '.body-alert-message .alert-content .alert-message', function () {
        var open_id = $(this).attr('id'),
            page_id = $(".chat-hidden #page_id").val();
        access(open_id, page_id,$(this));
   
    });

    $(document).on('click', '.body-alert-message .alert-header .fa-close', function () {
        $(".body-alert-message").removeClass('slideInRight').hide();
    });

    $(document).on('click', '.chat-center .chat-body .voice-img', function () {
        var name = $(this).attr("name");
        var audio = $(".chat-center .chat-body #" + name)[0];

        audio.play();
    });

    function getDat(timeStamp) {
        var date = timeStamp == -1 ? new Date() : new Date(timeStamp*1000);
        var year, month, day, hour, minute, second;
        year = date.getFullYear();
        month = date.getMonth() + 1;
        day = date.getDate();
        hour = date.getHours();
        minute = date.getMinutes();
        second = date.getSeconds();

        return month + '/' + day + ' ' + hour + ':' + minute;
    }

    function replace_em(str){
        str = str.replace(/\</g,'<；');
        str = str.replace(/\>/g,'>；');
        str = str.replace(/\n/g,'<；br/>；');
        str = str.replace(/\[em_([0-9]*)\]/g,'<img src="/images/face/$1.gif" border="0" />');
        return str;
    }

    function wechatMap() {
        var map = {
            "1":'微笑',"2":'撇嘴',"3":'色',"4":'发呆',"5":'流泪',"6":'害羞',"7":'闭嘴',
            "8":'睡',"9":'大哭',"10":'尴尬',"11":'发怒',"12":'调皮',"13":'呲牙',"14":'惊讶',
            "15":'难过',"16":'冷汗',"17":'抓狂',"18":'吐',"19":'偷笑',"20":'愉快',"21":'白眼',
            "22":'傲慢',"23":'饥饿',"24":'困',"25":'惊恐',"26":'流汗',"27":'憨笑',"28":'悠闲',
            "29":'奋斗',"30":'咒骂',"31":'疑问',"32":'嘘',"33":'晕',"34":'疯了',"35":'衰',
            "36":'敲打',"37":'再见',"38":'擦汗',"39":'抠鼻',"40":'糗大了',"41":'坏笑',"42":'左哼哼',
            "43":'右哼哼',"44":'哈欠',"45":'鄙视',"46":'委屈',"47":'快哭了',"48":'阴险',"49":'亲亲',
            "50":'吓',"51":'可怜',"52":'拥抱',"53":'月亮',"54":'太阳',"55":'炸弹',"56":'骷髅',
            "57":'菜刀',"58":'猪头',"59":'西瓜',"60":'咖啡',"61":'饭',"62":'爱心',"63":'强',
            "64":'弱',"65":'握手',"66":'胜利',"67":'抱拳',"68":'勾引',"69":'OK',"70":'NO',
            "71":'玫瑰',"72":'凋谢',"73":'嘴唇',"74":'爱情',"75":'飞吻'
        };

        return map;
    }

    function access(open_id, page_id,that) {
        $.get('/chat/check-connect?pageId=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }

            $.getJSON('/chat/check-talk', {"open_id": open_id}, function (res) {
                if (res.error == '') {
                    that.remove();
                    $("#addModal").modal('hide');
                    $("#saleModal").modal('hide');
                    $(".body-menu .menu-list").find("li").removeClass('selected');
                    $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                    $(".navInfo").find("a").removeClass('selected');
                    $(".body-content .rightPanel").load('/chat/access-talk?openId=' + open_id, function () {
                        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function () {

                            $(".body-content .tool-bar .link-list").remove();
                            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

                            $(".body-content .tool-bar a").append(list);
                            $(".body-content .leftPanel").attr('name', 'connecting');

                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);

                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                                sortIndex(data.link);
                                $(".chat-right #transfer-server").attr('linkid', data.link.id);
                            });
                        });
                    });
                } else {

                    swal({
                        title: res.error,
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "请求转接",
                        cancelButtonText: "取 消",
                        closeOnConfirm: false
                    }, function () {
                        sendData = '{"event":"RTRANSFER","link_id":"' + res.data.link_id + '", "kefu_id":"' + res.data.kefu_id + '","page_id":"' + res.data.page_id + '","user_nick":"' + res.data.user_nick + '","kefu_name":"' + res.data.kefu_name + '"}';
                        socket.send(sendData);

                        swal('请求已发送', '', 'success');
                    });
                }
            });
        });
    }
    
    //回执功能
    $(document).on('click','#pause-answer', function () {
        var open_id = $("#open_id").val(),
            user_head = $(".chat-hidden #user-head").val();
        if(open_id){
            $.post('/chat/add-message',{content:'[系统提示：回执信息]',open_id:open_id},function(response){
                if (response.error == '') {
                    var list = '<div id="message_' + response.data.message_id + '" class="right-message" >';
                    list += '<div class="avatar"><img src=' + user_head + '></div>';
                    list += '<div class="content"><p>' + response.data.content + '</p>';
                    list += '<p class="grey">' + response.data.kefu_name + ' ' + getDat(-1) + '</p></div>';
                    list += '<div class="clearAll"></div>';
                    $(".chat-body").append(list).scrollTop(1000000);
                    $("#user-input").val("");
                }
            },'JSON');
        }
    });
    //优先排序
    function sortIndex(obj){
        if(obj != undefined ){
            if($('#link_'+obj.id).length > 0){
                var linkCell = $(".leftPanel #link_" + obj.id).clone();
                $(".leftPanel #link_" + obj.id).remove();
                $(".leftPanel .chat-bar").prepend(linkCell);
            }else{
                var select_user = $('.chat-bar').find('.bgchange').clone();
                $('.chat-bar').find('.bgchange').remove();
                $(".leftPanel .chat-bar").prepend(select_user);
            }
        }      
    }

    //发送微信消息
    function sendMessageToWechat(inputValue,open_id)
    {
        $.post("/chat/add-message", {"content": inputValue, "open_id": open_id}, function (res) {
            var response = JSON.parse(res);
            if (response.error == '') {
                var list = '<div id="message_' + response.data.message_id + '" class="right-message" >';
                list = list + '<div class="avatar"><img src=' + $(".chat-hidden #user-head").val() + '></div>';
                list = list + '<div class="content"><p>' + response.data.content + '</p>';
                list = list + '<p class="grey">' + response.data.kefu_name + ' ' + getDat(-1) + '</p></div>';
                list = list + '<div class="clearAll"></div>';

                $(".chat-body").append(list);
                $('.chat-body').scrollTop(1000000);
                $("#user-input").val("");

                var param = {
                    "content": inputValue,
                    "open_id": open_id,
                    "message_id": response.data.message_id
                };

                $.post('/chat/send-wechat', param, function (status) {
                    var statusObj = JSON.parse(status);
                    if (statusObj.error != '') {
                        var message = '<span style="color: red; margin-right: 5px;" class="fa fa-exclamation-circle pull-right"></span>';
                        $(".chat-body #message_" + response.data.message_id + " .content").after(message);
                        swal({
                            title: statusObj.error,
                            text: "是否发送模版消息",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "发送模版消息",
                            cancelButtonText: "不发送",
                            closeOnConfirm: false
                        }, function () {
                            swal('发送成功', '', 'success');
                            $.post('/chat/send-template', param);
                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                                sortIndex(data.link);
                                $(".chat-right #transfer-server").attr('linkid', data.link.id);
                            });
                        });
                    }else{
                        $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
                            sortIndex(data.link);
                            $(".chat-right #transfer-server").attr('linkid', data.link.id);
                        });
                    }
                });
            } else {

                swal({
                    title: response.error,
                    text: "",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "请求转接",
                    cancelButtonText: "取 消",
                    closeOnConfirm: false
                }, function () {
                    sendData = '{"event":"RTRANSFER","link_id":"' + response.data.link_id + '", "kefu_id":"' + response.data.kefu_id + '","page_id":"' + response.data.page_id + '","user_nick":"' + response.data.user_nick + '","kefu_name":"' + response.data.kefu_name + '"}';
                    socket.send(sendData);

                    swal('请求已发送', '', 'success');
                });

            }
            $(".chat-center .chat-footer .send-btn span").removeClass("vip_btn_disable");
        });
    }
    
    /*
     * 生成渠道经理专属二维码
     */
    $(document).on('click','.userConfig #channel_code',function(res){
       var userid = $('.chat-hidden #user-id').val();
       $.get('/user/channel-code?userid='+userid,function(res){
           //刷新页面
          $('.userConfig #channel_code').remove();
          var html = '<a href="#" id="channel_code_show"><span class="fa fa-circle green"></span> &nbsp;渠道二维码</a>';
          $('.userConfig .channel_qr').append(html);
       })
    })
    
    /*
     * 点击查看渠道经理的专属二维码
     * create by sjy
     */
    
    $(document).on('click','.userConfig #channel_code_show',function(res){
        var userid = $('.chat-hidden #user-id').val();
        $("#qrcodeModal").modal({backdrop: "static"});
        $(" #qrcodeModal .modal-body").empty().text("loading...").load('/user/get-channel-code?userid=' + userid);
    });

    
});