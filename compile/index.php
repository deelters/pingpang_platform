<?php show_template("common_header");?>

<div class="container">
    <?php show_template("common_nav");?>
    <h3 style="text-align: center;margin-top: 10px">所在比赛组挑战列表</h3>
    <p>当前组别：航空港全校 <?php if($my_info['sex']==1){?>男子组<?php }else{?>女子组<?php }?></p>
    <p>我的当前排名：<?php if($now_rankNum < $max_rankNum){?>第 <?php echo $now_rankNum; ?> 名<?php }else{?>暂未上榜<?php }?></p>
    <p>我的当前积分：<?php echo $my_score;?> 分</p>
    <table class="table table-hover table-bordered" style="text-align: center">
    <thead>
        <th>排名</th>
        <th>姓名</th>
        <th>当前积分</th>
        <th>选手编号</th>
        <th>操作</th>
    </thead>
    <tbody>
        <?php for($i=0;$i<$total_num;$i++){?>
        <tr <?php if($_SESSION["user_id"]==$rank_list[$i]['user_id']){?> style="color: red"<?php }else{?> <?php }?>>
            <td><?php echo $rank_list[$i]['rank_num'];?></td>
            <td class="text-nowrap"><?php echo $rank_list[$i]['username'];?></td>
            <td><?php echo $rank_list[$i]['score'];?></td>
            <td><?php echo $rank_list[$i]['user_id'];?></td>
            <td>
                <?php if($btn_enabled[$i]){?>
                <button class="btn btn-danger btn-sm btn-challenge" data-target_ranknum="<?php echo $rank_list[$i]['rank_num'];?>" data-target_name="<?php echo $rank_list[$i]['username'];?>" data-target_id="<?php echo $rank_list[$i]['user_id'];?>">发起挑战</button>
                <?php }else{?>
                <?php }?>
            </td>
        </tr>
        <?php }?>
    </tbody>
</table>
</div>

<script>
    //挑战按钮
    $('.btn-challenge').click(function (){
        let target_id = $(this).data("target_id");
        showModal('提示', '是否确定要挑战该选手?<br><br>选手编号: ' + target_id + '<br>选手姓名: ' + $(this).data('target_name') + '<br>选手名次: 第' + $(this).data('target_ranknum') + "名", function (){
            makeChallenge(target_id);
        });
    });

    //挑战数据上报
    function makeChallenge(target_id)
    {
        $.post({
            url: "/api/setting",
            data: {action:"make_challenge", target_id: target_id},
            success: function (response){
                if (response.status == "success"){
                    showAlert('提示', response.message, 'success', function () {
                        location.reload();
                    });
                }
                else
                {
                    showAlert('提示', response.message ,'error');
                }
            },
            error: function (error){
                showAlert('出错啦', '连接服务器失败, 请稍后重试!', 'error');
            }
        })
    }
</script>

<?php show_template("common_component");?>

<script>
    <?php if( $has_unconfirmed_task){?>
        //待接受挑战提示
        showAlert('提示', '有人向你发起了挑战，<br>请前往“我的挑战”中进行查看!', 'error', function () {

        });
    <?php }else{?>
    <?php }?>
</script>
<?php show_template("common_footer");?>
