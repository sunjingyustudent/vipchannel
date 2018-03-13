/**
 * Created by mac on 17/2/26.
 */
$(function () {

    /*
    $(document).on('click', '.navInfo #new-user', function () {
        $("#showModal .modal-body").text("正在加载数据...").load('/chat/new-user-page',function () {
            $("#showModal").modal("show");
            $('.modal-title').text('新用户列表');
        });
    });

    $(document).on('click', '.studentPage .fixTime-link', function () {
        var student_id = $(this).attr("id").split('_')[1];
        $("#commonModal .modal-body").load('/student/student-fix-time?student_id=' + student_id, function () {
            $("#commonModal .modal-title").text("固定时间");
            $("#commonModal .modal-footer .confirm_btn").attr("id", 'time-confirm');
            $("#commonModal").modal("show");
        });
    });
    */　
   $('.norepay').on({
        click:function(){
            loaddata(1)
        },
        mouseenter:function(e){
            loaddata(0);
        }
   });
    function loaddata(type){
        if($(".body-alert-message").is(':hidden')){
            setTimeout(function() {
               $.post('chat/get-no-repay-info','',function(data){
                    //消息数
                    var message_count = data.count>99?99:data.count;
                    $(".norepay").find('span').html(message_count);
                    if(message_count>0){
                        var content = '';
                        //列表
                        $(".body-alert-message .alert-content").empty();
                        $(".body-alert-message .title").text('未回复用户');
                        for (var i = 0; i < data.data.length; i++) {
                            var nickname = data.data[i].nickname==null?'无':data.data[i].nickname;
                            content += '<span id="' + data.data[i].bind_openid + '" class="alert-message label label-warning" >' + data.data[i].name + '(' + nickname + ')</span>';
                        }
                        content += '<div class="clear"></div>';
                        $(".body-alert-message .alert-content").append(content);
                        $(".body-alert-message").removeClass('slideInRight').addClass('slideInRight').show();
                    }else{              
                        $(".body-alert-message").removeClass('slideInRight').hide();
                    }
                },'JSON');　
            },300);　
        }else{
            if(type){
                $(".body-alert-message").removeClass('slideInRight').hide();
            }
        }
    }
});
