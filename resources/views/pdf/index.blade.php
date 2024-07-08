<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PDF Layout</title>
    <style>
        /* SVGに対するCSSスタイルを定義 */
        svg {
            width: 100%;
            height: auto;
        }
        /* 必要に応じて他のCSSスタイルを追加 */
        #Rectangle_1 {
            fill: white;
            stroke: black;
        }
        #Customer, #datetime {
            font-family: "Inter", sans-serif;
            font-size: 32px;
        }
    </style>
</head>
<body>
    {!! $svgContent !!}
</body>
</html>