<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <!-- Metro 4 -->
    <link href="<?=url('/assets/third_party/metrouicss/css/metro-all.css')?>" rel="stylesheet" />

    <title>HCMUE - H-SCA SYSTEM</title>

    <style>
        .login-form {
            width: 450px;
            height: auto;
            top: 50%;
            margin-top: -160px;
        }
    </style>
    
    <script>
        // $.noConflict();
        // function redirectTo(sUrl) {
        //     window.location = sUrl
        // }
    </script>
</head>
<body class="h-vh-100 bg-brandColor2">
    <form class="login-form bg-white p-6 mx-auto border bd-default win-shadow"
        method="POST"
        action="{{ url('/post-login') }}"
        data-role="validator"
        {{-- action="javascript:" --}}
        data-clear-invalid="2000"
        data-on-error-form="invalidForm"
        {{-- data-on-validate-form="validateForm" --}}
    >
        {!! csrf_field() !!}
        <span class="mif-vpn-lock mif-4x place-right" style="margin-top: -10px;"></span>
        <h2 class="text-light">ĐĂNG NHẬP HỆ THỐNG</h2>
        <hr class="thin mt-4 mb-4 bg-white">
        <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
            <input type="text" data-role="input" data-prepend="<span class='mif-user'>" placeholder="Nhập tài khoản" data-validate="required" id="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
            <input type="password" data-role="input" data-prepend="<span class='mif-key'>" placeholder="Nhập mật khẩu" data-validate="required minlength=6" id="password" name="password" value="{{ old('password') }}">
        </div>
        @if (Session('error'))
            <p class="remark alert fg-red">{{ session('error') }}</p>
        @endif
        <div class="form-group mt-10">
            {{-- <input type="checkbox" data-role="checkbox" data-caption="Remember me" class="place-right"> --}}
            <button class="button primary" type="submit">
                {{-- <i class="mif-sign-in"></i> --}}
                Đăng nhập
            </button>
        </div>
    </form>

    <!-- Metro 4 -->
    <script src="<?=url('/assets/third_party/metrouicss/js/metro.min.js')?>"></script>
    <script src="<?=url('/assets/js/jquery-3.5.1.min.js')?>"></script>
    <script>
        function invalidForm(){
            var form  = $(this);
            form.addClass("ani-ring");
            setTimeout(function(){
                form.removeClass("ani-ring");
            }, 1000);
        }

        function validateForm(){
            $(".login-form").animate({
                opacity: 0
            });
        }
    </script>

</body>
</html>