<!--模态框对话框显示部分-->
<div class="modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="staticBackdropLabel"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div id="modal-content" class="modal-body">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
        <button id="modal-sure" type="button" class="btn btn-primary">确定</button>
      </div>
    </div>
  </div>
</div>

<!--模态提示框部分-->
<div class="modal fade" id="modal-alert" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
<!--      <div class="modal-header">-->
<!--        <h5 class="modal-title" id="exampleModalLabel"></h5>-->
<!--        <button type="button" class="close" data-dismiss="modal" aria-label="Close">-->
<!--          <span style="font-size: larger" aria-hidden="true">&times;</span>-->
<!--        </button>-->
<!--      </div>-->
      <div class="modal-body">
          <div style="height: auto">
              <i id="modal-icon" class="fa fa-exclamation-circle" style="text-align: center;font-size: 60px;color: red;display: block" aria-hidden="true"></i>
              <div id="modal-alert-tip" style="margin-top: 20px;width: 100%;text-align: center;font-size: 20px;line-height: 30px;display: inline-block"></div>
          </div>
      </div>
      <div class="modal-footer" style="border: none">
        <button id="alert-sure" style="vertical-align: center" type="button" class="btn btn-secondary" data-dismiss="modal">好的</button>
      </div>
    </div>
  </div>
</div>

<script>
    //自封装模态框显示函数(带回调函数)
    function showModal(title, content, callFunc){
        $('#staticBackdropLabel').html(title);
        $('#modal-content').html(content);

        //！！！非常重要 模态框隐藏后取消当前绑定事件
        $('#staticBackdrop').on('hidden.bs.modal', function () {
            $('#modal-sure').off('click');
        });

        $('#modal-sure').on('click', function (){
            $('#modal-sure').off('click');
            callFunc();
            $('#staticBackdrop').modal('hide');
        });
        $('#staticBackdrop').modal('show');
    }

    //自封装提示框(带确认按钮单击回调函数)
    function showAlert(title, content, type, callFunc = function (){})
    {
        if (type == "success")
        {
            $('#modal-icon').attr('class', 'fa fa-check-circle');
            $('#modal-icon').css('color', 'green');
        }
        else if (type == "error")
        {
            $('#modal-icon').attr('class', 'fa fa-exclamation-circle');
            $('#modal-icon').css('color', 'red');
        }

        //！！！非常重要 模态框隐藏后取消当前绑定事件
        $('#modal-alert').on('hidden.bs.modal', function () {
            $('#alert-sure').off('click');
        });

        //调用传入的回调函数
        $('#alert-sure').on('click', function (){
            $('#alert-sure').off('click');
            callFunc();
        });


        $('#modal-alert .modal-title').html(title);
        $('#modal-alert #modal-alert-tip').html(content);
        $('#modal-alert').modal('show');
    }

</script>