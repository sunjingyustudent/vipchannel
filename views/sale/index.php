<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 16/11/9
 * Time: 下午1:57
 */
?>
<div class="employe">
    <div class="search-form">
        <div style="float: left;width: 500px">
            <div style="clear: left">
                <input type="text" id="employe-keyword" class="form-control"
                       placeholder="输入销售姓名进行搜索"  style="margin-right: 10px"/>
            </div>
            <div>
                <select id="status" class="form-control" style="width: 200px" >
                    <option value="0" selected>选择客服状态</option>
                    <option value="1">可用</option>
                    <option value="10">禁用</option>
                </select>
            </div>


        </div>
        <div class="add-sales" >
            <button class="add-employe btn btn-default" id="add-employe"><i class="fa fa-plus"></i></button>
        </div>
    </div>

    <div id="sales-table" style="clear: left" >

    </div>
</div>
<script>
    $(function () {
        $(".employe #employe-date").daterangepicker();
    });
</script>
