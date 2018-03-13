/**
 * Created by mac on 17/2/26.
 */
$(function () {
    /*************************聊天页面按钮**************************/
    //聊天消息按钮
    $(document).on('click', '.body-header .navInfo #new-wait', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=1', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('新用户列表');
        });

    });

    $(document).on('click', '.body-header .navInfo #all-user', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=0', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('全部用户列表');
        });

    });


    $(document).on('click', '.body-header .navInfo #user-of-the-day', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=5', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('当日新用户列表');
        });

    });

    $(document).on('click', '.body-header .navInfo #worth-wait', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=2', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('推广用户消息');
        });

    });

    $(document).on('click', '.body-header .navInfo #withdrawals-wait', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=4', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('提现申请消息');
        });

    });

    $(document).on('click', '.body-header .navInfo #no-worth-wait', function () {

        $("#showModal .modal-body").text("正在加载数据...").load('/chat/chat-user-page?type=3', function () {
            $("#showModal").modal("show");
            $('.modal-title').text('无价值用户消息');
        });

    });

    //  快捷回复
    $(document).on('click', '.chat-center .chat-footer #quick-answer', function () {
        $("#showModal .modal-body").load('/chat/quick-answer-index', function () {
            $("#showModal .modal-body .quick-answer-content .answer-list").load('/chat/quick-answer?type=1');
            $("#showModal .modal-title").text('快捷回复');
            $("#showModal").modal("show");
        });
    });

    // 新用户
    $(document).on('click', '#showModal .quick-answer-content #answer_btn_1', function () {
        $("#showModal .modal-body .quick-answer-content .answer-list").load('/chat/quick-answer?type=1', function () {
            $('#showModal .modal-body .quick-answer-content .answer-type').removeClass('active');
            $('#showModal .modal-body .quick-answer-content #answer_btn_1').addClass('active');
        });
    });

    /**
     *有推广价值用户话术
     */
    $(document).on('click', '#showModal .quick-answer-content #answer_btn_2', function () {
        $("#showModal .modal-body .quick-answer-content .answer-list").load('/chat/quick-answer?type=2', function () {
            $('#showModal .modal-body .quick-answer-content .answer-type').removeClass('active');
            $('#showModal .modal-body .quick-answer-content #answer_btn_2').addClass('active');
        });
    });

    /**
     *无推广价值用户话术
     */
    $(document).on('click', '#showModal .quick-answer-content #answer_btn_3', function () {
        $("#showModal .modal-body .quick-answer-content .answer-list").load('/chat/quick-answer?type=3', function () {
            $('#showModal .modal-body .quick-answer-content .answer-type').removeClass('active');
            $('#showModal .modal-body .quick-answer-content #answer_btn_3').addClass('active');
        });
    });

    /**
     *选择快捷回复
     */
    $(document).on('click', '#showModal .quick-answer-content .answer-list .content', function () {
        $(".chat-center .chat-footer #user-input").val($(this).text());
        $(".chat-center .chat-footer #user-input").focus();
        $("#showModal").modal('hide');
        $('#user-input').focus();
    });

    $(document).on('click', '.table-responsive .table .update', function () {
        var quick_type = $(this).attr("id").split('_')[0],
                quick_id = $(this).attr("id").split('_')[1];

        $('.show').hide();
        $('.show').removeClass('show');

        if (quick_type == 'edit') {
            var content = $(this).prev().prev().text();
            $(this).parent().parent().parent().next().show();
            $('#quick_' + quick_id).parent().parent().show();
            $('#quick_' + quick_id).parent().parent().addClass('show');
            $('#quick_' + quick_id).val(content);

        } else if (quick_type == 'delete') {
            swal({
                title: "确定要删除这条快捷回复？",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "删除",
                cancelButtonText: "取 消",
                closeOnConfirm: false
            }, function () {
                $.get('/chat/delete-qucik-answer?id=' + quick_id, function (res) {
                    if (res == 0) {
                        swal('删除成功', '', 'success');
                        $('#delete_' + quick_id).parent().parent().hide();
                    } else {
                        swal(res, '', 'error');
                    }
                })
            });
        }
    });

    $(document).on('keyup', '.table-responsive .input-quick', function (event) {

        var theEvent = event || window.event;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;

        if (code == 13) {
            event.preventDefault();
            var id = $(this).attr('id').split('_')[1],
                    content = $(this).val();

            $.get('/chat/edit-qucik-answer?id=' + id + '&content=' + content, function (res) {
                if (res == 0) {
                    $('#edit_' + id).prev().prev().text(content);
                    $('#quick_' + id).parent().parent().hide();
                    $('.show').removeClass('show');
                } else {
                    swal(res, '', 'error');
                }
            });
        }
    });


    /**
     * 添加快捷回复
     */
    $(document).on('click', '#showModal .quick-answer-content #add-quick', function () {

        $("#showModal .quick-answer-content .answer-list .table #textCell").remove();

        var content = '<tr id="textCell"><td>'
                + '<textarea rows="5" cols="119" type="text" id="input-quick"></textarea>'
                + '<div>'
                + '<button type="button" class="btn btn-default pull-left" id="cancel-quick">取消</button>'
                + '<button type="button" class="btn btn-success pull-right" id="confirm-quick">保存</button>'
                + '</div></td></tr>';

        $("#showModal .quick-answer-content .answer-list .table").prepend(content);
    });

    $(document).on('click', '#showModal .quick-answer-content #cancel-quick', function () {
        $("#showModal .quick-answer-content .answer-list .table #textCell").remove();
    });

    $(document).on('click', '#showModal .quick-answer-content #confirm-quick', function () {

        if ($(this).hasClass('vip_btn_disable')) {
            return false;
        }

        $(this).addClass('vip_btn_disable');

        var list = '';
        content = $("#showModal .quick-answer-content #input-quick").val(),
                type = $("#showModal .quick-answer-content .active").attr('id').split('_')[2];

        if ($.trim(content).length == 0) {
            swal('回复内容不能为空', '', 'error');
            $("#showModal .quick-answer-content #confirm-quick").removeClass('vip_btn_disable');
            return false;
        }

        var param = {
            "content": content,
            "type": type
        };

        $.post('/chat/add-quick-answer', param, function (res) {
            var result = JSON.parse(res);

            if (result.error == '') {
                swal("添加成功", '', 'success');
                var id = result.data.id;
                $("#showModal .quick-answer-content .answer-list .table #textCell").remove();
                list = '<tr><td><li><a style="color: limegreen" class="content" href="javascript:void(0)">' + content + '</a>' +
                        '<span id="delete_' + id + '" class="update trash_btn label label-danger fa fa-trash-o"></span>' +
                        '<span id="edit_' + id + '" class="update edit_btn label label-warning fa fa-pencil-square-o"></span></li></td></tr>'
                        + '<tr id="textEdit"  style="display: none;" ><td> <textarea class="input-quick" rows="5" cols="119" type="text" id="quick_' + id + '"></textarea></td> </tr>';

                $("#showModal .quick-answer-content .answer-list .table").prepend(list);
            } else {
                swal(result.error, '', 'error');
                $("#showModal .quick-answer-content #confirm-quick").removeClass('vip_btn_disable');
            }
        });
    });




    // 编辑用户
    $(document).on('click', '.rightPanel .right-content .edit-user', function () {
        var openid = this.id;

        $(".body-container .body-edit-user").removeClass('slideInLeft').addClass('slideInRight').show();
        $(".body-container .body-edit-user .edit-user-content").load('/user/edit-user?openid=' + openid);

    });

    $(document).on('click', '.edit-user-header .fa-close', function () {
        $(".body-container .body-edit-user").removeClass('slideInLeft').addClass('slideInRight').hide();
    });


    //用户详情
    $(document).on('click', '.rightPanel .chat-right  #user-detail', function () {
        var user_id = $('.rightPanel .chat-center').attr('sid');

        $("#showModal .modal-body").load('/chat/user-info?user_id=' + user_id, function () {
            $("#showModal .modal-title").text("用户详情");
            $("#showModal").modal("show");
        });
    });

    //跟进信息
    $(document).on('click', '.rightPanel .chat-right  #follow-info', function () {
        var channel_id = $('.rightPanel .chat-center').attr('sid');

        $("#showModal .modal-body").text("正在加载数据...").load('/sale/get-visit-page?channelId=' + channel_id, function () {
            $("#showModal .modal-title").text("跟进信息");
            $("#showModal").modal("show");
        });
    });

    //提交回访记录
    $(document).on('click', '#detailPage-new .add_visit .visit_btn', function () {

        var channel_id = $(".add_visit .channel_id").val(),
                content = $.trim($(".add_visit .content").val()),
                time_next = $(".add_visit .time-next").val(),
                next_content = $.trim($(".add_visit .next-content").val()),
                worth = $(".add_visit #worth :selected").val(),
                text = $('.add_visit #worth :selected').text(),
                classId =  $(".add_visit #classId :selected").val();

        if (content == '') {
            return swal('本次跟进内容没有填写', "", "error");
        }
        if ((!time_next && next_content) || (time_next && !next_content) ) {
            return swal('下次跟进时间和下次跟进内容需要一起填写', "", "error");
        }

        var param = {
            'channel_id': channel_id,
            'content': content,
            'time_next': time_next,
            'next_content': next_content,
            'worth': worth,
            'classId' : classId
        };

        //console.info(param);
        $.post('/sale/add-channel-visit', param, function (res) {

            if (!isNaN(res)) {
                $('.chat-bar .bgchange  .channel-status').text(text);
                if (worth == 2) {
                    $('.chat-bar .bgchange  .channel-status').css('color', 'cornflowerblue');
                } else if (worth == 3) {
                    $('.chat-bar .bgchange  .channel-status').css('color', 'red');
                }
                swal("添加成功", "", "success");
                $("#showModal").modal("hide");
            } else {
                swal(res, "", "error");
            }
        });
    });


    //绑定海报
    $(document).on('click', '.rightPanel .chat-right  #bind-haibao', function () {
        var openid = $(this).data('openid'),
                userid = $(this).data('userid');
        $("#showModal .modal-body ").load('/chat/poster-page?openId=' + openid + '&userId=' + userid, function () {
            $("#showModal .modal-title").text("发送素材");
            var poster_path = $('#select_send_poster option:selected').data('path');
            if (poster_path != null && poster_path.length > 0) {
                $('#change_poster_show').html('<img src="' + poster_path + '" height="263" width="263" />');
            }
            $("#showModal").modal("show");
        });
    });
    


    //发送课单
    $(document).on('click', '.rightPanel .chat-right  #send-class', function () {
        var sale_id = $('.rightPanel .chat-center').attr('sid');

        $("#showModal .modal-body").load('/chat/class-record-list?saleId=' + sale_id + '&type=1', function () {
            $("#showModal .modal-title").text("发送课单");
            $("#showModal").modal("show");
        });
    });

    /*
     * 发送课单种类
     */
    $(document).on('change', '#showModal .modal-body #class_record_list_select', function () {
        var type = $('.class_record_list_head #class_record_list_select').val();
        if(type == 3){
            $('.class_record_list_head #search_name').attr('placeholder','姓名和手机号和微信名');
        }
        var keyword = $('.class_record_list_head #search_name').val();
        var sale_id = $('.class_record_list_salesid').attr('rel');
        var date = $('.class_record_list_head #date-month').val();
        var start = 0;
        var end = 0;
        if (date != '')
        {
            start = $.trim(date.split('-')[0]);
            end = $.trim(date.split('-')[1]);
        }
        $("#showModal .modal-body").load('/chat/class-record-list?saleId=' + sale_id + '&keyword=' + keyword + '&type=' + type + '&start=' + start + '&end=' + end , function () {
            $('.class_record_list_head #class_record_list_select').val(type);
            $('.class_record_list_head #search_name').val(keyword);
            $('.class_record_list_head #date-month').val(date);
            if (type == 3) {
                $('.class_record_list_head #search_name').attr('placeholder', '姓名和手机号和微信名');
            }
        });
    });

    //发送课单搜索
    $(document).on('change', '#showModal .modal-body #search_name', function () {
        var type = $('.class_record_list_head #class_record_list_select').val();
        var keyword = $.trim($('.class_record_list_head #search_name').val());
        var sale_id = $('.class_record_list_salesid').attr('rel');
        var date = $('.class_record_list_head #date-month').val();
        var start = 0;
        var end = 0;
        if (date != '')
        {
            start = $.trim(date.split('-')[0]);
            end = $.trim(date.split('-')[1]);
        }
        $("#showModal .modal-body").load('/chat/class-record-list?saleId=' + sale_id + '&keyword=' + keyword + '&type=' + type + '&start=' + start + '&end=' + end, function () {
            $('.class_record_list_head #class_record_list_select').val(type);
            $('.class_record_list_head #search_name').val(keyword);
            $('.class_record_list_head #date-month').val(date);
             if (type == 3) {
                $('.class_record_list_head #search_name').attr('placeholder', '姓名和手机号和微信名');
            }
        });
    });
    /*
     * 取消课发送客服消息
     */
    $(document).on('click', '.table-striped .sendword', function () {
        var class_id = $(this).attr('id').split('_')[1];
        var sale_id = $('.class_record_list_salesid').attr('rel');

        $.get('/chat/send-cancelex-record?classId=' + class_id + '&saleId=' + sale_id, function (res) {
            var res = JSON.parse(res);
            if (res.error === 0)
            {
                var html = res.data;
                if (html.length > 0) {
                    html = html.replace(new RegExp(/<br>/g), '\r\n').replace(new RegExp(/&nbsp;/g), ' ');
                    $(".chat-center .chat-footer #user-input").val(html);
                    $(".chat-center .chat-footer #user-input").focus();
                    $("#showModal").modal('hide');
                    $('#user-input').focus();
            }
//                swal('发送成功', '', 'success');
            } else {
                swal({
                    title: '当前用户不能推送客服消息，是否改为发送模板消息',
                    text: '',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "发送模板消息",
                    cancelButtonText: "取 消",
                    closeOnConfirm: false
                }, function () {
                    //发送取消体验课的模板消息
                    $.get('/chat/send-cancelex-templet?classId=' + class_id + '&saleId=' + sale_id, function (res) {
                        var res = JSON.parse(res);
                        if (res.error === 0)
                        {
                            swal('发送成功', '', 'success');
                        } else {
                            swal('发送失败', '', 'error');
                        }
                    })
                });
            }
        });
    });
    /*
     * 取消课发送模板消息
     */
    $(document).on('click', '.table-striped .sendmoban', function () {
        var class_id = $(this).attr('id').split('_')[1];
        var sale_id = $('.class_record_list_salesid').attr('rel');
        //发送取消体验课的模板消息
        $.get('/chat/send-cancelex-templet?classId=' + class_id + '&saleId=' + sale_id, function (res) {
            var res = JSON.parse(res);
            if (res.error === 0)
            {
                swal('发送成功', '', 'success');
            } else {
                swal('发送失败', '', 'error');
            }
        });
    });

    /*
     * 发送关注未预约用户名单
     * send_no_ex_student
     */
    $(document).on('click', '#send_no_ex_student', function () {
        var sale_id = $('.class_record_list_salesid').attr('rel');
        var keyword = $(this).attr('rel');
        var date = $('.class_record_list_head #date-month').val();
        var start = 0;
        var end = 0;
        if (date != '')
        {
            start = $.trim(date.split('-')[0]);
            end = $.trim(date.split('-')[1]);
        }
        $.get('/chat/send-noex-record?saleId=' + sale_id + '&keyword=' + keyword + '&start=' + start + '&end=' + end, function (res) {
//            var html = '老师您好，这是您推荐后未预约体验课学生的名单<br>'
//                + '<a href="http://channelwx-test.pnlyy.com/introduce/parent-speak?kid=4">点击查看</a><br>'
//                + '麻烦您可以关照练琴不主动，进步教慢的学生来再次预约体验我们的服务';
            var html = res;
            if (html.length > 0) {
                html = html.replace(new RegExp(/<br>/g), '\r\n').replace(new RegExp(/&nbsp;/g), ' ');
                $(".chat-center .chat-footer #user-input").val(html);
                $(".chat-center .chat-footer #user-input").focus();
                $("#showModal").modal('hide');
                $('#user-input').focus();
            }
        });
    });



    //发送课单之发送话术
    $(document).on('click', '.table-striped .send', function () {
        var class_id = $(this).attr('id').split('_')[1];

        $.get('/chat/send-class-record?classId=' + class_id, function (res) {

            var res = JSON.parse(res);
            if (res.error === 0)
            {
                swal('发送成功', '', 'success');

                    $(".chat-body").append(
                            '<div class="right-message"><div class="avatar">' +
                            '<img src="' + res.data.head + '">' +
                            '</div> ' +
                            '<div class="content"> ' +
                            '<p>' + res.data.message + '</p> ' +
                            '<p class="grey">' + res.data.kefu_name + ' ' + res.data.time + '</p> ' +
                            '</div> ' +
                            '<div class="clearAll"></div> ' +
                            '</div>'
                            );
                $('.rightPanel .chat-body').scrollTop(1000000);

                $("#showModal").modal("hide");
            } else {
                swal('发送失败', '', 'error');
            }


        });
    });


    //发送奖励
    $(document).on('click', '.rightPanel .chat-right  #send-reward', function () {
        var user_id = $('.rightPanel .chat-center').attr('sid');

        $("#showModal .modal-body").load('/chat/send-reward-page?userId=' + user_id, function () {
            $("#showModal .modal-title").text(" 奖励");
            $('#showModal .modal-body .reward-content').load('/chat/send-reward-content?userId=' + user_id, function () {});
            $("#showModal").modal("show");
        });
    });

    //发送奖励操作
    $(document).on('click', '.red_content  .do_send_reward', function () {

        // 获取参数
        var user_id = $(".modal-body  .red_content   input[name='user_id']").val(),
                title = $(".modal-body  .red_content   input[name='title']").val(),
                money = $(".modal-body  .red_content   input[name='money']").val(),
                message = $(".modal-body  input[name='message']").val();

        var param = {
            "user_id": user_id,
            "title": title,
            "money": money,
            "message": message
        };

        if (!title) {
            return swal('请填写红包title', '', 'error');
        }

        if (!money) {
            return swal('请填写金额', '', 'error');
        }

        if (!message) {
            return swal('请填写话术', '', 'error');
        }

        if (isNaN(money)) {
            return swal('请输入数字', '', 'error');
        }

        $('.red_content .do_send_reward').addClass('disabled');


        $.post('chat/do-send-reward', param, function (res) {

            if (res === 'null') {
                swal('发送失败请联系管理员', '', 'error');
                return false;
            }

            var res = JSON.parse(res);
            if (res.error === 0)
            {
                swal('发送成功', '', 'success');
                $('.red_content .do_send_reward').removeClass('disabled');
                $("#showModal").modal("hide");
                    $(".chat-body").append(
                            '<div class="right-message"><div class="avatar">' +
                            '<img src="' + res.data.head + '">' +
                            '</div> ' +
                            '<div class="content"> ' +
                            '<p>' + res.data.message_1 + '</p> ' +
                            '<p class="grey">' + res.data.kefu_name + ' ' + res.data.time + '</p> ' +
                            '</div> ' +
                            '<div class="clearAll"></div> ' +
                            '</div>'
                            );

                    $(".chat-body").append(
                            '<div class="right-message"><div class="avatar">' +
                            '<img src="' + res.data.head + '">' +
                            '</div> ' +
                            '<div class="content"> ' +
                            '<p>' + res.data.message + '</p> ' +
                            '<p class="grey">' + res.data.kefu_name + ' ' + res.data.time + '</p> ' +
                            '</div> ' +
                            '<div class="clearAll"></div> ' +
                            '</div>'
                            );
                $('.rightPanel .chat-body').scrollTop(1000000);
            } else {
                swal(res.error, '', 'error');
                $('.red_content .do_send_reward').removeClass('disabled');
            }

            // if (!isNaN(res)) {
            //     swal('发送成功','','success');
            //     $('.red_content .do_send_reward').removeClass('disabled');
            //     $("#showModal").modal("hide");
            // } else {
            //     swal(res,'','error');
            //     $('.red_content .do_send_reward').removeClass('disabled');
            // }
        })

    });

    //发送奖励操作
    $(document).on('click', '.reward-prompt  #premission', function () {

        var uid = $(".modal-body  .red_content   input[name='user_id']").val();

        $.get('chat/do-open-premission?uid=' + uid, function (res) {
            if (!isNaN(res)) {
                swal('开启权限成功', '', 'success');
            } else {
                swal(res, '', 'warning');
            }
        })

    });

    // 发送奖励之跳转
    $(document).on('click', '.reward-head .reward_pill', function () {
        var user_id = $('.rightPanel .chat-center').attr('sid'),
                type = $(this).attr('id').split('_')[2];

        $('.active').removeClass('active');
        $(this).addClass('active');

        switch (Number(type)) {
            case 1:
                $('#showModal .modal-body .reward-content').load('/chat/send-reward-content?type=1&userId=' + user_id, function () {
                });
                break;
            case 2:
                $('#showModal .modal-body .reward-content').css('width', '100%');
                $('#showModal .modal-body .reward-content').load('/chat/other-reward-record-page?userId=' + user_id, function () {
                });
                break;
            default:
                break;
        }
    });

    //发送奖励之发送历史奖励话术
    $(document).on('click', '.table-striped .send_history_reward', function () {
        var history_id = $(this).attr('id').split('_')[1],
                uid = $(this).attr('id').split('_')[2];

        $.get('/chat/send-hisotry-channel-reward-message?uid=' + uid + '&historyid=' + history_id, function (res) {

            if (!isNaN(res)) {
                swal('发送成功', '', 'success');
            } else {
                swal(res, '', 'error');
            }
        });
    });

    // 听课权限
    $(document).on('click', '.rightPanel .chat-right #listening-competence', function () {
        var open_id = $('.rightPanel #open_id').val();
        $("#showModal .modal-body").load('/chat/listen-class-power?openId=' + open_id, function () {
            $("#showModal .modal-title").text("听课权限");
            $("#showModal").modal("show");
            $("#showModal .modal-body .listen-class-content").load('/chat/listen-class-power-page', function () {
            });
        });
    });

    // 搜索
    $(document).on('click', '.listen-class-power .search_class_btn', function () {
        var open_id = $('.rightPanel #open_id').val(),
                keyword = $('.listen-class-power  #search_class').val();


        $("#showModal .modal-body .listen-class-content").load('/chat/listen-class-power-page?keyword=' + keyword, function () {
        });
    });

    $(document).on('click', '.listen-table .search_power_btn', function () {
        var open_id = $('.listen-class-power  #channel_open_id').val(),
                class_id = $(this).next().val(),
                back_type = $(this).next().next().val();

        $.get('/chat/search-wechat-class?openId=' + open_id + '&classId=' + class_id + '&isBackShare=' + back_type, '', function (res) {

            if (res == 0) {
                return swal('用户这节课拥有权限,请让他稍后再试', '', 'success');

            } else if (res > 0) {
                swal({
                    title: '该用户这节课无权限',
                    text: '(若该用户证明信息完善请手动添加)',
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "添加权限",
                    cancelButtonText: "取 消",
                    closeOnConfirm: false
                }, function () {
                    var class_id = res;

                    var param = {
                        'class_id': class_id,
                        'open_id': open_id,
                        'back_type': back_type
                    };
 
                    $.post('chat/add-wechat-class-info', param, function (res) {
                        if (!isNaN(res)) {
                            swal('添加成功', '', 'success');
                            $('.class_power_' + class_id).text('(有权限)');

                        } else {
                            swal(res, '', 'error');
                        }
                    })
                });
            } else {
                swal(res, '', 'error');
            }
        });

    });

    $(document).on('click', '.listen-class-power .super_class_btn', function () {
        var open_id = $('.listen-class-power  #channel_open_id').val();
        swal({
            title: '确定要一键开启所有权限吗？',
            text: '(确定要一键开启所有权限吗)',
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "添加权限",
            cancelButtonText: "取 消",
            closeOnConfirm: false
        }, function () {
            $.get('/chat/open-super-class?openid=' + open_id, function (res) {
                var res = JSON.parse(res);
                if (res.error == 0) {
                    if (res.data > 0)
                    {
                        swal('成功开启' + res.data + '节课程', '', 'success');
                        $("#showModal .modal-body .listen-class-content").load('/chat/listen-class-power-page', function () {
                        });
                    } else
                    {
                        swal('没有课程需要开启', '', 'success');
                    }
                } else {
                    swal("开启课程失败", '', 'error');
                }
            })
        });
    });

    $(document).on('click', '.listen-table .send-class-link', function () {
        var url = $(this).next().val(),
                title = $(this).next().next().val();

        $("#showModal").modal("hide");
        // $('.body-content .rightPanel #user-input').append('立即查看：<a href="' + url + '">' + title + '</a>');
        $('.body-content .rightPanel #user-input').val('立即查看：<a href="' + url + '">' + title + '</a>');

        $('.body-content .rightPanel #user-input').focus();
    });


    //推广详情
    $(document).on('click', '.rightPanel .chat-right  #channel-info', function () {
        var user_id = $('.rightPanel .chat-center').attr('sid');

        $("#showModal .modal-body").load('/chat/channel-info-index?id=' + user_id, function () {
            $("#showModal .modal-title").text("推广详情");
            $("#showModal").modal("show");

            $("#showModal .channel-info-content").load('/chat/channel-info-page?id=' + user_id + '&type=1', function () {
            });
        });
    });

    $(document).on('click', '.channel-info-head  .channel_pill', function () {
        var user_id = $('.channel-info-head  .sale_channel_id').val(),
                type = $(this).attr('id').split('_')[2];

        $('.channel-info-head .active').removeClass('active');
        $(this).addClass('active');

        $("#showModal .channel-info-content").load('/chat/channel-info-page?id=' + user_id + '&type=' + type, function () {
        });
    });

    // 海报推进界面
    // 销售渠道海报
    $(document).on('click', '.modal-dialog .poster', function () {
        var user_id = $('.rightPanel .chat-center').attr('sid'),
                open_id = $('.rightPanel #open_id').val();

        $("#showModal .modal-body .poster_content").text('...正在加载海报').load('/chat/channel-poster-page?open_id=' + open_id + '&user_id=' + user_id, function () {
            $("#showModal .modal-title").text("销售渠道海报");
        });
    });

    //发送海报和福利卡
    $(document).on('click', '#send-haibao,#send_welfare_qrcode', function () {
        var qrcode = '', openid = $("#select_send").data('openid');
        if ($(this).attr('id') == 'send-haibao') {//海报
            if ($('#select_send_poster').val().length == 0) {
                swal('请先选择海报', '', 'error');
                return;
            }
            if ($('#imgHaibao').length && $("#imgHaibao").attr("src").length > 0) {
                qrcode = $("#imgHaibao").attr("src");
            } else {
                swal('请点击生成海报', '', 'error');
                return;
            }
        } else {//福利卡
            qrcode = $("#show_welfare_qrcode").attr("src");
        }
        if (qrcode == '') {
            swal('生成福利卡失败', '', 'error');
            return;
        } else {
            $(this).text(" 正在发送...").attr('disabled', true);
        }
        if (openid.length == 0) {
            swal('微信号错误', '', 'error');
            return;
        }
        sendWechatPic(openid, qrcode);
    });

    /************************用户页面***********************************************/
    //编辑用户
    $(document).on('click', '.body-container .body-edit-user #Confirm', function () {
        var elem = $(this), val = '';
        var param = $("#edit_user_save").serialize();
        var color_num = $('#worth').val();
        var text = $('#worth').find("option:selected").text();
        $('input[type="checkbox"]:checked').each(function (i) {
            val += $(this).data('name') + ' ';
        })
        $(elem).text('正在提交...').attr('disabled', true);
        $.post('/user/do-edit-user', param, function (res) {
            if (!isNaN(res)) {
                $('.chat-bar .bgchange  .channel-status').text(text);
                if (color_num == 1) {
                    $('.chat-bar .bgchange  .channel-status').css('color', 'lawngreen');
                } else if (color_num == 2) {
                    $('.chat-bar .bgchange  .channel-status').css('color', 'cornflowerblue');
                } else if (color_num == 3) {
                    $('.chat-bar .bgchange  .channel-status').css('color', 'red');
                }
                $('#chat_instrument').html(val);
                swal({title: "提交成功", text: "", type: "success"}, function () {
                    $(".body-container .body-edit-user").removeClass('slideInRight').hide();
                });

            } else {
                sweetAlert(res, '', 'error');
                $(elem).text('确 定').removeAttr('disabled');
            }
        });
    });
    //二维码更换
    $(document).on('click', '.edit-user-update-code', function () {
        var id = $("#edit_user_save").find('#studentID').val(),
            type = $(this).data('type'),
            that = $(this);
        that.attr('disabled', true);
        $.post('/user/update-qrcode',{id:id,type:type},function(data){
            that.removeAttr('disabled');
            if (data.error == '') {
                swal({title: "操作成功", 
                    text: "2秒后自动关闭", 
                    type: "success",
                    timer: 2000,   
                    showConfirmButton: false}, function () {
                    swal.close();
                    $(".body-container .body-edit-user").removeClass('slideInRight').hide();
                });
            } else {
                swal(data.error, '', 'error');
            }
        },'json');
    });
    $(document).on('change', '.body-modal .body-modal-content #todo-time', function () {

        var start = $('.body-modal .body-modal-content #todo-time').val().split(' ')[0],
                end = $('.body-modal .body-modal-content #todo-time').val().split(' ')[2];

        $(".todo-content .todo-body").load('/sale/todo-list?start=' + start + '&end=' + end, function () {

        });
    });

    //用户名单 跳转用户页面
    $(document).on('click', '.user-list .user_pill', function () {
        var type = $(this).attr('id').split('_')[2],
            keyword = $.trim($('.user-list .head #search_name').val()),
            studentPhone = '';
        $('.user-list .active').removeClass('active');
        $(this).addClass('active');
        keyword = encodeURI(keyword);
        $('.user-list .head #student_phone').val('');

        if( type == 1 || type == 2 || type == 3)
        {
            $('.user-list .head #student_phone').show();
        } else {
            $('.user-list .head #student_phone').hide();
        }
        if (type == 5)
        {
            $(".user-list .head #search_date_2").hide();
            $(".user-list .head #search_date_1").show();
            var time = $('.user-list .head #search_date_1').val();
            var reward_type = $(".user-list .head #reward_type>option:selected").val()
            $(".user-list .head #reward_type").show();
            $(".user-list #list-content").load("/user/reward-user-page?time=" + time + "&keyword=" + keyword + '&rewardtype=' + reward_type, function (res) {});

        } else if (type == 6) {
            $(".user-list .head #reward_type").hide();
            $(".user-list .head #search_date_1").hide();
            $(".user-list .head #search_date_2").show();
            var time = $('.user-list .head #search_date_2').val();
            $(".user-list #list-content").load("/user/user-list-page?type=" + type + "&keyword=" + keyword + '&time=' + time, function () {});
        } else {
            $(".user-list .head #reward_type").hide();
            $(".user-list .head #search_date_2").hide();
            $(".user-list .head #search_date_1").hide();
            $(".user-list #list-content").load('/user/user-list-page?type=' + type + '&keyword=' + keyword + '&studentPhone' + studentPhone, function (res) {});
        }
    });

    //用户列表 绑定学生手机号搜索
    $(document).on('change', '.user-list ul li #student_phone', function () {
        var type = $('.user-list .active').attr('id').split('_')[2];
        var studentPhone = $.trim($(this).val());

        if(studentPhone != ''){
            studentPhone = encodeURI(studentPhone);
            $(".user-list #list-content").load('/user/user-list-page?type=' + type + '&keyword=&studentPhone='+ studentPhone);
        } else {
            $(".user-list #list-content").load('/user/user-list-page?type=' + type + '&keyword=&studentPhone=');
        }
    })

    // 奖励名单提醒 王珂
    $(document).on('change', '.user-list #reward_type', function () {
        var reward_type = $('.user-list .head #reward_type option:selected').val();

        var keyword = $.trim($('.user-list .head #search_name').val());
        var time = $('.user-list .head #search_date_1').val();

        keyword = encodeURI(keyword);
        $(".user-list #list-content").load("/user/reward-user-page?time=" + time
                + "&keyword=" + keyword
                + '&rewardtype=' + reward_type, function (res) {});
    });

    // 用户页面搜索
    $(document).on('change', '.user-list #search_name', function () {
        var type = $('.active').attr('id').split('_')[2],
            keyword = $.trim($(this).val()),
            studentPhone = '';
        $('.user-list .head #student_phone').val('');
        keyword = encodeURI(keyword);

        if (type == 5)
        {
            var time = $('.user-list .head #search_date_1').val();
            var reward_type = $('.user-list .head #reward_type option:selected').val();
            $(".user-list .head #search_date_1").show();
            $(".user-list #list-content").load("/user/reward-user-page?time=" + time + "&keyword=" + keyword + '&rewardtype=' + reward_type, function (res) {});
        } else if (type == 6) {
            var time = $('.user-list .head #search_date_2').val();
            $(".user-list .head #search_date_2").show();
            $(".user-list #list-content").load("/user/user-list-page?type=" + type + "&keyword=" + keyword + '&time=' + time, function () {});
        } else {
            $(".user-list .head #search_date_1").hide();
            $(".user-list .head #search_date_2").hide();
            $(".user-list #list-content").load('/user/user-list-page?type=' + type + '&keyword=' + keyword +'&studentPhone=', function (res) {});
        }
    });


    // 用户删除操作
    $(document).on('click', '.user-list .delete_btn', function () {
        var id = $(this).attr('id').split('_')[1];
        swal({
            title: '删除用户',
            text: '(确定要删除该用户吗？)',
            type: "error",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确 认",
            cancelButtonText: "取 消",
            closeOnConfirm: false
        }, function () {

            var param = {
                'id': id
            };

            $.post('user/do-delete-user', param, function (res) {
                if (!isNaN(res)) {
                    swal('删除成功', '', 'success');
                } else {
                    swal(res, '', 'error');
                }
            });
        });
    });


    // // 推广效果
    // $(document).on('change', ".promotion-effect-list #time-range", function () {
    //     var start = $('.promotion-effect-list .head #time-range').val().split(' - ')[0],
    //         end = $('.promotion-effect-list .head #time-range').val().split(' - ')[1];
    //     var url = "/sale/promotion-effect-page?start=" + start + "&end=" + end;
    //     $(".promotion-effect-list  #list-content").load(url);
    // });

    //发送素材
    $(document).on('click', "#select_send>li", function () {
        var that = $(this),
                index = that.index(),
                userid = $('#select_send').data('userid'),
                openid = $('#select_send').data('openid');
        that.addClass('active').siblings('li').removeClass('active');
        $('.main_content').hide().eq(index).show();
        //福利卡
        if (index == 1) {
            if ($('#show_welfare_qrcode').attr('src').length == 0) {
                $.post('/chat/create-welfare-card', {openid: openid}, function (data) {
                    if (data.error == '')
                    {
                        $('#show_welfare_qrcode').attr('src', data.data);
                    } else {
                        swal(data.error, '', 'error');
                    }
                }, 'JSON');
            }
        }
        //拉老师海报
        if (index == 3) {
            if ($('#show_welfare_qrcode').attr('src').length == 0) {
                $.post('/chat/create-temporary-poster', {userid: userid}, function (data) {
                    if (data.error == '') {
                        $('#teacher_temporary_poster').attr('src', data.data);
                    } else {
                        swal(data.error, '', 'error');
                    }
                }, 'JSON');
            }
        }

        //名片默认选中一张图片
        if (index == 4) {
            var radio = $('#select_send_type input');
            if (!radio.is(':checked')) {
                radio.each(function (i, e) {
                    if (radio.eq(i).data('picurl').length > 0) {
                        img = '<img id="channel_card" height="263" width="263" src="' + radio.eq(i).data('picurl') + '"/>';
                        $('.select_send_type.per').html(img);
                        radio.eq(i).attr("checked", "checked");
                        return false;
                    }
                });
            }
        }
    });

    //选择海报
    $(document).on('change', "#select_send_poster", function () {
        var poster_path = $('#select_send_poster option:selected').data('path'),
                img = '<img src="' + poster_path + '" height="263" width="263" />';
        $('#change_poster_show').html(img);
    });

    //生成海报
    $(document).on('click', "#create_send_poster", function () {
        if ($('#select_send_poster').val().length == 0) {
            $('#imgHaibao').attr('src', '');
            swal('请先选择海报', '', 'error');
            return;
        }
        $(this).text('正在生成海报...').attr('disabled', true);
        var poster_id = $('#select_send_poster').val(),
                open_id = $('#select_send').data('openid'),
                user_id = $('#select_send').data('userid');
        $.post('/chat/channel-poster-page', {open_id: open_id, user_id: user_id, poster_id: poster_id}, function (data) {
            if (data.error == '') {
                var img = '<img id ="imgHaibao" src="' + data.data + '" height="350" width="350" />';
                $('.main_content #banner').html(img);
            } else {
                swal(data.error, '', 'error');
            }
            $('#create_send_poster').text('生成海报').attr("disabled", false);
        }, 'JSON');
    });

    //选择内容介绍页 和勾选链接
    $(document).on('change', '#select-introduce-page,#bottom-qrcode', function () {
        var that = $('#select-introduce-page option:selected'),
                id = that.val(),
                url = that.data('url'),
                content = that.data('content'),
                kefu_id = $('#select_send').data('kefuid');
        if ($(this).val().length == 0)
            content = '';
        if ($(this).attr('id') == 'bottom-qrcode') {//checkbox
            $('#bottom-qrcode').is(':checked') ? (url += '/' + kefu_id) : (url += '/0');
            $('#introduce-page').find('>a:last').attr('href', url);
        } else {//select
            url += '/' + kefu_id;
            if (content)
                content = that.data('content') + '<br><a href="' + url + '">点击查看</a>';
            if (!$('#bottom-qrcode').is(':checked'))
                $('#bottom-qrcode').trigger('click');
            $('#introduce-page').html(content);
        }
    });

    //发送介绍页
    $(document).on('click', '#send_introduce', function () {
        var html = $('#introduce-page').html();
        if (html.length > 0) {
            html = html.replace(new RegExp(/<br>/g), '\r\n').replace(new RegExp(/&nbsp;/g), ' ');
            $(".chat-center .chat-footer #user-input").val(html);
            $(".chat-center .chat-footer #user-input").focus();
            $("#showModal").modal('hide');
            $('#user-input').focus();
        } else {
            swal('请先选择介绍页', '', 'error');
        }
    });

    //发送名片
    $(document).on('click', '#select_send_type input', function () {
        var pic_url = $(this).data('picurl');
        img = '<img id="channel_card" height="263" width="263" src="' + pic_url + '"/>';
        $('.select_send_type.per').html(img);
    });

    //图片发送类型过多，单独写发送名片
    $(document).on('click', '#send-channel-pic', function () {
        var pic_url = $('#channel_card').attr('src'),
                openid = $("#select_send").data('openid');

        if (pic_url != undefined && pic_url.length > 0) {
            $(this).text("正在发送...").attr('disabled', true);
            sendWechatPic(openid, pic_url, 'qn');
        } else {
            swal('没有可选择图片', '', 'error');
        }
    });

    //发送老师临时二维码海报
    $(document).on('click', '#send_teacher_temporary_poster', function () {
        var picurl = $('#teacher_temporary_poster').attr('src'),
                openid = $("#select_send").data('openid');
        if (picurl.length > 0) {
            $(this).text(" 正在发送...").attr('disabled', true);
            sendWechatPic(openid, picurl);
        } else {
            swal('请先选择图片', '', 'error');
        }
    });

    //发送微信图片公用方法
    function sendWechatPic(openid, picurl, type) {
        $.post('/chat/do-send-haibao', {
            "openid": openid,
            "fpath": picurl,
            "type": type
        }, function (res) {
            var list = '<div class="right-message" >';
            list += '<div class="avatar"><img src="' + res.head + '"></div>';
            list += '<div class="content"><img class="wechat-img" src="' + res.url + '">';
            list += '<p class="grey">' + res.date + '</p></div>';
            list += '<div class="clearAll"></div>';

            $(".chat-body").append(list);
            $('.chat-body').scrollTop(1000000);

            $("#showModal").modal("hide");
        }, 'JSON');
    }
});
