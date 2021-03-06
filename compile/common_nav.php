<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #e3f2fd;">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item <?php if($controller_name == 'index'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/index">发起挑战<span class="sr-only">(current)</span></a>
      </li>
<!--      <li class="nav-item <?php if($controller_name == 'divide'){?>active<?php }else{?><?php }?>">-->
<!--        <a class="nav-link" href="/divide">开幕赛分组<span class="sr-only">(current)</span></a>-->
<!--      </li>-->
      <li class="nav-item <?php if($controller_name == 'total'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/total/rank/1">全校实时榜单</a>
      </li>
      <li class="nav-item <?php if($controller_name == 'mytask'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/mytask">我的挑战</a>
      </li>
      <li class="nav-item <?php if($controller_name == 'myscore'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/myscore">我的积分</a>
      </li>
      <li class="nav-item <?php if($controller_name == 'myinfo'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/myinfo" tabindex="-1" aria-disabled="true">我的信息</a>
      </li>
      <?php if($role_id == 2){?>
      <li class="nav-item <?php if($controller_name == 'admin'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/admin/index" tabindex="-1" aria-disabled="true">管理后台</a>
      </li>
      <li class="nav-item <?php if($controller_name == 'start'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/start.php" target="_blank" tabindex="-1" aria-disabled="true">分组管理</a>
      </li>
      <?php }else{?>
      <?php }?>
      <li class="nav-item <?php if($controller_name == 'loginout'){?>active<?php }else{?><?php }?>">
        <a class="nav-link" href="/api/loginout">退出登录</a>
      </li>
    </ul>
  </div>
  <a class="navbar-brand" href="#">欢迎您，<?php echo $username;?></a>
</nav>
