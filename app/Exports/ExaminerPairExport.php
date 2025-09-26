<?php

namespace App\Exports;

use App\Models\AnswerKey;
use App\Models\Examinee;
use App\Models\ExamineeTestMix;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;

use App\Models\ExaminerPair;
use App\Models\ExaminerPairDetail;
use App\Models\ExaminerAssignment;
use App\Models\ExaminerRubricDetail;
use App\Models\RubricCriteria;
use App\Models\Monitor;
use App\Models\Rubric;

class ExaminerPairExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    
    public function __construct()
	{
	}
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function array(): array
    {
        //
        $data = [];

        $examiner_pairs = ExaminerPairDetail::where('no_assigned_test','>',0)->orderBy('examiner_id')->get();

        foreach($examiner_pairs as $num => $item){
            $pair = ExaminerPair::find($item->examiner_pair_id);
            $examiner = Monitor::find($item->examiner_id);
            $no_undone = (intval($item->no_assigned_test) - intval($item->no_done_test));
            $data[] = [
                'num' => ($num + 1),
                'council' => $pair->council_code,
                'turn' => $pair->council_turn_code,
                'pair_code' => $pair->code,
                'name' => $examiner->name,
                'account' => $examiner->code,
                'role' => 'CBChT ' . $item->examiner_role,
                'no_tests' => $item->no_assigned_test?$item->no_assigned_test:'0',
                'no_done' => $item->no_done_test?$item->no_done_test:'0',
                'no_undone' => $no_undone?$no_undone:'0',
                'start' => $item->start_at,
                'finish' => $item->finish_at,
            ];
        }

        return $data;
    }
    
    /**
     * Write code on Method
     *
     * @return response()
     */
    public function headings(): array
    {
        $header = [
            'STT',
            'HĐ thi',
            'Ca thi',
            'Mã cặp chấm',
            'Họ tên',
            'Tài khoản',
            'Vai trò',
            'Số bài',
            'Đã chấm',
            'Chưa chấm',
            'Ngày BĐ',
            'Ngày KT',
        ];
        return $header;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }
}
