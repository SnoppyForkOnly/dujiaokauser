@extends('hyper.layouts.default')
@section('content')
<div class="row justify-content-center">
    <div class="col-lg-4">
        <div class="page-title-box">
            <h4 class="page-title">{{ __('hyper.login_title') }}</h4>
        </div>
    </div>
</div>
@if(dujiaoka_config_get('is_open_login') == \App\Models\BaseModel::STATUS_OPEN)
    <div class="row justify-content-center">
        <div class="col-lg-4">
            <div class="card card-body sticky">
                <form id="login" action="{{ url('login') }}" method="post">
                    @csrf
                    <!-- Existing email and password fields -->
                    <div class="form-group">
                        <div class="buy-title">{{ __('hyper.login_email') }}</div>
                        <input type="email" name="email" class="form-control" placeholder="{{ __('hyper.login_email_input') }}">
                    </div>
                    <div class="form-group">
                        <div class="buy-title">{{ __('hyper.login_password') }}</div>
                        <input type="password" name="password" class="form-control" placeholder="{{ __('hyper.login_password_input') }}">
                    </div>
                    
                    <!-- Math question field -->
                      @if(dujiaoka_config_get('is_openlogin_img_code') == \App\Models\Goods::STATUS_OPEN)
                    <div class="form-group">
                        <div class="buy-title">{{ __('数学题必填') }} </div>
                        <label id="math-question"></label>
                        <input type="text" name="math_answer" class="form-control" placeholder="输入结果">
                    </div>
         
                    <!-- Refresh button -->
                    <div class="form-group">
                        <button type="button" class="btn btn-secondary" id="refresh">
                            <i class="mdi mdi-refresh mr-1"></i>
                            {{ __('换一个') }}
                        </button>
                    </div>
                    @endif
                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-primary" id="submit">
                            <i class="mdi mdi-login mr-1"></i>
                            {{ __('hyper.login_submit') }}
                        </button>
                    </div>
                </form>
                <div class="mt-3 text-center">
                    {{ __('hyper.to_tip') }}<a href="{{ url('register') }}">{{ __('hyper.to_register') }}</a>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="row justify-content-center">
        <div class="col-lg-4">
                <div class="alert alert-warning">
                {{ __('hyper.login_title') }}关闭维护中......<a href="/">返回首页</a>
            </div>
        </div>
    </div>
@endif
@stop
@section('js')
<script>
       @if(dujiaoka_config_get('is_openlogin_img_code') == \App\Models\Goods::STATUS_OPEN) 
    function generateMathQuestion() {
        var operator = Math.random() < 0.5 ? '+' : '-';
        var num1 = Math.floor(Math.random() * 100);
        var num2 = Math.floor(Math.random() * 100);


        $('#math-question').text(num1 + ' ' + operator + ' ' + num2 + ' =');

    
        $('#math-question').data('answer', operator === '+' ? num1 + num2 : num1 - num2);
    }


    generateMathQuestion();

  
    $('#refresh').click(function(){
     
        generateMathQuestion();
    });


    $('#submit').click(function(){
      
        var mathAnswer = $("input[name='math_answer']").val();
        if(mathAnswer == ''){
            $.NotificationApp.send("警告","答案不能为空","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }

        // 验证
        var correctAnswer = $('#math-question').data('answer');
        if (parseFloat(mathAnswer) !== correctAnswer) {
            $.NotificationApp.send("警告","答案不正确","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }

        // 随机
        generateMathQuestion();
        
        @endif
        
           $('#submit').click(function(){
        var email = $("input[name='email']").val();
        if(email == ''){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.register_email_input') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        let reg = /^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/;
        if (!reg.test(email)) {
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.register_email_error') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
        var password = $("input[name='password']").val();
        if(password == ''){
            $.NotificationApp.send("{{ __('hyper.buy_warning') }}","{{ __('hyper.register_password_input') }}","top-center","rgba(0,0,0,0.2)","info");
            return false;
        }
    });
    });
</script>
@stop
