<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PDF Layout</title>
    <style>
        rect {
          fill: white;
          stroke: black;
        }
        line {
          stroke: black;
        }
        text {
            font-family: "Inter", sans-serif;
            font-size: 16px;
            fill: black;
        }
        svg {
            width: 100%;
            height: auto;
        }

      </style>
</head>
<body>
    {!! $svgContent !!}
</body>
</html>