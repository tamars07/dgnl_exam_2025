<?php
namespace App\Services;
 
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Storage;

class PhpWordService
{
    public function createFileWord()
    {
        //Khởi tạo đối tượng phpWord
        $phpWord = new PhpWord();

        //Thêm một tài liệu Word
        $section = $phpWord->addSection();

        //Thêm nội dung tài liệu cũng như các định dạng cơ bản của tài liệu
        $section->addText(
            'Nội dung',
            array(
                'name' => 'Arial',
                'size' => 14
            )
        );

        //Khởi tạo đối tượng writer
        $writer = IOFactory::createWriter($phpWord, 'Word2007');

        //Tạo tập tin Word
        $writer->save(Storage::path('export/word/test.docx'));
        //write file
        // Storage::put('testdata/plain/' . $filename,$plain);
        // Storage::put('testdata/encrypt/' . $filename,$encryption);
        // Storage::put('testdata/decrypt/' . $filename,$decryption);

        // return Storage::download('testdata/encrypt/' . $filename);
    }

    public static function createFromTemplate($data,$template_file,$filename)
    {
        $templateProcessor = new TemplateProcessor($template_file);

        $templateProcessor->cloneBlock('block', 0, true, false, $data);

        //Tạo tập tin Word
        $pathToSave = Storage::path('export/docx/' . $filename);
        $templateProcessor->saveAs($pathToSave);
    }
    public static function createFromTemplateNoBLock($data,$template_file,$filename)
    {
        $templateProcessor = new TemplateProcessor($template_file);

        foreach($data as $key => $value){
            $templateProcessor->setValue($key, $value, true);
        }
        //Tạo tập tin Word
        $pathToSave = Storage::path('export/docx/' . $filename);
        $templateProcessor->saveAs($pathToSave);
    }
}