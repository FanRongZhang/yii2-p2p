<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '我要借款';
    AppAsset::register($this);
    $this->registerJsFile('@web/js/jquery.js');
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="main-loan-box">
        <div class="q-wide">
                <div class="con-loan-box">
                    <div class="con-loan-back">
                       <form method="post">

                                <div class="reg-maints1">
                                    <div class="clearfix">
                                        <label class="fl reg-label">借款性质</label>
                                        <div class="reginput-box fl">
                                            <div class="loants-fontes">抵押贷</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="reg-maints1">
                                    <div class="clearfix">
                                        <label class="fl reg-label">借款金额(元)</label>
                                        <div class="reginput-box fl">
                                            <div><input id='money' name='money' type="text" class="register-input" placeholder="请输入借款金额"></div>
                                        </div>
                                    </div>
                                    <div class="valid-text tel-vercode titners money"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                                </div>
                               <div class="reg-maints1">
                                   <div class="clearfix">
                                       <label class="fl reg-label">借款周期(天)</label>
                                       <div class="reginput-box fl">
                                           <div><input id='sey' name='sey' type="text" class="register-input" placeholder="请输入借款周期"></div>
                                       </div>
                                   </div>
                                   <div class="valid-text tel-vercode titners sey"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                               </div>

                               <div class="reg-maints1">
                                   <div class="clearfix">
                                       <label class="fl reg-label">借款抵押物</label>
                                       <div class="reginput-box fl">
                                           <div><input id='guarantee' name='guarantee' type="text" class="register-input" placeholder="房、车等具备流通价值的物品"></div>
                                       </div>
                                   </div>
                                   <div class="valid-text tel-vercode titners guarantee"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                               </div>
                               <div class="reg-maints1">
                                   <div class="clearfix">
                                       <label class="fl reg-label">借款用途说明</label>
                                       <div class="reginput-box fl">
                                           <div><textarea id='purpose' class="register-textarea" name="purpose" placeholder="请输入说明内容"></textarea></div>
                                       </div>
                                   </div>
                                   <div class="valid-text tel-vercode titners purpose"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                               </div>
                               <div class="reg-maints1">
                                   <div class="clearfix">
                                       <label class="fl reg-label">借款人姓名</label>
                                       <div class="reginput-box fl">
                                           <div><input id='name' name='name' type="text" class="register-input" placeholder="请输入您的姓名"></div>
                                       </div>
                                   </div>
                                   <div class="valid-text tel-vercode titners name"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                               </div>
                               <div class="reg-maints1">
                                   <div class="clearfix">
                                       <label class="fl reg-label">借款人手机号码</label>
                                       <div class="reginput-box fl">
                                           <div><input name='tel' id='tel' type="text" class="register-input" placeholder="请输入您的手机号码，我们会联系您！"></div>
                                       </div>
                                   </div>
                                   <div class="valid-text tel-vercode titners tel"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                               </div>

                                <div class="reg-maints1">
                                    <div class="clearfix">
                                        <label class="fl reg-label"></label>
                                        <div class="regtiest fl mar15">
                                            <button type='submit' class="regbitntes">提交</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
        </div>
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

$(function(){
  var ok1 = ok2 = ok3 = ok4 = ok5 =ok6 = false;

  var re = /^[0-9]+$/ ;

  $(".sey").css('display', 'none');
  $(".guarantee").css('display', 'none');
  $(".purpose").css('display', 'none');
  $(".money").css('display', 'none');
  $(".name").css('display', 'none');
  $(".tel").css('display', 'none');

  $('.regbitntes').click(function(){
    //验证金额
    var money=$('#money').val();
    if (money == '') {
      $('.money').show();
      $('.money').text('金额不可为空');
    } else if(!re.test(money)) {
      $('.money').show();
      $('.money').text('金额格式不正确');
    } else if(money > 1000000) {
      $('.money').show();
      $('.money').text('借款最大金额为1000000元');
    } else if(money < 0) {
      $('.money').show();
      $('.money').text('借款金额不得小于零');
    } else {
      $(".money").css('display', 'none');
      ok1=true;
    }
    //验证周期
    var sey=$('#sey').val()
    if (sey == '') {
      $('.sey').show();
      $('.sey').text('借款周期不可为空');
    } else if(!re.test(sey)) {
      $('.sey').show();
      $('.sey').text('借款周期格式不正确');
    } else if(sey < 0) {
      $('.sey').show();
      $('.sey').text('借款周期不得小于零');
    } else {
      $(".sey").css('display', 'none');
      ok2=true;
    }
    //验证抵押物
    var guarantee=$('#guarantee').val()
    if (guarantee == '') {
      $('.guarantee').show();
      $('.guarantee').text('抵押物信息不可为空');
    } else {
      $(".guarantee").css('display', 'none');
      ok3=true
    }
    //借款用途
    var purpose=$('#purpose').val()
    if (purpose == '') {
      $('.purpose').show();
      $('.purpose').text('借款用途不可为空');
    } else {
      ok4=true
    }
    //借款人姓名
    var name=$('#name').val()
    if (name =='') {
      $('.name').show();
      $('.name').text('借款人不可为空');
    } else {
      $(".name").css('display', 'none');
      ok5=true
    }
    //手机号
    var tel=$('#tel').val();
    if (tel == '') {
      $('.tel').show();
      $('.tel').text('借款人电话不可为空');
    } else if(!(/^1[34578]\d{9}$/.test(tel))) {
      $('.tel').show();
      $('.tel').text('借款人电话格式不正确');
    } else {
      $(".tel").css('display', 'none');
      ok6=true
    }
    if(ok1 && ok2 && ok3 && ok4 && ok5 && ok6){
    return true;
      }else{
        ok1 = ok2 = ok3 = ok4 = ok5 =ok6 = false;
        return false;
      }
  })
})
<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>