<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/15
 * Time: 上午9:23
 */
?>

<div class="update-employe-content">
    <table class="table">
        <tbody>
            <input type="hidden" id="kefu_id"  value="<?= $kefu_info['id'] ?>">
            <tr>
                <td>登录账号</td>
    <!--            username,role,nickname,email,telephone_system_name,telephone_system_pwd-->
                <td><input id="username" type="text" class="form-control" value="<?= $kefu_info['username'] ?>" readonly="true"><span style="color: #ff0000">(必填)</span></td>
            </tr>
            <tr>
                <td>销售姓名</td>
                <td><input id="nick" type="text" class="form-control" value="<?= $kefu_info['nickname'] ?>"><span style="color: #ff0000">(必填)</span></td>
            </tr>
            <tr>
                <td>企业邮箱</td>
                <td><input id="email" type="text" class="form-control" value="<?= $kefu_info['email'] ?>"><span style="color: #ff0000">(必填)</span></td>
            </tr>
            <tr>
                <td>销售类型</td>
                <td>
                    <select id="type" class="form-control" disabled>
                            <option value="0" >普通销售</option>
                            <option value="1" selected>新签客服</option>
                            <option value="4">复购销售</option>
                            <option value="2">销售经理</option>
                            <option value="5" selected>微课客服</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>400账号</td>
                <td><input id="telephone-name" type="text" class="form-control" value="<?= $kefu_info['telephone_system_name'] ?>"></td>
            </tr>
            <tr>
                <td>400密码</td>
                <td><input id="telephone-pwd" type="text" class="form-control" value="<?= $kefu_info['telephone_system_pwd'] ?>"></td>
            </tr>

            <tr>
                <td height="66px">员工名片</td>
                <td >
                    <div  style="float: left">
                        <img id="card-picurl" width="180px"  height="60px" src="<?= $kefu_info['card'] ?>" alt="upload...">
                    </div>
                    <div style="float: left">
                        <input id="card" type="button" value="重新上传"  style="margin-top: 35px;margin-left: 10px">
                    </div>
                </td>


            </tr>
            <tr>
                <td>员工海报</td>
                <td >

                    <div   style="float: left">
                        <img id="poster-picurl" width="180px"  height="60px" src="<?= $kefu_info['poster'] ?>" alt="upload...">
                    </div>
                    <div  style="float: left">
                        <input id="poster" type="button" value="重新上传"  style="margin-top: 35px;margin-left: 10px">
                    </div>

                </td>
            </tr>
            <tr>
                <td>员工二维码</td>
                <td >

                    <div   style="float: left">
                        <img id="qrcode-picurl" width="180px"  height="60px" src="<?= $kefu_info['qrcode'] ?>" alt="upload...">
                    </div>
                    <div  style="float: left">
                        <input id="qrcode" type="button" value="重新上传"  style="margin-top: 35px;margin-left: 10px">
                    </div>

                </td>
            </tr>


            <tr>
                <td>底部banner</td>
                <td >

                    <div   style="float: left">
                        <img id="banner-picurl" width="180px"  height="60px" src="<?= $kefu_info['banner'] ?>" alt="upload...">
                    </div>
                    <div  style="float: left">
                        <input id="banner" type="button" value="重新上传"  style="margin-top: 35px;margin-left: 10px">
                    </div>

                </td>
            </tr>
        </tbody>
    </table>
</div>

<script >

    $(function () {

        //员工管理  上传名片
        var btnUpload = $('.update-employe-content #card');
        new AjaxUpload(btnUpload, {
            action: "/sale/img-upload",
            type:"POST",
            name: 'icon',
            onSubmit: function (file, ext) {
                //文件上传时
                if (ext && /^(jpg|png|jpeg|gif)$/.test(ext.toLowerCase())) {
                    //只上传一个 不可多个筛选
                    this.disable();
                }
                else {
                    return false;
                }

            },
            onComplete: function (file, response) {
                if (response == "0") {
                    swal("上传图片格式不对或尺寸太大!", "", "error");
                }
                else {
                    $(".update-employe-content #card-picurl").attr("src", response);
                }
                this.enable();
            }
        });


        //员工管理  上传海报
        var btnUpload = $('.update-employe-content #poster');
        new AjaxUpload(btnUpload, {
            action: "/sale/img-upload",
            type:"POST",
            name: 'icon',
            onSubmit: function (file, ext) {
                //文件上传时
                if (ext && /^(jpg|png|jpeg|gif)$/.test(ext.toLowerCase())) {
                    //只上传一个 不可多个筛选
                    this.disable();
                }
                else {
                    return false;
                }

            },
            onComplete: function (file, response) {
                if (response == "0") {
                    swal("上传图片格式不对或尺寸太大!", "", "error");
                }
                else {
                    $(".update-employe-content #poster-picurl").attr("src", response);
                }
                this.enable();
            }
        });

        //员工管理  上传二维码
        var btnUpload = $('.update-employe-content #qrcode');
        new AjaxUpload(btnUpload, {
            action: "/sale/img-upload",
            type:"POST",
            name: 'icon',
            onSubmit: function (file, ext) {
                //文件上传时
                if (ext && /^(jpg|png|jpeg|gif)$/.test(ext.toLowerCase())) {
                    //只上传一个 不可多个筛选
                    this.disable();
                }
                else {
                    return false;
                }

            },
            onComplete: function (file, response) {
                if (response == "0") {
                    swal("上传图片格式不对或尺寸太大!", "", "error");
                }
                else {
                    $(".update-employe-content #qrcode-picurl").attr("src", response);
                }
                this.enable();
            }
        });

        //员工管理  底部banner
        var btnUpload = $('.update-employe-content #banner');
        new AjaxUpload(btnUpload, {
            action: "/sale/img-upload",
            type:"POST",
            name: 'icon',
            onSubmit: function (file, ext) {
                //文件上传时
                if (ext && /^(jpg|png|jpeg|gif)$/.test(ext.toLowerCase())) {
                    //只上传一个 不可多个筛选
                    this.disable();
                }
                else {
                    return false;
                }

            },
            onComplete: function (file, response) {
                if (response == "0") {
                    swal("上传图片格式不对或尺寸太大!", "", "error");
                }
                else {
                    $(".update-employe-content #banner-picurl").attr("src", response);
                }
                this.enable();
            }
        });


    })

</script>
