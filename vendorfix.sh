#!/bin/bash
sed -i "s/^        'binary'  => '\/usr\/local\/bin\/wkhtmltopdf',/        \/\/'binary'  => '\/usr\/local\/bin\/wkhtmltopdf',\n        'binary' => base_path('vendor\/h4cc\/wkhtmltopdf-amd64\/bin\/wkhtmltopdf-amd64'),/" ./vendor/barryvdh/laravel-snappy/config/snappy.php
sed -i "s/^        'binary'  => '\/usr\/local\/bin\/wkhtmltoimage',/        \/\/'binary'  => '\/usr\/local\/bin\/wkhtmltoimage',\n        'binary' => base_path('vendor\/h4cc\/wkhtmltoimage-amd64\/bin\/wkhtmltoimage-amd64'),/" ./vendor/barryvdh/laravel-snappy/config/snappy.php

sed -i ":begin;$!N;s/        \$this->document = new DOMDocument(\$xmlVersion, \$xmlEncoding);\n        \$this->replaceSpacesByUnderScoresInKeyNames = \$replaceSpacesByUnderScoresInKeyNames;/        \$this->document = new DOMDocument(\$xmlVersion, \$xmlEncoding);\n        \$this->document->formatOutput = true;\n        \$this->replaceSpacesByUnderScoresInKeyNames = \$replaceSpacesByUnderScoresInKeyNames;/;tbegin;P;D" ./vendor/spatie/array-to-xml/src/ArrayToXml.php

sed -i "s/^    protected function getBarcodePNG(\$code, \$type, \$w = 2, \$h = 30, \$color = array(0, 0, 0)) {/     protected function getBarcodePNG(\$code, \$type, \$w = 2, \$h = 30, \$color = array(0, 0, 0), \$showCode = false) {/" ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php
line191=`sed '191q;d' ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php`
line191new="            if(\$showCode)"
if [ "$line191" != "$line191new" ]
then
    sed -i "191i\ \ \ \ \ \ \ \ \ \ \ \ if(\$showCode)" ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php
fi
line192=`sed '192q;d' ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php`
line192new="                \$bh -= imagefontheight(3);"
if [ "$line192" != "$line192new" ]
then
    sed -i "192i\ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \ \$bh -= imagefontheight(3);" ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php
fi
sed -i ":begin;$!N;s/        ob_start();\n        \/\/ get image out put/        ob_start();\n        \/\/ Add Code String in bottom\n        if(\$showCode)\n            if (\$imagick) {\n                \$bar->setTextAlignment(\\Imagick::ALIGN_CENTER);\n                \$bar->annotation( 10 , \$h - \$bh +10 , \$code );\n            } else {\n                \$width_text = imagefontwidth(3) * strlen(\$code);\n                \$height_text = imagefontheight(3);\n                imagestring(\$png, 3, (\$width\/2) - (\$width_text\/2) , (\$height - \$height_text) , \$code, \$fgcol);\n            }\n        \/\/ get image out put/;tbegin;P;D" ./vendor/milon/barcode/src/Milon/Barcode/DNS1D.php
