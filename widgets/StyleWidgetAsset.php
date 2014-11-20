<?php

namespace flyiing\css\widgets;

class StyleWidgetAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@flyiing/css/assets';

    public $js = [
        'js/style-widget.js',
    ];

    public $css = [
    ];

    public $depends = [
        'yii\jui\JuiAsset',
        'flyiing\widgets\Select2TWBSExtAsset',
    ];

}
