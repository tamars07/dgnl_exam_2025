<?php
$heading = isset($heading)?$heading:'';
$css_files = isset($css_files)?$css_files:[];
$js_files = isset($js_files)?$js_files:[];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>@yield('title')</title>
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
                        <span class="title">Phân hệ tổ chức thi</span>
                        <span class="mr-3">v.2025.05.15</span>
                        {{-- <div class="buttons">
                            <a href="<?=url('/logout')?>"><span class="btn-close"></span></a>
                        </div> --}}
                    </div>
                    <nav data-role="ribbonmenu" class="ribbon-menu">
                        <ul class="tabs-holder">
                            <li class="{{ $cat=='management'?'active':'' }}"><a href="#section-exam">Tổ chức thi</a></li>
                            <li class="{{ $cat=='evaluation'?'active':'' }}"><a href="#section-evaluation">Chấm thi</a></li>
                            <li class="{{ $cat=='config'?'active':'' }}"><a href="#section-config">Hệ thống</a></li>
                        </ul>
                        <div class="content-holder">
                            <!--Tổ chức thi-->
                            <div class="section" id="section-exam">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='council'?'active':'' }}" onClick="redirectTo('<?=url('/exam/council')?>')">
                                            <span class="icon">
                                                <span class="mif-document-file-qt"></span>
                                            </span>
                                            <span class="caption">Danh sách</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='councilAdd'?'active':'' }}" onClick="redirectTo('<?=url('/exam/council#/add')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Tạo mới</span>
                                        </button>
                                    </div>
                                    <span class="title">Hội đồng thi</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='examinee'?'active':'' }}" onClick="redirectTo('<?=url('/exam/examinee')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Danh sách</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='examineeAdd'?'active':'' }}" onClick="redirectTo('<?=url('/exam/examinee#/add')?>')">
                                            <span class="icon">
                                                <span class="mif-user-plus"></span>
                                            </span>
                                            <span class="caption">Tạo mới</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='examineeImport'?'active':'' }}" onClick="redirectTo('<?=url('/exam/examinee-import')?>')">
                                            <span class="icon">
                                                <span class="mif-folder-upload"></span>
                                            </span>
                                            <span class="caption">Nhập file</span>
                                        </button>
                                    </div>
                                    <span class="title">Thí sinh</span>
                                </div>
                                <div class="group"></div>
                            </div>
                            <!--Chấm thi-->
                            <div class="section" id="section-evaluation">
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='sync'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/council-exam-data')?>')">
                                            <span class="icon">
                                                <span class="mif-database"></span>
                                            </span>
                                            <span class="caption">Đồng bộ dữ liệu</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='answer-key'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/manage-answer-key')?>')">
                                            <span class="icon">
                                                <span class="mif-key"></span>
                                            </span>
                                            <span class="caption">Nhập đáp án</span>
                                        </button>
                                    </div>
                                    <span class="title">Thiết lập</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='summary-sync-data'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/summary-sync-data')?>')">
                                            <span class="icon">
                                                <span class="mif-dashboard"></span>
                                            </span>
                                            <span class="caption">Thống kê dữ liệu</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='rubric'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/rubric')?>')">
                                            <span class="icon">
                                                <span class="mif-equalizer"></span>
                                            </span>
                                            <span class="caption">QL Rubrics</span>
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
                                        <button class="ribbon-button {{ $sub_cat=='assign-reviewer'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/assign-reviewer')?>')">
                                            <span class="icon">
                                                <span class="mif-history"></span>
                                            </span>
                                            <span class="caption">Phân công chấm PK</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='summary-examiner-pair'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/summary-examiner-pair')?>')">
                                            <span class="icon">
                                                <span class="mif-chart-pie"></span>
                                            </span>
                                            <span class="caption">Thống kê cặp chấm</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='examinee-answer'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/examinee-answers')?>')">
                                            <span class="icon">
                                                <span class="mif-open-book"></span>
                                            </span>
                                            <span class="caption">Bài làm Ngữ văn</span>
                                        </button>
                                    </div>
                                    <span class="title">Chấm thi tự luận</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='council-auto-marking'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/council-auto-marking')?>')">
                                            <span class="icon">
                                                <span class="mif-done_all"></span>
                                            </span>
                                            <span class="caption">Chấm theo HĐ thi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='examinee-auto-marking'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/examinee-auto-marking')?>')">
                                            <span class="icon">
                                                <span class="mif-user-check"></span>
                                            </span>
                                            <span class="caption">Chấm theo thí sinh</span>
                                        </button>
                                        {{-- <button class="ribbon-button {{ $sub_cat=='assign-examiner'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/assign-examiner')?>')">
                                            <span class="icon">
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Phân công chấm chi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='summary-examiner-pair'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/summary-examiner-pair')?>')">
                                            <span class="icon">
                                                <span class="mif-plus"></span>
                                            </span>
                                            <span class="caption">Thống kê cặp chấm</span>
                                        </button> --}}
                                    </div>
                                    <span class="title">Chấm thi tự động</span>
                                </div>
                                <div class="group">
                                    <div data-role="button-group" data-cls-active="active">
                                        <button class="ribbon-button {{ $sub_cat=='result-list'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/result-list')?>')">
                                            <span class="icon">
                                                <span class="mif-chart-bars"></span>
                                            </span>
                                            <span class="caption">Kết quả thi</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='result-list-with-rubric'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/result-list-with-rubric')?>')">
                                            <span class="icon">
                                                <span class="mif-chart-bars2"></span>
                                            </span>
                                            <span class="caption">Kết quả thi Ngữ văn</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='manage-exam-data'?'active':'' }}" onClick="redirectTo('<?=url('/evaluation/manage-exam-data')?>')">
                                            <span class="icon">
                                                <span class="mif-books"></span>
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
                                                <span class="mif-users"></span>
                                            </span>
                                            <span class="caption">Danh sách</span>
                                        </button>
                                        <button class="ribbon-button {{ $sub_cat=='monitorAdd'?'active':'' }}" onClick="redirectTo('<?=url('/exam/monitor#/add')?>')">
                                            <span class="icon">
                                                <span class="mif-user-plus"></span>
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