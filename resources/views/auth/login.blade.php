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
        function redirectTo(sUrl) {
            window.location = sUrl
        }
    </script>
</head>
<body class="h-vh-100 bg-brandColor2">
    <form class="login-form bg-white p-6 mx-auto border bd-default win-shadow"
        data-role="validator"
        {{-- action="javascript:" --}}
        data-clear-invalid="2000"
        data-on-error-form="invalidForm"
        data-on-validate-form="validateForm"
    >
        <span class="mif-vpn-lock mif-4x place-right" style="margin-top: -10px;"></span>
        <h2 class="text-light">ĐĂNG NHẬP HỆ THỐNG</h2>
        <hr class="thin mt-4 mb-4 bg-white">
        <div class="form-group">
            <input type="text" data-role="input" data-prepend="<span class='mif-user'>" placeholder="Nhập tài khoản" data-validate="required" id="email">
        </div>
        <div class="form-group">
            <input type="password" data-role="input" data-prepend="<span class='mif-key'>" placeholder="Nhập mật khẩu" data-validate="required minlength=6" id="password">
        </div>
        <div class="form-group mt-10">
            {{-- <input type="checkbox" data-role="checkbox" data-caption="Remember me" class="place-right"> --}}
            <button class="button primary" type="submit">Đăng nhập</button>
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

        $(document).ready(function () {
            $("form").submit(function (event) {
                var formData = {
                    password: $("#password").val(),
                    email: $("#email").val(),
                };

                $.ajax({
                    type: "POST",
                    url: "<?= url('/api/auth/login') ?>",
                    data: formData,
                    dataType: "json",
                    encode: true,
                }).done(function (data) {
                    // console.log(data);
                    const jwt_token = data.data.serviceToken;
                    const user = data.data.user
                    localStorage.setItem('user',user);
                    localStorage.setItem('serviceToken',data.data.serviceToken);
                    localStorage.setItem('roles',user.roles);
                    for(let role of user.roles){
                        switch(role){
                        case 'ADMIN': case 'EDITOR':
                            redirectTo('<?= url('/qbank') ?>');
                            // window.location.href = '<?= url('/qbank') ?>';
                            break;
                        case 'MODERATOR': case 'CHAIRMAN':
                            redirectTo('<?= url('/exam') ?>')
                            // window.location.href = '<?= url('/exam') ?>';
                            break;
                        }
                    }
                    
                });

                event.preventDefault();
            });
        });
    </script>

</body>
</html>