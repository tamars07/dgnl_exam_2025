@extends('layouts.qbank')

@section('title',$title)

@section('content')
@if(isset($heading) && $heading != '')
<div class="cl_create_sub_question"><a href="{{url('qbank/question/TNC/')}}" target="_blank"><button class="btn btn-info">Quay lại</button></a></div>
<h3>Nội dung ngữ cảnh</h3>
<?php
$heading = isset($heading)?$heading:'';
?>
<div class="cl_heading"><?=$heading?></div>
@endif
@if(isset($back_url) && $back_url != '')
<div class="row"><a href="{{url($back_url)}}" class="float-right"><button class="btn btn-success">Quay lại</button></a></div>
@endif
<?=$output?>
@endsection