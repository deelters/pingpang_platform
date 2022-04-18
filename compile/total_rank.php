<?php show_template("common_header");?>
<div class="container">
    <?php show_template("common_nav");?>
    <h3 style="text-align: center;margin-top: 10px">航空港全校实时榜单</h3>

    <ul class="nav nav-tabs">
        <?php for($i=0;$i<$groups_count;$i++){?>
        <li class="nav-item">
            <a class="nav-link <?php if($query_group_id == $groups_info[$i]['group_id']){?>active<?php }else{?><?php }?>" href="/total/rank/<?php echo $groups_info[$i]['group_id'];?>"><?php echo $groups_info[$i]['group_name'];?></a>
        </li>
        <?php }?>
    </ul>

    <br>
    <h3 style="text-align: center"><?php if( $query_group_id == 1){?> 男子组 <?php }else{?> 女子组 <?php }?></h3>
    <table class="table table-bordered" style="text-align: center">
        <thead>
            <th>排名</th>
            <th>姓名</th>
            <th>当前积分</th>
            <th>选手编号</th>
        </thead>
        <tbody>
            <?php for($i=0;$i<$count_rank_list;$i++){?>
            <tr <?php if($rank_list[$i]['user_id'] == $user_id){?>style="color:red"<?php }else{?><?php }?>>
                <td><?php echo $rank_list[$i]['rank_num'];?></td>
                <td><?php echo $rank_list[$i]['username'];?></td>
                <td><?php echo $rank_list[$i]['score'];?></td>
                <td><?php echo $rank_list[$i]['user_id'];?></td>
            </tr>
            <?php }?>
        </tbody>
    </table>

</div>
<?php show_template("common_footer");?>
