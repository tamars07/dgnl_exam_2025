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
                        <span class="title">Phân hệ chấm thi</span>
                        <div class="buttons">
                            <a href="<?=url('/logout')?>"><span class="btn-close"></span></a>
                        </div>
                    </div>
                    <nav data-role="ribbonmenu" class="ribbon-menu">
                        <ul class="tabs-holder">
                            {{-- <li class="{{ $cat=='management'?'active':'' }}"><a href="#section-exam">Tổ chức thi</a></li> --}}
                            <li class="{{ $cat=='evaluation'?'active':'' }}"><a href="#section-evaluation">Chấm thi</a></li>
                            <li class="{{ $cat=='config'?'active':'' }}"><a href="#section-config">Hệ thống</a></li>
                        </ul>
                        <div class="content-holder">
                            <!--Tổ chức thi-->
                            <!--Chấm thi-->
                            <div class="section" id="section-evaluation">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='sync'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/council-exam-data')?>')">
                                            <span class="icon">
                                                <span class="mif-document-file-qt"></span>
                                            </span>
                                            <span class="caption">Đồng bộ dữ liệu</span>
                                        </button>
                                    </div>
                                    <span class="title">Thiết lập</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='examinee-answer'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/examinee-answers')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Bài làm</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='answer-key'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/answer-key')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Nhập đáp án</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='rubric'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/rubric')?>')">
                                            <span class="icon">
                                                <span class="mif-equalizer"></span>
                                            </span>
                                            <span class="caption">Rubrics</span>
                                        </button>
                                    </div>
                                    <span class="title">Dữ liệu chấm thi</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='pair-examiner'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/assign-examiner#/add')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Tạo cặp chấm</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='assign-examiner'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/assign-examiner')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Phân công chấm chi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='summary-assign-examiner'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/summary-assign-examiner')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Thống kê chấm thi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='summary-examiner-pair'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/summary-examiner-pair')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Thống kê cặp chấm</span>
                                        </button>
                                    </div>
                                    <span class="title">Tổ chức chấm thi</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='exam-result'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/result')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Kết quả thi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='detail-result'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/detail-result')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Điểm giám khảo</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='exam-data'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/exam-data')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Dữ liệu bài thi</span>
                                        </button>
                                    </div>
                                    <span class="title">Thống kê</span>
                                </div>
                                <div class="group"></div>
                            </div>
                            <!--Dữ liệu chung-->
                            <div class="section" id="section-config">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='org'?'active':'' }}" onClick="redirectTo('<?=url('/exam/organization')?>')">
                                            <span class="icon">
                                                <span class="mif-vertical-align-top"></span>
                                            </span>
                                            <span class="caption">Địa điểm thi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='room'?'active':'' }}" onClick="redirectTo('<?=url('/exam/room')?>')">
                                            <span class="icon">
                                                <span class="mif-flow-tree"></span>
                                            </span>
                                            <span class="caption">Phòng thi</span>
                                        </button>
                                    </div>
                                    <span class="title">CSVC</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='monitor'?'active':'' }}" onClick="redirectTo('<?=url('/exam/monitor')?>')">
                                            <span class="icon">
                                                <span class="mif-document-file-qt"></span>
                                            </span>
                                            <span class="caption">Danh sách</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='monitorAdd'?'active':'' }}" onClick="redirectTo('<?=url('/exam/monitor#/add')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Tạo mới</span>
                                        </button>
                                    </div>
                                    <span class="title">Tài khoản</span>
                                </div>
                                <div class="group">
                                    
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