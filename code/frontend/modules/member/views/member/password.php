<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = '修改密码';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="fr memberright">
    <div class="member-main-box1">
        <div class="member-passwordts">
            <form action="<?=Url::to(['member/member-password'])?>" method="post">
                <div class="reg-maints1">
                    <div class="clearfix">
                        <label class="fl reg-label">原登陆密码</label>
                        <div class="reginput-box fl">
                            <div><input id="old_password" type="password" name="UMember[old_password]" class="register-input" placeholder="请输入原登陆密码"></div>
                        </div>
                    </div>
                    <div id="valid_old" class="valid-text tel-vercode titners" style="display: none"><i class="officicon yzicont"></i>密码有误，请重新输入</div>
                    <div id="valid_old_type" class="valid-text tel-vercode titners" style="display: none"><i class="officicon yzicont"></i>密码格式错误</div>
                </div>
                <div class="reg-maints1">
                    <div class="clearfix">
                        <label class="fl reg-label">新登录密码</label>
                        <div class="reginput-box fl">
                            <div><input id="new_password" name="UMember[new_password]" type="password" class="register-input" placeholder="请输入6~18位数字+字母组合"></div>
                        </div>
                    </div>
                    <div id="valid_new" class="valid-text tel-vercode titners" style="display: none"><i class="officicon yzicont"></i>密码由6~18位数字+字母组合!</div>
                </div>
                <div class="reg-maints1">
                    <div class="clearfix">
                        <label class="fl reg-label">确认登录密码</label>
                        <div class="reginput-box fl">
                            <div><input id="confirm_password" name="UMember[confirm_password]" type="password" class="register-input" placeholder="请输入6~18位数字+字母组合"></div>
                        </div>
                    </div>
                    <div id="valid_confirm" class="valid-text tel-vercode titners" style="display: none"><i class="officicon yzicont"></i>俩次密码输入不相同</div>
                </div>
                <div class="reg-maints1">
                    <div class="clearfix">
                        <label class="fl reg-label"></label>
                        <div class="regtiest fl mar15">
                            <button id="pass" class="regbitntes btn-login-pass">提交</button>
                            <?php
                            if(!empty($error_msg)){
                                echo '<div id="error-msg" class="valid-text tel-vercode titners"><i class="officicon yzicont"></i>'.$error_msg.'</div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <?php $this->registerJsFile('@web/js/jquery.js'); ?>

    <?php $this->beginBlock('test'); ?>

    $('#old_password').blur(function(){
    if($('#error-msg') != undefined){
    $('#error-msg').remove();
    }

    var password = this.value;
    if(verifyPassword(password) == false){
    $('#valid_old_type').show();
    return false;
    }
    if(oldPasswordCheck(password) == false){
    return false;
    }
    return true;
    });

    $('#old_password').focus(function(){
    $('#valid_old_type').hide();
    $('#valid_old').hide();
    });

    $('#new_password').focus(function(){
        $('#valid_new').hide();
    });

    $('#confirm_password').focus(function(){
    $('#valid_confirm').hide();
    });

    $('#new_password').blur(function(){
        if($('#error-msg') != undefined){
            $('#error-msg').remove();
        }
        var password = this.value;
        if(verifyPassword(password) == false){
            $('#valid_new').show();
            return ;
        }

        if(password == $('#old_password').val()){
            $('#valid_new').text('新旧密码不能相同');
            $('#valid_new').show();
        }
        validPsw();
        return true;
    });

    $('#confirm_password').blur(function(){
        if($('#error-msg') != undefined){
        $('#error-msg').remove();
    }
    var password = this.value;
    var new_password = $('#new_password').val();
    if(password != new_password){
    $('#valid_confirm').show();
    return false;
    }
    return true;
    });

    $('#pass').click(function(){

        var old_password = $('#old_password').val();
        var new_password = $('#new_password').val();
        var confirm_password = $('#confirm_password').val();

        if(verifyPassword(old_password) == false){
            $('#valid_old').show();
            return false;
        }
        if(oldPasswordCheck(old_password) == false){
            return false;
        }
        
        if(verifyPassword(new_password) == false){
            $('#valid_new').show();
            return false;
        }

        if(confirm_password != new_password){
            $('#valid_confirm').show();
            return false;
        }

        if(validPsw() == false){
            $('#valid_new').show();
            return false;
        }
        
        return true;
    });

    function verifyPassword(password)
    {
        var reg = /^[A-Za-z0-9]{6,18}$/;
        if(!reg.test(password)){
            return false;
        }else{
            return true;
        }
    }

    function oldPasswordCheck(password){
        var url = '<?php echo Url::to("confirm-password");?>';
        $.post(
        url,
        {password:password},
        function(res){
            if(res == 0){
                $('#valid_old').show();
                return false;
            }

            return true;
        }
        );
    }
    
    function validPsw() {
        var password = $('#new_password').val();
        var num = 0;  
        var number = 0 ;  
        var letter = 0 ;  
        var bigLetter = 0 ;  
        var chars = 0 ;  
          
        if (password.search(/[0-9]/) != -1) {  
            num += 1;  
            number =1;  
        }  
        if (password.search(/[A-Z]/) != -1) {  
            num += 1;  
            bigLetter = 1 ;  
        }  
        if (password.search(/[a-z]/) != -1) {  
            num += 1;  
            letter = 1 ;  
        }  
        if (password.search(/[^A-Za-z0-9]/) != -1) {  
            num += 1;  
            chars = 1 ;  
        }  
        
        if (num >= 2 && (password.length >= 6 && password.length <= 18)) {
            $("#valid_new").remove();
            return true;
        }else if(password.length < 6 || password.length > 18){
            $("#valid_new").html("密码由6~18位数字+字母组合!");
            return false;
        }else if(num == 1){  
            if(number==1){ 
                $("#valid_new").css('display','block').html("不能全为数字!");
                return false;
            }  
            if(letter==1){  
                $("#valid_new").css('display','block').html("不能全为字母!");
                return false;
            }  
            if(bigLetter==1){  
                $("#valid_new").css('display','block').html("不能全为字母!");
                return false;
            }  
            if(chars==1){  
                $("#valid_new").css('display','block').html("不能全为字符!");
                return false;
            }
        }  
    }  

    <?php $this->endBlock()  ?>
    <!-- 将数据块 注入到视图中的某个位置 -->
    <?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>
