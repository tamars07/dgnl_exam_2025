@extends('layouts.council')

@section('title',$title)

@section('content')
<div class="row">
    <div class="cell-sm-12 cell-md-half">
        @if(isset($data['form_url']) && $data['form_url'] != '')
        <form action="{{ $data['form_url'] }}" method="post" enctype="multipart/form-data" class="mx-3">
            @csrf
            @if(isset($data['code']) && $data['code'] != '')
            <input type="hidden" placeholder="Your Name" class="metro-input" name="code" value="{{ $data['code'] }}">
            @endif
            {{-- import council data --}}
            @if($data['data'] == 'council_data')
            <div class="form-group">
                <label>Dữ liệu HĐ thi</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="file">
                <small class="text-muted">Chọn file excel chứa dữ liệu</small>
            </div>
            <div class="form-group">
                <button class="button success">Nhập dữ liệu</button>
            </div>
            @endif
            {{-- sync data --}}
            @if($data['data'] == 'sync-exam-data')
            <div class="form-group">
                <label>Ca thi</label>
                <select class="metro-input" name="council_turn_code">
                    <option value="0">Chọn ca thi</option>
                    @foreach($data['council_turns'] as $item)
                    <option value="{{ $item->code }}">{{ $item->name . '_' . $item->council_code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Dữ liệu bài thi</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="examdata">
                <small class="text-muted">Chọn file *.bak chứa bài thi</small>
            </div>
            <div class="form-group">
                <label>Dữ liệu đề thi</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="testdata">
                <small class="text-muted">Chọn file *.dat chứa đề thi (kèm đáp án)</small>
            </div>
            <div class="form-group">
                <label>Mật khẩu đề thi</label>
                <input type="text" placeholder="Mật khẩu mở đề" class="metro-input" name="testpassword">
            </div>
            <div class="form-group">
                <button class="button success">Đồng bộ</button>
            </div>
            @endif
            {{-- import answer key --}}
            @if($data['data'] == 'answer-key')
            <div class="form-group">
                <label>Dữ liệu đáp án</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="testdata">
                <small class="text-muted">Chọn file *.dat chứa đề thi (kèm đáp án)</small>
            </div>
            <div class="form-group">
                <label>Mật khẩu đề thi</label>
                <input type="text" placeholder="Mật khẩu mở đề" class="metro-input" name="testpassword">
            </div>
            <div class="form-group">
                <button class="button success">Nhập dữ liệu</button>
            </div>
            @endif

            <div class="form-group">
                @if (session('message'))
                    <label>{{ session('message') }}</label>
                @endif
            </div>
        </form>
        @endif
        @if(isset($data['data']) && $data['data'] == 'summary-examiner-pair')
        <div class="form-group">
            <a class="button success" href="/export/xlsx/summary-examiner-pair">Xuất DS phân công</a>
        </div>
        @endif
        @if(isset($data['data']) && $data['data'] == 'summary-assign-examiner')
        <div class="form-group">
            <a class="button success" href="/export/xlsx/all-examiner-pair">Xuất chi tiết chấm thi</a>
        </div>
        @endif
    </div>
</div>
<?=$output?>
@endsection