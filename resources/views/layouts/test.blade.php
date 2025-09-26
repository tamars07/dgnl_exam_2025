<?php
$heading = isset($heading)?$heading:'';
$css_files = isset($css_files)?$css_files:[];
$js_files = isset($js_files)?$js_files:[];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>HCMUE - @yield('title')</title>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <?php foreach($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
        <?php endforeach; ?>

        <!-- Metro 4 -->
        <script src="<?=url('/assets/third_party/metrouicss/js/metro.min.js')?>"></script>
        <link href="<?=url('/assets/third_party/metrouicss/css/metro-all.css')?>" rel="stylesheet" />
        <style>
            label.label_question p{
                display: inline;
                list-style-type: none;
                margin-left: 0.2rem;
            }
            .cl_nav_quick{
                width: 22px;
                height: 20px;
            }
        </style>
        <script src="/assets/js/load-mathjax.js" async></script>
        <script>
            $.noConflict();
            function redirectTo(sUrl) {
                window.location = sUrl
            }
            if (typeof $ !== 'undefined') {
                $(document).ready(function () {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                });
            }
        </script>
        
    </head>
    <body>
        <div class="container-fluid">
            <div class="top">
                <div class="window">
                    
                    <div class="window-content p-2">
                        @yield('content')
                    </div>
                </div><!-- Close window class -->
            </div><!-- Close top class -->
        </div><!-- Close container-fluid class-->
        <?php foreach($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>
    </body>
</html>