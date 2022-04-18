<?php show_template("common_header");?>
<div class="container">
    <?php show_template("common_nav");?>
    <br>
    <h3 style="text-align: center">我的积分明细列表</h3>
    <br>
    <?php for($i=0;$i<$total_num;$i++){?>
    <table class="table table-bordered" style="text-align: center">
        <tr>
            <td>编号</td>
            <td><?php echo $i + 1;?></td>
        </tr>
        <tr>
            <td>领取时间</td>
            <td><?php echo $detail_list[$i]['get_time'];?></td>
        </tr>
        <tr>
            <td>对手信息</td>
            <td><?php echo $detail_list[$i]['target_name'];?>&nbsp;(<?php echo $detail_list[$i]['target_id'];?>&nbsp;号)</td>
        </tr>
        <tr>
            <td>奖励积分</td>
            <td>+&nbsp;<?php echo $detail_list[$i]['get_score'];?></td>
        </tr>
        <tr>
            <td>累计积分</td>
            <td><?php echo $detail_list[$i]['rest_score'];?></td>
        </tr>
        <tr>
            <td>赛前我的排名</td>
            <td>
                <?php if($detail_list[$i]['master_ranknum'] > $max_rankNum[$sex]){?>
                暂未上榜
                <?php }else{?>
                第<?php echo $detail_list[$i]['master_ranknum'];?>名
                <?php }?>
            </td>
        </tr>
        <tr>
            <td>赛前对手排名</td>
            <td>
                <?php if($detail_list[$i]['target_ranknum'] > $max_rankNum[$sex]){?>
                暂未上榜
                <?php }else{?>
                第<?php echo $detail_list[$i]['target_ranknum'];?>名
                <?php }?>
            </td>
        </tr>
    </table>
    <br><br>
    <?php }?>
</div>
<?php show_template("common_component");?>
<?php show_template("common_footer");?>
