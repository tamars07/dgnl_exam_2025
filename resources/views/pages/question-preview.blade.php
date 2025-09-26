@extends('layouts.test')

@section('title','Kiểm tra câu hỏi')

@section('content')
<div>
    <h5 class="mt-3">{{ $question->code }}</h5>
    <div>{!! ($question->pre_content) !!}</div>
    <div>{!! ($question->content) !!}</div>
    <div class="mb-3">{!! html_entity_decode($question->post_content) !!}</div><br>
        @if($question->question_type_id == 3)
            @foreach($sub_questions as $key => $value)
                <div>
                    <h6 class="mt-5">{{ $value->code }}</h6>
                    <div>{!! $value->pre_content !!}</div>
                    <div>{!! $value->content !!}</div>
                    <div class="mb-3">{!! $value->post_content !!}</div><br>
                    <strong class="mb-3 fg-red">Đáp án: {!! $value->answer_key !!}</strong>
                    <div><strong>1. </strong>{!! $value->option_1 !!}</div>
                    <div><strong>2. </strong>{!! $value->option_2 !!}</div>
                    <div><strong>3. </strong>{!! $value->option_3 !!}</div>
                    <div><strong>4. </strong>{!! $value->option_4 !!}</div>
                </div>
                <hr>
            @endforeach;
        @elseif($question->question_type_id == 1)
            <div>
                <strong class="mb-3 fg-red">Đáp án: {!! $question->answer_key !!}</strong>
                <div><strong>1. </strong>{!! $question->option_1 !!}</div>
                <div><strong>2. </strong>{!! $question->option_2 !!}</div>
                <div><strong>3. </strong>{!! $question->option_3 !!}</div>
                <div><strong>4. </strong>{!! $question->option_4 !!}</div>
            </div>
        @elseif($question->question_type_id == 2)
            <div>
                <strong class="mb-3 fg-red">Đáp án: {!! $question->answer_key !!}</strong>
                <div><strong>1. </strong>{!! $question->option_1 !!}</div>
                <div><strong>2. </strong>{!! $question->option_2 !!}</div>
                <div><strong>3. </strong>{!! $question->option_3 !!}</div>
                <div><strong>4. </strong>{!! $question->option_4 !!}</div>
                <div><strong>5. </strong>{!! $question->option_5 !!}</div>
            </div>
        @elseif($question->question_type_id == 4)
            <div>
                <strong class="mb-3">Loại câu hỏi: {!! $question->answer_type !!}</strong><br>
                <strong class="mb-3 fg-red">Đáp án: {!! $question->answer_key !!}</strong>
            </div>
        @endif
</div>
@endsection