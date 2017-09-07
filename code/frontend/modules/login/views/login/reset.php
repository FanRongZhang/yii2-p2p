<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '登录界面';
    AppAsset::register($this);
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="regbox1">
            <div class="clearfix reg-titbox">
                <h1 class="fl">找回登录密码</h1>
                <div class="fr gray1 f18">已有账号，<a href="<?php echo Url::to(['/login/login/login']);?>" class="blue1">前去登录</a> </div>
            </div>
            <div class="clearfix reg-main">
                <div  class="retrieval-box">
                    <?php $form = ActiveForm::begin([
			            'id' => 'login-form',
			            'options' => ['class' => ''],
			        ]); ?>
                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">手机号码</label>
                                <div class="reginput-box fl">
                                    <div><input type="text" class="register-input mem-mobile" placeholder="请输入已注册的手机号码" name="UMember[mobile]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners mobile"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">图形验证码</label>
                                <div class="reginput-box fl">
                                    <div class="form-groupts imgcaptcha">
                                        <input type="text"  class="register-input fl register-width mem-captcha" placeholder="请输入图形验证码">
                                        <span id="captcha">
                                        <?php echo yii\captcha\Captcha::widget(['name'=>'verifyCode','captchaAction'=>'login/captcha','imageOptions'=>['id'=>'captchaimg', 'title'=>'换一个', 'alt'=>'换一个', 'style'=>'cursor:pointer;margin-left:10px;'],'template'=>'{image}']);?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners captcha"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">短信验证码</label>
                                <div class="reginput-box fl clearfix">
                                    <div class="form-groupts clearfix fl">
                                        <input type="text" class="register-input register-width mem-code" placeholder="请输入短信验证码" name="UMember[code]">
                                    </div>
                                    <button onclick='sendcode()' class="regyzdbtn coneget-btn fl" type="button">获取验证码</button>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners code"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div>
                            <div class="clearfix">
                                <label class="fl reg-label">重置登录密码</label>
                                <div class="reginput-box fl">
                                    <div><input type="password" class="register-input mem-pass" placeholder="请输入6~18位数字+字母组合" name="UMember[password]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners password"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>
                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label"></label>
                                <div class="regtiest fl mar15">
                                    <button type="submit" class="regbitntes">提交</button>
                                </div>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

    $(function(){

        var ok1 = ok2 = ok3 =ok4 = false;

        $(".mobile").css('display', 'none');
        $(".code").css('display', 'none');
        $(".password").css('display', 'none');
        $(".captcha").css('display', 'none');
        $('.mem-captcha').val('');

        $(".regbitntes").click(function() {
            var mobile = $('.mem-mobile').val();
            if(mobile == ''){
                $(".mobile").show();
                $(".mobile").text('手机号不能为空！');
                ok1 = false;
            }else if(!(/^1[34578]\d{9}$/.test(mobile))){
                $(".mobile").show();
                $(".mobile").text('手机号码格式不正确！');
                ok1 = false;
            }else{
                $(".mobile").css('display', 'none');
                ok1 = true;
            }
            //验证图形验证码
            var captcha = $('.mem-captcha').val();
            if(captcha == ''){
                $(".captcha").show();
                $(".captcha").text('验证码不能为空！');
                ok4= false
                return false;
            }else{
                $(".captcha").css('display', 'none');
                ok4=true
            }
            var code = $('.mem-code').val();
            if(code == ''){
                $(".code").show();
                $(".code").text('短信验证码不能为空！');
                ok2 = false;
            }else{
                $(".code").css('display', 'none');
                ok2 = true;
            }
            var password = $('.mem-pass').val();
            var p = /[0-9]/;
            var q = /[a-z]/i;
            var reg =/\s/;
            if(password == ''){
                $(".password").show();
                $(".password").text('密码不能为空！');
                ok3 = false;
            }else if(password.length > 18 || password.length < 6){
                $(".password").show();
                $(".password").text('密码长度需在6-18位之间');
                ok3 = false;
            }else if(!p.test(password) || !q.test(password)){
                $(".password").show();
                $(".password").text('密码需为字母和数字组合');
                ok3 = false;
            }else if(reg.test(password)){
                $(".password").show();
                $(".password").text('密码不能包含空格');
                ok3 = false;
            }else if(!/^[0-9a-zA-Z]*$/g.test(password)){
                $(".password").show();
                $(".password").text('密码不能包含特殊字符');
                ok3 = false;
            }else{
                $(".password").css('display', 'none');
                ok3 = true;
            }
            if(ok1 && ok2 && ok3 && ok4){
                return true;
            } else {
                return false
            }

        });
    });
    //验证码发送
    function sendcode(){

        // 初始化
        $(".captcha").hide();
        $(".captcha").text('');
        $(".code").hide();
        $(".code").text('');
        $(".mobile").hide();
        $(".mobile").text('');

        var mobile = $('.mem-mobile').val();
        if(mobile == ''){
            $(".mobile").show();
            $(".mobile").text('手机号不能为空！');
            return false
        }else if(!(/^1[34578]\d{9}$/.test(mobile))){
            $(".mobile").show();
            $(".mobile").text('手机号码格式不正确！');
            return false;
        }else{
            $(".mobile").css('display', 'none');
        }
        //验证图形验证码
        var captcha = $('.mem-captcha').val();
        if(captcha == ''){
            $(".captcha").show();
            $(".captcha").text('验证码不能为空！');
            return false;
        }else{
            $(".captcha").css('display', 'none');
        }

        // 发送短信验证码
        var posturl = '<?php echo Url::toRoute("/login/login/sendcode");?>';

        $.post(
            posturl,
            {mobile:mobile,type:8, captcha:captcha},
            function(result){

            console.log(result);
            if (result['status'] == 'success') {

                //发送验证码后的60秒倒计时
                var time = null;

                if($('.regyzdbtn').hasClass("btn-titgrya") || time) return;

                var i = 59;
                $('.regyzdbtn').addClass("btn-titgrya");
                $('.regyzdbtn').html("剩余"+ (i+1) + "秒");
                time = setInterval(function(){
                    if(i == 0){
                        time = clearInterval(time);
                        $('.regyzdbtn').removeClass("btn-titgrya")
                        $('.regyzdbtn').html("获取验证码");
                        $('.regyzdbtn').attr("onclick",'sendcode()');
                    }else{
                        $('.regyzdbtn').html("剩余" + i + "秒");
                        $('.regyzdbtn').removeAttr('onclick');
                        i--;
                    }
                },1000);

                //刷新验证码
                changeVerifyCode();

            }else{

                // 图形验证错误
                if(result['error_type'] == 'captcha'){
                    $(".captcha").show();
                    $(".captcha").text(result['msg']);
                }else if(result['error_type'] == 'sendcode'){
                    $(".code").show();
                    $(".code").text(result['msg']);
                }else{
                    $(".mobile").show();
                    $(".mobile").text(result['msg']);
                }
                //刷新验证码
                changeVerifyCode();
            }
        }, 'json');
    };

    //更改或者重新加载验证码
    function changeVerifyCode() {
        $.ajax({
        //使用ajax请求reg/captcha方法，加上refresh参数，接口返回json数据
        url: "/login/login/captcha?refresh",
        dataType: 'json',
        cache: false,
        success: function (data) {
        //将验证码图片中的图片地址更换
        $("#captchaimg").attr('src', data['url'])
        }
        });
    }

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>