@extends('layouts.council')

@section('title',$title)

@section('content')
@if(isset($data['form_url']) && $data['form_url'] != '')
<div class="row">
    <div class="cell-sm-12 cell-md-half">
        <form action="{{ $data['form_url'] }}" method="post" enctype="multipart/form-data" class="mx-3">
            @csrf
            @if(isset($data['code']) && $data['code'] != '')
            <input type="hidden" placeholder="Your Name" class="metro-input" name="code" value="{{ $data['code'] }}">
            @endif
            {{-- import test data --}}
            @if($data['data'] == 'test_data')
            <div class="form-group">
                <label>Dữ liệu đề thi</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="testdata">
                <small class="text-muted">Chọn file chứa đề thi</small>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="text" placeholder="Mật khẩu mở đề" class="metro-input" name="testpassword">
            </div>
            <div class="form-group">
                <button class="button success">Nhận đề thi</button>
            </div>
            @endif
            {{-- import examinee data --}}
            @if($data['data'] == 'examinee_data')
            <div class="form-group">
                <label>Hội đồng thi</label>
                <select class="metro-input" name="council_code">
                    <option value="0">Chọn HĐ thi</option>
                    @foreach($data['councils'] as $item)
                    <option value="{{ $item->code }}">{{ $item->code }}</option>
                    @endforeach
                </select>
                {{-- <small class="text-muted">Chọn HĐ thi</small> --}}
            </div>
            <div class="form-group">
                <label>Ca thi</label>
                <select class="metro-input" name="council_turn_code">
                    <option value="0">Chọn ca thi</option>
                    @foreach($data['council_turns'] as $item)
                    <option value="{{ $item->code }}">{{ $item->name . '_' . $item->council_code }}</option>
                    @endforeach
                </select>
                {{-- <small class="text-muted">Chọn ca thi</small> --}}
            </div>
            <div class="form-group">
                <label>Phòng thi</label>
                <select class="metro-input" name="room_code">
                    <option value="0">Chọn phòng thi</option>
                    @foreach($data['rooms'] as $item)
                    <option value="{{ $item->code }}">{{ $item->code }}</option>
                    @endforeach
                </select>
                {{-- <small class="text-muted">Chọn phòng thi</small> --}}
            </div>
            <div class="form-group">
                <label>Dữ liệu thí sinh</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="file">
                <small class="text-muted">Chọn file excel chứa dữ liệu thí sinh</small>
            </div>
            <div class="form-group">
                <button class="button success">Nhập dữ liệu</button>
            </div>
            @endif
            {{-- import room data --}}
            @if($data['data'] == 'room_data')
            <div class="form-group">
                <label>Địa điểm thi</label>
                <select class="metro-input" name="organization_code">
                    <option value="0">Chọn địa điểm thi</option>
                    @foreach($data['organizations'] as $item)
                    <option value="{{ $item->code }}">{{ $item->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Chọn HĐ thi</small>
            </div>
            <div class="form-group">
                <label>Dữ liệu phòng thi</label>
                <input type="file" data-role="file" data-button-title="Chọn file" name="file">
                <small class="text-muted">Chọn file excel chứa dữ liệu</small>
            </div>
            <div class="form-group">
                <button class="button success">Nhập dữ liệu</button>
            </div>
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

            <div class="form-group">
                @if (session('message'))
                    <label>{{ session('message') }}</label>
                @endif
            </div>
        </form>
    </div>
</div>
@endif
<?=$output?>
@endsection