<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
             {!! dujiaoka_config_get('rjtitle') !!}
        </title>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
  

        <style>
            html,
        body {
            margin: 0;
            padding: 0;
        }

        body, input {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        *,
        *:before,
        *:after {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            font-family: "Poppins", sans-serif;
        }

        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #d4fce4;
        }

        .container {
            position: relative;
            border-radius: 100vw;
            width: 340px;
            height: 70px;
        }

        .ripple {
            position: absolute;
            width: 180px;
            height: 70px;
            z-index: 90;
            right: 0;
            transition: -webkit-transform 0.2s;
            transition: transform 0.2s;
            transition: transform 0.2s, -webkit-transform 0.2s;
            transition: transform 0.2s;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 0 25px;
            overflow: hidden;
            border-radius: 100vw;
            pointer-events: none;
        }

        .ripple.animate:before {
            -webkit-animation: clicked 0.4s forwards cubic-bezier(0.5, 0.61, 0.36, 1);
            animation: clicked 0.4s forwards cubic-bezier(0.5, 0.61, 0.36, 1);
        }

        .ripple:before {
            content: "";
            background: rgba(0, 0, 0, 0.1);
            width: 100px;
            height: 100px;
            position: absolute;
            top: 50%;
            right: 41px;
            border-radius: 50%;
            opacity: 0;
            -webkit-transform: translate(50%, -50%) scale(0.5);
            transform: translate(50%, -50%) scale(0.5);
            pointer-events: none;
        }

        @-webkit-keyframes clicked {
            0% {
                opacity: 0;
                -webkit-transform: translate(50%, -50%) scale(0.5);
                transform: translate(50%, -50%) scale(0.5);
            }
            10% {
                opacity: 0.8;
            }
            100% {
                opacity: 0;
                -webkit-transform: translate(50%, -50%) scale(1.2);
                transform: translate(50%, -50%) scale(1.2);
            }
        }

        @keyframes clicked {
            0% {
                opacity: 0;
                -webkit-transform: translate(50%, -50%) scale(0.5);
                transform: translate(50%, -50%) scale(0.5);
            }
            10% {
                opacity: 0.8;
            }
            100% {
                opacity: 0;
                -webkit-transform: translate(50%, -50%) scale(1.2);
                transform: translate(50%, -50%) scale(1.2);
            }
        }

        .toggle {
            position: absolute;
            width: 85px;
            height: 70px;
            background: transparent;
            z-index: 100;
            right: 0;
            top: 0;
            transition: -webkit-transform 0.2s;
            transition: transform 0.2s;
            transition: transform 0.2s, -webkit-transform 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0;
            overflow: hidden;
            border-radius: 100vw;
            cursor: pointer;
        }

        .toggle:before {
            content: "";
            display: block;
            position: absolute;
            left: 35px;
            top: 25px;
            height: 2px;
            background: black;
            -webkit-transform-origin: top left;
            transform-origin: top left;
            -webkit-transform: rotateZ(46deg);
            transform: rotateZ(46deg);
            transition: width 0.13s ease-out;
        }

        .toggle[data-state="visible"]:before {
            width: 25px;
        }

        .toggle[data-state="hidden"]:before {
            width: 0;
        }

        .toggle .eye {
            fill: #000000;
            transition: -webkit-transform .13s linear;
            transition: transform .13s linear;
            transition: transform .13s linear, -webkit-transform .13s linear;
            stroke-width: 0;
            -webkit-transform: scale(1) rotateY(0);
            transform: scale(1) rotateY(0);
        }

        .toggle .eye path {
            fill: none;
            stroke-width: 1.5;
            stroke-miterlimit: 5;
            stroke: #000000;
        }

        .toggle:active {
            -webkit-transform: scale(0.9);
            transform: scale(0.9);
        }

        .toggle:active + input {
            -webkit-transform: rotateY(1deg);
            transform: rotateY(1deg);
            letter-spacing: 1.5px;
            box-shadow: 3px 0px 15px 0px #c1e6d0;
            cursor: text;
        }

        .toggle:active + input.password {
            letter-spacing: 3px;
        }

        .toggle:active:before {
            -webkit-transform: rotateZ(46deg) rotateY(5deg);
            transform: rotateZ(46deg) rotateY(5deg);
        }

        .toggle:active .eye {
            -webkit-transform: scale(0.75) rotateY(5deg);
            transform: scale(0.75) rotateY(5deg);
        }

        input {
            width: 340px;
            height: 70px;
            background: #ffffff;
            border-radius: 10px;
            will-change: transform;
            border-radius: 100vw;
            transition: all 0.2s ease;
            cursor: pointer;
            color: #ffffff;
            font-size: 22px;
            color: #000000;
            outline: none;
            text-align: left;
            border: 0;
            padding: 10px 80px 10px 30px;
            -webkit-transform-origin: left center;
            transform-origin: left center;
            transition: -webkit-transform 0.13s;
            transition: transform 0.13s;
            transition: transform 0.13s, -webkit-transform 0.13s;
            font-family: "Poppins", sans-serif;
            box-shadow: 0px 0px 30px 0px #f2d3da;
            transition: letter-spacing 0.13s ease-out, box-shadow 0.13s ease-out;
        }

        input::-moz-selection {
            background: #d4fce4;
        }

        input::selection {
            background: #d4fce4;
        }

        input::-webkit-input-placeholder {
            color: #c1e6d0;
        }

        input:-ms-input-placeholder {
            color: #c1e6d0;
        }

        input::-ms-input-placeholder {
            color: #c1e6d0;
        }

        input::placeholder {
            color: #c1e6d0;
        }

        input.password {
            letter-spacing: 1px;
        }

        .box {
            background: #ffffffd4;
            height: 100vh;
            width: 100%;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        </style>
    </head>
    <body>
     <div class="box">
               <div class="container">
        <div class="ripple"></div>
        <div class="toggle" data-state="visible">
            <svg xmlns="http://www.w3.org/2000/svg" class="eye" width="32" height="32">
                <circle cx="16" cy="15" r="3"/>
                <path d="M30 16s-6.268 7-14 7-14-7-14-7 6.268-7 14-7 14 7 14 7zM22.772 10.739a8 8 0 1 1-13.66.189"/>
            </svg>
        </div>
               @if(dujiaoka_config_get('is_open_pass') == \App\Models\BaseModel::STATUS_OPEN)
       <input type="password" id="password" placeholder="请输入密码{{ dujiaoka_config_get('cnpass') }}">
     @else
       <input type="password" id="password" placeholder="请输入密码">
          @endif
                <div class="toggle" id="submit"><span></span></div>
                <div class="ripple"></div>
            </div>
            <div class="loader"></div>
            <p id="continue"></p>
            <form method="GET" id="form">
                <input type="hidden" name="_challenge" value="">
            </form>
        </div>
    </body>
    <script>
        const encoder = new TextEncoder();
        async function sha1(str) {
            const hash = await crypto.subtle.digest('SHA-1', encoder.encode(str));
            return Array.from(new Uint8Array(hash))
                .map(b => b.toString(16).padStart(2, '0'))
                .join('');
        }

        async function work(target) {
            const maxLength = 10;
  
            for (let i = 0; i < Number.MAX_SAFE_INTEGER; i++) {
                const hash = await sha1(i);
                if (hash.endsWith(target)) {
                    return i;
                }
            }
        }

       document.addEventListener('DOMContentLoaded', function() {
    // 给提交按钮添加点击事件监听
    document.querySelector('#submit').addEventListener('click', submitPassword);

    // 给密码输入框添加回车键事件监听
    document.querySelector('#password').addEventListener('keypress', function(event) {
        // 当按下回车键时，event.keyCode === 13
        if (event.keyCode === 13) {
            submitPassword();
        }
    });

    // 密码提交逻辑封装成函数以便复用
    function submitPassword() {
        const password = document.querySelector('#password').value;
       if (password === '{!! dujiaoka_config_get('cnpass') !!}'){
            document.querySelector('.loader').style.display = "block";
            work('{{$code}}').then(output => {
                document.querySelector('#continue').style.display = "block";
                document.querySelector('.loader').style.display = "none";
                document.querySelector('input[name="_challenge"]').value = output;
                document.querySelector('#form').submit();
            });
        } else {
            alert('密码错误!');
    
        }
    }

});

    </script>
</html>
