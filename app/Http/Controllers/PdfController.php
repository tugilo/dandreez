<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function generatePdf()
    {
        // SVGファイルを読み込む
        $svgContent = File::get(public_path('svg/test.svg'));

        // プレースホルダーを置換するデータを準備
        $data = [
            'customer' => 'tugi',
            'datetime' => '20290509',
        ];

        // SVG内の特定の要素を検索し、対応する値で置換
        foreach ($data as $key => $value) {
            $svgContent = str_replace("%{$key}%", $value, $svgContent);
        }

        // ビューにSVGコンテンツを渡す
        $html = view('pdf.layout', compact('svgContent'))->render();

        // PDFを生成
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        // PDFをレスポンスとして返す
        return $dompdf->stream('document.pdf');
    }
}