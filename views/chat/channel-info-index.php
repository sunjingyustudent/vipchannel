<div class="channel-info-head">
    <ul class="nav nav-pills" role="tablist"　>
        <li class="active channel_pill" id="new_user_1">
            <a href="#" style="border:1px solid #ddd">微课拉新<span class="badge"><?= $data['new_count'] ?>人</span></a>
        </li>
        <li class="channel_pill" id="new_user_2">
            <a href="#" style="border:1px solid #ddd">陪练拉新 <span class="badge"><?= $data['register_count'] ?>人</span></a>
        </li>
        <li  id="channel_user_3" class="channel_pill">
            <a href="#" style="border:1px solid #ddd" >带来体验人数<span class="badge"><?= $data['ex_count'] ?>人</span></a>
        </li>
        <li   id="useless_user_4" class="channel_pill">
            <a href="#" style="border:1px solid #ddd">带来买单人数  <span class="badge "><?= $data['buy_count'] ?>人 </span></a>
        </li>
        <li   id="useless_user_5" class="channel_pill">
            <a href="#" style="border:1px solid #ddd">带来二级买单人数<span class="badge"><?= $data['two_buy_count'] ?>人</span></a>
        </li>
    </ul>
    <input type="text" class="sale_channel_id" hidden value="<?= $user_id ?>">
</div>
<div class="channel-info-content">

</div>