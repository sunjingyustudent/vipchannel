/**
 * Created by Administrator on 2017/4/10.
 */
$(function () {
    //员工管理 搜索
    $(document).on('keydown', '.employe .search-form #employe-keyword', function(e){
        //回车事件
        var theEvent = e || window.event;
        var code = theEvent.keyCode || theEvent.which || theEvent.charCode;
        if(code == 13){
            var param = {
                "keyword":$(this).val(),
                "status":0,
            };
            $(".employe #sales-table").load('/sale/employe-page', param);
        }
    });

    //员工管理 状态改变
    $(document).on('change','.employe .search-form #status',function () {
        var status = $('.employe .search-form #status option:selected').val();

        var param = {
            "keyword":'',
            "status":status,
        };
        $(".employe #sales-table").load('/sale/employe-page', param);
    });

    //添加员工 界面
    $(document).on('click', '.employe .search-form #add-employe', function () {
        $("#commonModal .modal-body").text('正在加载页面...').load('/sale/add-employe');
        $("#commonModal .modal-title").text('添加员工');
        $("#commonModal").modal("show");
        $("#commonModal .modal-footer .confirm_btn").attr("id", 'confirm-add-employe');
    });


    //员工管理  添加员工操作
    $(document).on('click', '#commonModal #confirm-add-employe', function () {
        var param = {
            "username":$("#commonModal .add-employe-content #username").val(),
            "nick":$("#commonModal .add-employe-content #nick").val(),
            "email":$("#commonModal .add-employe-content #email").val(),
            "type":5,
            "telephone_name":$("#commonModal .add-employe-content #telephone-name").val(),
            "telephone_pwd":$("#commonModal .add-employe-content #telephone-pwd").val(),
            'card': $("#commonModal .add-employe-content #card-picurl").attr("src"),
            'poster': $("#commonModal .add-employe-content #poster-picurl").attr("src"),
            'qrcode': $("#commonModal .add-employe-content #qrcode-picurl").attr("src"),
            'banner': $("#commonModal .add-employe-content #banner-picurl").attr("src"),
        };

        if(param.username.length <= 0)
        {
            $("#commonModal .add-employe-content #username").focus();
            swal('请填写登录账号','','warning');
            return false;

        }
        if(param.nick.length <= 0)
        {
            $("#commonModal .add-employe-content #nick").focus();
            swal('请填写销售姓名','','warning');
            return false;
        }
        if(param.email.length <= 0)
        {
            $("#commonModal .add-employe-content #email").focus();
            swal('请填写企业邮箱','','warning');
            return false;
        }

        $.post('/sale/do-add-course-kefu', param, function (res) {
            var result = JSON.parse(res);
            console.log(result);
            if (result.error == '')
            {
                swal("操作成功", "", "success");
                $("#commonModal").modal("hide");
                $(".body-modal .refresh_it").click();  //因为currentPage = 12没有加到全局中
            }else {
                swal(result.error,'','error');
            }
        });
    });


    //员工管理 编辑界面
    $(document).on('click', '.employe #sales-table .update-employe', function () {

        var kefu_id = $(this).attr('id').split('_')[1];
        $("#commonModal .modal-body").text('正在加载页面...').load('/sale/update-employe?kefuid='+kefu_id);
        $("#commonModal .modal-title").text('修改微课顾问');
        $("#commonModal").modal("show");
        $("#commonModal .modal-footer .confirm_btn").attr("id", 'confirm-update-employe');

    });

    //修改员工操作
    $(document).on('click', '#commonModal #confirm-update-employe', function () {
        var param = {
            "kefu_id":$("#commonModal .update-employe-content #kefu_id").val(),
            "nick":$("#commonModal .update-employe-content #nick").val(),
            "email":$("#commonModal .update-employe-content #email").val(),
            "type":5,
            "telephone_name":$("#commonModal .update-employe-content #telephone-name").val(),
            "telephone_pwd":$("#commonModal .update-employe-content #telephone-pwd").val(),

            'card': $("#commonModal .update-employe-content #card-picurl").attr("src"),
            'poster': $("#commonModal .update-employe-content #poster-picurl").attr("src"),
            'qrcode': $("#commonModal .update-employe-content #qrcode-picurl").attr("src"),
            'banner': $("#commonModal .update-employe-content #banner-picurl").attr("src"),
        };

        if(param.nick.length <= 0)
        {
            $("#commonModal .update-employe-content #nick").focus();
            swal('请填写销售姓名','','warning');
            return false;
        }
        if(param.email.length <= 0)
        {
            $("#commonModal .update-employe-content #email").focus();
            swal('请填写企业邮箱','','warning');
            return false;
        }

        if((param.telephone_name.length <= 0 && param.telephone_pwd.length > 0)
            ||(param.telephone_name.length > 0 && param.telephone_pwd.length <= 0) )
        {
            $("#commonModal .update-employe-content #telephone_name").focus();
            swal('请全部填写400账号和400密码','','warning');
            return false;
        }

        $.post('/sale/do-update-employe', param, function (res) {
            var result = JSON.parse(res);
            console.log(result);
            if (result.error == '')
            {
                swal("操作成功", "", "success");
                $("#commonModal").modal("hide");
                $(".body-modal .refresh_it").click();//因为currentPage = 12没有加到全局中
            }else {
                swal(result.error,'','error');
            }
        });
    });



    //禁用员工
    $(document).on('click', '.employe #sales-table .del', function () {
        var param = {
            "kefu_id":$(this).attr('id').split('_')[1],
            'del_type': 0
        };

        var delobj = $(this);

        swal({
            title: "是否禁用?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确 定",
            cancelButtonText: "取 消"
        }, function(){
            delobj.removeClass("del label label-warning label-primary")
                .addClass("process label label-default")
                .text("正在处理..");

            $.post('/sale/delete-kefu', param, function (res) {
                var result = JSON.parse(res);
                obj = $(".employe #sales-table  #kefu_" + result.data.kefu_id + " .process");

                if (result.error == '')
                {
//                        obj.siblings().hide();
//                        obj.hide();

                    $(".body-modal .title").text("员工管理");
                    $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/employe',function () {
                        var param = {
                            "keyword":'',
                            "status":0,
                        };
                        $(".employe #sales-table").load('/sale/employe-page', param, function (res) {

                        });
                    });

                }else {
                    swal(result.error, '', 'error');

                    $(".employe #sales-date #kefu_" + result.data.kefu_id + " .process")
                        .removeClass("process label label-default")
                        .addClass("del label label-warning")
                        .text("禁用");
                }
            });
        });
    });


    //启用员工
    $(document).on('click', '.employe #sales-table .open', function () {
        var param = {
            "kefu_id":$(this).attr('id').split('_')[1],
        };

        var delobj = $(this);

        swal({
            title: "是否启用?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确 定",
            cancelButtonText: "取 消"
        }, function(){
            delobj.removeClass("del label label-warning label-primary")
                .addClass("process label label-default")
                .text("正在处理..");

            $.post('/sale/open-employe', param, function (res) {
                var result = JSON.parse(res);
                obj = $(".employe #sales-table  #kefu_" + result.data.kefu_id + " .process");

                if (result.error == '')
                {
                    $(".body-modal .title").text("员工管理");
                    $(".body-modal .body-modal-content").text("正在加载数据...").load('/sale/employe',function () {
                        var param = {
                            "keyword":'',
                            "status":0,
                        };
                        $(".employe #sales-table").load('/sale/employe-page', param, function (res) {

                        });
                    });

                }else {
                    swal(result.error, '', 'error');

                    $(".employe #sales-date #kefu_" + result.data.kefu_id + " .process")
                        .removeClass("process label label-default")
                        .addClass("del label label-warning")
                        .text("禁用");
                }
            });
        });
    });


    /**
     * 日上班时间
     */
    $(document).on("click",'.employe #sales-table #work_time',function () {
        var kefu_id = $(this).parent().parent().parent().attr("id").split("_")[1];
        $("#commonModal .modal-body").load('/sale/work-time', function () {
            $("#commonModal #myModalLabel").html('编辑内容');

            $("#commonModal #kid").val(kefu_id);
            $("#commonModal #work_week").removeClass('active');
            $("#commonModal #work_day").addClass('active');
            $("#commonModal #work_time")
                .empty()
                .load('/sale/get-work-time?kefuid=' + kefu_id + '&time=' + '&type=1');
            $("#commonModal .modal-footer .confirm_btn").attr("id", 'confirm-work-time');
            $("#commonModal").modal();
        });
    });

    $(document).on("click",'#commonModal #work_day',function () {
        var kefu_id = $("#commonModal #kid").val();
        $("#commonModal #work_week").removeClass('active');
        $("#commonModal #work_day").addClass('active');

        $("#commonModal #work_time")
            .empty()
            .load('/sale/get-work-time?kefuid=' + kefu_id + '&time=' + '&type=1');
        $("#commonModal").modal();

    });

    $(document).on("click",'#commonModal #work_week',function () {
        var kefu_id = $("#commonModal #kid").val();
        $("#commonModal #work_day").removeClass('active');
        $("#commonModal #work_week").addClass('active');
        $("#commonModal #work_time").empty();
        $("#commonModal #work_time").load('/sale/get-kefu-fixed-time?kefuid=' + kefu_id);
        $("#commonModal").modal();

    });

    $(document).on("click",'#commonModal #work_time #do-add',function () {
        var html;
        var id;
        var tem;
        var string = $(this).parent().next().children().last().attr('id');

        if(typeof(string) == "undefined")
        {
            tem = 'day_0';

        }else{
            id = parseInt(string.split('_')[1]) + 1;
            tem = 'day_'+id;
        }
//            alert(string);return false;

        html = '<div class="workTimeCell" id='+tem+'>'
            + '<div class="timeLabel">'
            + '<input type="text" class="form-control timeInputField" placeholder="时间" value="00:00">'
            + '<span class="workTimeQuote">-</span>'
            + '<input type="text" class="form-control timeInputField" placeholder="时间" value="00:00">'
            + '<button id="do-delete" type="button" class="btn btn-xs">'
            + '<span class="fa fa-minus" aria-hidden="true"></span></button>'
            + '</div></div>';
        $(this).parent().next().append(html);
    });

    $(document).on("click",'#commonModal #work_time #do-delete',function () {
        $(this).parent().parent().remove();
    });

    $(document).on("click",'#commonModal #work_time #reset',function () {
        $(this).prev().empty();
    });

    $(document).on("click",'#commonModal .modal-footer #confirm-work-time',function (){
        var id = $("#commonModal #work_time>:first").attr('id');

        if(id == 'day')
        {
            var time = $("#commonModal #work-day-time").val();
            var kefu_id = $("#commonModal #kid").val();

            var i,
                result,
                inputs,
                param = {'kefu_id':kefu_id, 'time':time, 'fix_info':[]},
                jsonStr = '{',
                eachCell = $("#commonModal ").find(".workTimeCell");

            for(i = 0; i < eachCell.length; i ++)
            {
                var id = $(eachCell[i]).attr('id');
                inputs = $("#commonModal #"+id).find('input');
                jsonStr += '"time_start":' + '"' + $(inputs[0]).val() + '"' + ',';
                jsonStr += '"time_end":' + '"' + $(inputs[1]).val() + '"' + '}';

                param.fix_info.push(JSON.parse(jsonStr));

                jsonStr = '{';
            }

            $.post('/sale/add-work-time', param, function (re) {
                if(re == 1){
                    swal({title: "修改成功!", text: "", type: "success"}, function () {
                        $.getJSON('/sale/get-work-time?kefuid=' + kefu_id + '&time=' + time + '&type=2', function (re) {

                            var string = "";
                            $('#commonModal #work_time #day-time').empty();
                            $.each(re,function (index,item) {

                                string += '<div class="workTimeCell" id="day_' + index + '" >'
                                    + '<div class="timeLabel">'
                                    + '<input type="text" class="form-control timeInputField" placeholder="时间" value="' + item.start + '" >'
                                    + '<span class="workTimeQuote"> - </span>'
                                    + '<input type="text" class="form-control timeInputField" placeholder="时间" value="' + item.end + '" >'
                                    + '<button id="do-delete" type="button" class="btn btn-xs" >'
                                    + '<span class="fa fa-minus" aria-hidden="true" ></span>'
                                    + '</button></div ></div >';
                            });
                            $('#commonModal #work_time #day-time').append(string);
                        });
                    });
                }else{
                    swal('修改失败,请联系技术人员!','','error');
                }
            });
        }else
        {
            var time = $("#work-week-time").val();
            var kefu_id = $("#commonModal #kid").val();
            var time_list = new Array;
            var i,
                result,
                inputs,
                param = {'kefu_id':kefu_id, 'time':time, 'fix_info':[]},
                weekCell = $("#commonModal ").find(".weekDayCell");

            for(i = 0;i < weekCell.length; i ++)
            {
                var jsonStr = '{';
                var week_id = $(weekCell[i]).attr('id');

                var eachCell = $("#commonModal #"+week_id).find(".workTimeCell");
                for(j = 0; j < eachCell.length; j ++)
                {
                    var day_id = $(eachCell[j]).attr('id');

                    inputs = $("#commonModal #" + week_id + " #" +day_id).find('input');
                    jsonStr += '"week":' + '"' + week_id + '"' + ',';
                    jsonStr += '"time_start":' + '"' + $(inputs[0]).val() + '"' + ',';
                    jsonStr += '"time_end":' + '"' + $(inputs[1]).val() + '"' + '}';
                    time_list.push(JSON.parse(jsonStr));
                    jsonStr = '{';
                }
                if(time_list.length == 0){
                    time_list = {
                        "0" : {"week": week_id,
                            "time_start": '00:00',
                            "time_end": '00:00'

                        }
                    }
                }

                param.fix_info.push(time_list);
                time_list = [];

            }
            $.post('/sale/add-kefu-fixed-time',param,function (re) {
                if(re == 1){
                    swal({title: "修改成功!", text: "", type: "success"}, function () {
                        $("#commonModal #work_time").empty();
                        $("#commonModal #work_time").load('/sale/get-kefu-fixed-time?kefuid=' + kefu_id);
                        $("#commonModal").modal();
                    });
                }else{
                    swal('修改失败,请联系技术人员!','','error');
                }
            });
        }
    });

    // 全部客户 用户页面搜索
    $(document).on('change', '.all-user-list #search_name, .all-user-list  #kefu-type', function () {
        $('.all-user-list #student_phone').val('');
        var type = $('.active').attr('id').split('_')[2],
            keyword = $.trim($('.all-user-list #search_name').val()),
            kefutype = $('.all-user-list  #kefu-type option:selected').val()
            keyword = encodeURI(keyword);
        $('.all-user-list #student_phone').val('');
        $(".all-user-list .head #search_date").hide();
        $(".all-user-list #list-content").load('/user/all-user-list-page?type='+ type +"&kefutype="+ kefutype +'&keyword='+keyword + '&studentPhone=', function (res) {});
    });

    //全部用户 绑定学生手机号搜索
    $(document).on('change', '.all-user-list #student_phone', function () {
        var studentPhone = $.trim($(this).val());
        var type = $('.active').attr('id').split('_')[2];
        if(studentPhone != ''){
            studentPhone = encodeURI(studentPhone);
            $(".all-user-list .head #search_date").hide();
            $(".all-user-list #list-content").load('/user/all-user-list-page?type='+ type +'&studentPhone='+ studentPhone);
        } else {

            $(".all-user-list #list-content").load('/user/all-user-list-page?type='+ type +'&kefutype=0&keyword=&studentPhone=');
        }
    })

    // 全部用户type=0、新用户type=1、推广用户type=2、无价值用户type=3、可伶红包用户type=4、
    $(document).on('click', '.all-user-list .user_pill', function () {
        $('.all-user-list #student_phone').val('');
        var type = $(this).attr('id').split('_')[2],
            keyword = $.trim($('.all-user-list .head #search_name').val()),
            kefutype = $('.all-user-list  #kefu-type option:selected').val();
        keyword = encodeURI(keyword);

        $('.all-user-list .active').removeClass('active');
        $(this).addClass('active');

        $(".all-user-list .head #search_date").hide();
        $(".all-user-list #list-content").load('/user/all-user-list-page?type='+ type+"&kefutype="+ kefutype + '&keyword=' + keyword + '&studentPhone=', function (res) {});

    });


    //全部用户  分配客服
    $(document).on('click', '.all-user-list .table .distrbute_btn', function () {
        var user_id = $(this).attr('id').split('_')[1];
        var  elm =  $(this);
        var old_kefu = $(this).attr('sid');
        var kefu = $(".all-user-list #kefu>option:selected").val();

        if(old_kefu == kefu){
            swal('不能和原来客服相同！！！', '', 'error');
            return false;
        }

        var param = {
            "user_id":user_id,
            "kefu_id":kefu
        };

        $(this).removeClass("distrbute_btn label label-primary")
            .addClass("process label label-default")
            .text("正在处理..");

        $.post('/user/distribute', param, function (res) {
            var result = JSON.parse(res);
            var obj = $(".all-user-list .table #" + result.data.user_id + " .process ");

            if (result.error == '')
            {
                obj.removeClass("process label label-default")
                    .addClass("distrbute_btn label label-primary")
                    .text("重新分配");
                $(".all-user-list .table #" + result.data.user_id + " .kefu-nick li").text(result.data.nick);


                console.log(kefu);
                elm.attr('sid',kefu);
            }else {
                swal(result.error, '', 'error');
            }
        });
    });

    // 全部用户 用户删除操作
    $(document).on('click', '.all-user-list .delete_btn', function () {
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

            $.post('user/do-delete-all-user',param, function(res){
                if(!isNaN(res)) {
                    swal('删除成功','','success');
                } else {
                    swal(res,'','error');
                }
            });
        });
    });



    //预约课报表
    //0 每日注册  1每日体验
    $(document).on('click', '.ex-class-report .user_type', function () {
        $('.ex-class-report #register-status').prop('selectedIndex',0);
        $('.ex-class-report #ex-status').prop('selectedIndex',0);
        $('.ex-class-report #kefu-type').prop('selectedIndex',0);
        $('.ex-class-report #date').val('');
        $('.ex-class-report #date').attr('placeholder','默认今天');

        var type = $(this).attr('id').split('_')[1];
        var status = 0;
        var date = 0;
        var kefuid = 0;

        if(type == 0)
        {
            $(".ex-class-report .head #type_0").addClass('active');
            $(".ex-class-report .head #type_1").removeClass('active');
            $(".ex-class-report .head #ex-status").hide();
            $(".ex-class-report .head #register-status").show();
        }
        else
        {
            $(".ex-class-report .head #type_1").addClass('active');
            $(".ex-class-report .head #type_0").removeClass('active');
            $(".ex-class-report .head #ex-status").show();
            $(".ex-class-report .head #register-status").hide();
        }

        $(".ex-class-report #list-content").load('/report/ex-class-report-page?type='+ type
            +'&date='+ date
            +'&status='+ status
            +'&kefuid=' + kefuid);
    });


    //关注或体验的状态改变
    $(document).on('change','.ex-class-report #register-status, .ex-class-report #ex-status',function () {
        var type = $('.ex-class-report .active').attr('id').split('_')[1];
        var status = 0;
        var date =  $('.ex-class-report #date').val();
        var kefuid = $('.ex-class-report #kefu-type option:selected').val();

        console.log(type);

        if(type == 0)
        {
            $(".ex-class-report .head #type_0").addClass('active');
            $(".ex-class-report .head #type_1").removeClass('active');
            $(".ex-class-report .head #ex-status").hide();
            $(".ex-class-report .head #register-status").show();
            status = $('.ex-class-report #register-status option:selected').val();
        }
        else
        {
            $(".ex-class-report .head #type_1").addClass('active');
            $(".ex-class-report .head #type_0").removeClass('active');
            $(".ex-class-report .head #ex-status").show();
            $(".ex-class-report .head #register-status").hide();
            status = $('.ex-class-report #ex-status option:selected').val();
        }


        $(".ex-class-report #list-content").load('/report/ex-class-report-page?type='+ type
            +'&date='+ date
            +'&status='+ status
            +'&kefuid=' + kefuid);
    })



    $(document).on('change','.ex-class-report #kefu-type',function () {
        var type = $('.ex-class-report .active').attr('id').split('_')[1];
        var status = 0;
        var date =  $('.ex-class-report #date').val();
        var kefuid = $('.ex-class-report #kefu-type option:selected').val();

        if(type == 0)
        {
            $(".ex-class-report .head #type_0").addClass('active');
            $(".ex-class-report .head #type_1").removeClass('active');
            $(".ex-class-report .head #ex-status").hide();
            $(".ex-class-report .head #register-status").show();
            status = $('.ex-class-report #register-status option:selected').val();
        }
        else
        {
            $(".ex-class-report .head #type_1").addClass('active');
            $(".ex-class-report .head #type_0").removeClass('active');
            $(".ex-class-report .head #ex-status").show();
            $(".ex-class-report .head #register-status").hide();
            status = $('.ex-class-report #ex-status option:selected').val();
        }


        $(".ex-class-report #list-content").load('/report/ex-class-report-page?type='+ type
            +'&date='+ date
            +'&status='+ status
            +'&kefuid=' + kefuid);
    })

    /**
     * 预约课报表  聊天
     */
    $(document).on('click', '.ex-class-report #detailPage tbody tr td .talk_btn_ex', function () {
        var open_id = $(this).attr('id').split('_')[1],
            page_id = $(".chat-hidden #page_id").val();

        console.log('opendid = '+open_id+',page_id='+page_id)

        $.get('/chat/check-connect?page_id=' + page_id, function (check) {
            if (check == 1) {
                swal('您已断开连接', '', 'warning');
                return false;
            }

            $.getJSON('/chat/check-talk', {"open_id": open_id}, function (res) {
                if (res.error == '') {
                    $(".body-menu .menu-list").find("li").removeClass('selected');
                    $(".body-modal").removeClass('fadeInUp').addClass('fadeOutDown');
                    $(".body-content .rightPanel").load('/chat/access-talk?open_id=' + open_id, function () {
                        $(".body-content .leftPanel .chat-bar").load('/chat/left-user', function () {


                            $(".body-content .tool-bar .link-list").remove();
                            var list = '<span id="link-history" class="link-list fa fa-history"> 历史接待</span>';

                            $(".body-content .tool-bar a").append(list);
                            $(".body-content .leftPanel").attr('name', 'connecting');


                            $(".rightPanel .chat-body").height($(".body-chat-home").height() - 175);
                            $('.rightPanel .chat-body').scrollTop(1000000);

                            $.getJSON('/chat/get-link', {"open_id": open_id}, function (data) {
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

                        console.log(sendData);
                        socket.send(sendData);

                        swal('请求已发送', '', 'success');
                    });

                }
            });
        });
    });

    //微信聊天窗口的点亮功能
    $(document).on('click','.rightPanel .center-content .lighten-user',function () {
        var open_id = $(this).attr('id');
        var click_span = $(this).find('span');
        setTimeout(function() {
            $.post('/user/lighten-user', {open_id:open_id}, function (res) {
                if(res.error=='')
                {
                    if(click_span.hasClass('label-success'))
                    {
                        click_span.text('未添加').removeClass('label-success').addClass('label-warning');
                    }
                    else
                    {
                        click_span.text('已添加').removeClass('label-warning').addClass('label-success');
                    }  
                }
                else
                {
                    swal(res.error,'','error');
                }
            },'JSON');
        },300);
    })    
    //转渠道效果
    $(document).on('click','.transfer_channel_click',function () {
        var that = $(this),
            id = that.data('id'),
            status = that.data('status'),
            sid = that.data('sid');
            $("#showModal .modal-body").text("正在加载数据...").load('/user/show-transfer-reward?id=' + id, function () {
                $("#showModal .modal-title").text("发送奖励");
                $("#showModal").modal("show");
            });
    });
    //用户买单记录
    $(document).on('click',"#transfer_block > li",function(event){
        event.preventDefault();
        var that = $(this),
            index = that.index(),
            sid = that.data('sid');
        $(".transfer-content").eq(1).html("");
        if(index==1)
        {   
            $(".transfer-content").eq(1).text("正在加载数据...").load('/user/get-order-page?sid=' + sid, function () {
            });
        }
        that.addClass('active').siblings('li').removeClass('active');
        $('.transfer-content').hide().eq(index).show();
    });

    //提交转渠道奖励
    $(document).on('click','#transfer_reward_form_submit',function () {
        swal({
            title: "是否确认发送奖励",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "确 定",
            cancelButtonText: "取 消"
        }, function(){
            $(this).attr('disabled',true);
            var params = $('#transfer_reward_form_submit').closest('form').serialize();
            $.post('/user/do-transfer-reward',params,function(data){
                if(data.error){
                    swal(data.error, '', 'error');
                    $(this).attr('disabled',false);
                    return false;
                }
                swal({title: "提交成功", text: "", type: "success"}, function () {
                    $('#transfer_list').find('tr').each(function(i){
                        var tag = $(this).find("td:last").find('a');
                        var data_id = tag.data('id');
                        if(data_id == $('#transfer_id').val())
                        {
                            tag.html('详情');
                            return ;
                        }
                    });
                    $("#showModal").modal('hide');
                });return false;
            },'JSON');
        });
    });

    //转渠道列表触发搜索
    $(document).on('change','#transfer_from .form-control',function(){
        var url = $('#transfer_from').serialize();
        url = "/user/transfer-page?"+url;
        $(".channel-transfer-list .list-transfer").load(url);
    });




    //拉老师和体验课状态改变
    $(document).on('change', '.month-gift #user_type, .month-gift #kefu-type', function () {
        var user_type = $('.month-gift #user_type').val();
        var kefuId = $('.month-gift #kefu-type').val();
        var gift_sdate = 0;
        var gift_edate = 0;
        var rang = $('.month-gift #date-range').val();
        if(rang != '')
        {
            gift_sdate = $.trim(rang.split('-')[0]);
            gift_edate = $.trim(rang.split('-')[1]);
        }
        $(".month-gift-content").load('/chat/month-gift-page?start=' + gift_sdate
            + '&end=' + gift_edate
            + '&usertype=' + user_type
            + '&kefuId=' + kefuId);
    })

    //微信聊天窗口的跟进信息的处理功能
    $(document).on('click','#detailPage-new div.histort_visit .is_done',function () {
        var visit_id = $(this).attr('id');
        var is_done = $(this).attr('sid');
        var click_span = $(this).find('span');
        var done_span = $(this);
        
        //console.info(visit_id +';'+ is_done + ';');
        if (is_done == 1) {
            return false;
        }
        $.post('/sale/done-visit', {visitId:visit_id}, function (res) {
            if(res.error=='')
            {
                click_span.text('已跟进').removeClass('label-danger').addClass('label-success');
                done_span.attr('sid',1);
                $('#detailPage-new > div.histort_visit_view > div.visit_done_count > h3 > span').text(res.data.nowNeedDoneCount);
            }
            else
            {
                swal(res.error,'','error');
            }
        },'JSON');
    })

    //待跟进发名单的默认条数

    showTodoListCount()
    //防止冒泡不用mouseover
    $(document).on('mouseenter','.navInfo #todo-stuff', showTodoListCount)

    function showTodoListCount() {
        setTimeout(function () {
            $.get('/sale/show-todolist-count', function (res) {
                if(res.error=='')
                {
                    $(".navInfo .todolist-count").remove();
                    if (res.data.count != 0) {
                        var count = '<div class="todolist-count">' + res.data.count + '</div>';
                        $('.navInfo #todo-stuff').prepend(count);
                    }
                }
            },'JSON')
        },300)
    }

    // 羊毛党客服选择
    $(document).on('change','.wool-party #kefu-type', function () {
        var kefuId = $('.wool-party #kefu-type option:selected').val();
        $(".wool-party #list-content").load('/user/wool-party-page?kefuId=' + kefuId);
    })

    // 羊毛党列表设为无价值
    $(document).on('click','.wool-party #list-content .set_type', function () {
        var obj = $(this);
        if($(this).hasClass('label-success')){
            return false;
        }
        // console.log(obj.attr('id'));
        $.post('/user/wool-set-type', {'id':obj.attr('id')}, function (res) {
            if(res.error=='')
            {
                obj.text('已操作').removeClass('label-danger').addClass('label-success');
            }
            else
            {
                swal(res.error,'','error');
            }
        },'JSON');
    });
})


