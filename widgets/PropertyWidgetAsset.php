<?php

namespace flyiing\css\widgets;

class PropertyWidgetAsset extends \yii\web\AssetBundle
{

    public $sourcePath = '@flyiing/css/assets';

    public $js = [
    ];

    public $css = [
        'css/property-widget.css',
    ];

    public $depends = [
        'yii\jui\JuiAsset',
    ];

}
