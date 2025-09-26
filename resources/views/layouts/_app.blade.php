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
                    <div class="window-caption">
                        <span class="icon mif-windows"></span>
                        <span class="title">Quản lý ngân hàng câu hỏi</span>
                        <div class="buttons">
                            <a href="<?=url('/logout')?>"><span class="btn-close"></span></a>
                        </div>
                    </div>
                    <nav data-role="ribbonmenu" class="ribbon-menu">
                        <ul class="tabs-holder">
                            <li class="{{ $cat=='test'?'active':'' }}"><a href="#section-tests">Đề thi</a></li>
                            <li class="{{ $cat=='qbank'?'active':'' }}"><a href="#section-questions">Câu hỏi</a></li>
                            <li class="{{ $cat=='cate'?'active':'' }}"><a href="#section-categories">Danh mục</a></li>
                            <li class="{{ $cat=='config'?'active':'' }}"><a href="#section-config">Cấu hình</a></li>
                        </ul>
                        <div class="content-holder">
                            <!--Đề thi-->
                            <div class="section" id="section-tests">
                                <div class="group">
                                    <div data-role="button-group">
                                    <button class="ribbon-button {{ $sub_cat=='tgroup'?'active':'' }}" onClick="redirectTo('<?=url('/test/tgroup')?>')">
                                        <span class="icon">
                                            <span class="mif-folder-shared"></span>
                                        </span>
                                        <span class="caption">Đề thi</span>
                                    </button>
                                    </div>

                                    <span class="title">Bộ đề thi</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" onClick="redirectTo('<?=url('/test/form')?>')">
                                    <button class="ribbon-button {{ $sub_cat=='tformat'?'active':'' }}">
                                        <span class="icon">
                                            <span class="mif-folder-shared"></span>
                                        </span>
                                        <span class="caption">Định dạng đề thi</span>
                                    </button>
                                    </div>
                                    <div data-role="button-group" onClick="redirectTo('<?=url('/test/part')?>')">
                                    <button class="ribbon-button {{ $sub_cat=='tpart'?'active':'' }}">
                                        <span class="icon">
                                            <span class="mif-folder-shared"></span>
                                        </span>
                                        <span class="caption">Các phần thi</span>
                                    </button>
                                    </div>
                                    <span class="title">Cấu trúc Đề thi</span>
                                </div>
                            </div>
                            <!--Câu hỏi-->
                            <div class="section" id="section-questions">
                                <div class="group">
                                <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='TN1'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TN1')?>')">
                                        <span class="icon">
                                            <span class="mif-document-file-qt"></span>
                                        </span>
                                        <span class="caption">Danh sách</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='TN1add'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TN1#/add')?>')">
                                        <span class="icon">
                                            <span class="mif-plus"></span>
                                        </span>
                                        <span class="caption">Tạo mới</span>
                                    </button>
                                </div>
                                <span class="title">TN 1 Phương án đúng</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='TNN'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TNN')?>')">
                                        <span class="icon">
                                            <span class="mif-document-file-qt"></span>
                                        </span>
                                        <span class="caption">Danh sách</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='TNNadd'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TNN#/add')?>')">
                                        <span class="icon">
                                            <span class="mif-plus"></span>
                                        </span>
                                        <span class="caption">Tạo mới</span>
                                    </button>
                                    </div>
                                    <span class="title">Nhiều Phương án đúng</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='TLN'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TLN')?>')">
                                        <span class="icon">
                                            <span class="mif-document-file-qt"></span>
                                        </span>
                                        <span class="caption">Danh sách</span>
                                    </button>

                                    <button class="ribbon-button {{ $sub_cat=='TLNadd'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TLN#/add')?>')">
                                        <span class="icon">
                                            <span class="mif-plus"></span>
                                        </span>
                                        <span class="caption">Tạo mới</span>
                                    </button>
                                    </div>
                                    <span class="title">Trả lời ngắn 1 PA</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='LNN'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/LNN')?>')">
                                        <span class="icon">
                                            <span class="mif-news"></span>
                                        </span>
                                        <span class="caption">Danh sách</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='LNNadd'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/LNN#/add')?>')">
                                        <span class="icon">
                                            <span class="mif-plus"></span>
                                        </span>
                                        <span class="caption">Tạo mới</span>
                                    </button>
                                    </div>
                                    <span class="title">Tự luận</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='TNC'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TNC')?>')">
                                        <span class="icon">
                                            <span class="mif-stackoverflow"></span>
                                        </span>
                                        <span class="caption">Danh sách</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='TNCadd'?'active':'' }}" onClick="redirectTo('<?=url('/qbank/question/TNC#/add')?>')">
                                        <span class="icon">
                                            <span class="mif-plus"></span>
                                        </span>
                                        <span class="caption">Tạo mới</span>
                                    </button>
                                    </div>
                                    <span class="title">Câu hỏi ngữ cảnh</span>
                                </div>
                            </div>
                            <!--Danh mục cố định-->
                            <div class="section" id="section-categories">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='diff'?'active':'' }}" onClick="redirectTo('<?=url('/management/difficult')?>')">
                                        <span class="icon">
                                            <span class="mif-vertical-align-top"></span>
                                        </span>
                                        <span class="caption">Độ khó</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='qtype'?'active':'' }}" onClick="redirectTo('<?=url('/management/qtype')?>')">
                                        <span class="icon">
                                            <span class="mif-flow-tree"></span>
                                        </span>
                                        <span class="caption">Kiểu câu hỏi</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='qmark'?'active':'' }}" onClick="redirectTo('<?=url('/management/question-mark')?>')">
                                        <span class="icon">
                                            <span class="mif-flow-tree"></span>
                                        </span>
                                        <span class="caption">Điểm</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='qstore'?'active':'' }}" onClick="redirectTo('<?=url('/management/question-store')?>')">
                                        <span class="icon">
                                            <span class="mif-flow-tree"></span>
                                        </span>
                                        <span class="caption">Kho lưu trữ (trạng thái)</span>
                                    </button>
                                    </div>
                                    <span class="title">Câu hỏi</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='grade'?'active':'' }}" onClick="redirectTo('<?=url('/management/grade')?>')">
                                        <span class="icon">
                                            <span class="mif-stack"></span>
                                        </span>
                                        <span class="caption">Khối</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='subject'?'active':'' }}" onClick="redirectTo('<?=url('/management/subject')?>')">
                                        <span class="icon">
                                            <span class="mif-stack3"></span>
                                        </span>
                                        <span class="caption">Môn</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='taxonomy'?'active':'' }}" onClick="redirectTo('<?=url('/management/taxonomy')?>')">
                                        <span class="icon">
                                            <span class="mif-layers"></span>
                                        </span>
                                        <span class="caption">Nội dung kiến thức</span>
                                    </button>
                                    <button class="ribbon-button {{ $sub_cat=='topic'?'active':'' }}" onClick="redirectTo('<?=url('/management/topic')?>')">
                                        <span class="icon">
                                            <span class="mif-section"></span>
                                        </span>
                                        <span class="caption">Đơn vị kiến thức</span>
                                    </button>
                                    </div>

                                    <span class="title">Phạm vi kiến thức</span>
                                </div>
                            </div>
                            <div class="section" id="section-config">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                    <button class="ribbon-button {{ $sub_cat=='criteria'?'active':'' }}" onClick="redirectTo('<?=url('/marking/criteria')?>')">
                                        <span class="icon">
                                            <span class="mif-list-numbered"></span>
                                        </span>
                                        <span class="caption">Tiêu chí chấm nghị luận</span>
                                    </button>
                                    </div>
                                    <span class="title">Chấm điểm</span>
                                </div>
                            </div>
                        </div>
                    </nav>
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