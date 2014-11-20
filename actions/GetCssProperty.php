<?php

namespace flyiing\css\actions;

use flyiing\css\widgets\StyleWidget;
use Yii;
use yii\web\Response;

class GetCssProperty extends \yii\base\Action
{

    public function run($baseName, $propName, $propValue = null)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $view = Yii::$app->getView();

        ob_start();
        ob_implicit_flush(false);
        //$view->beginPage();
        $view->head();
        $view->beginBody();
        $row = $view->render('@flyiing/css/widgets/views/property',
            compact('baseName', 'propName', 'propValue'));
        $view->endBody();
        //$view->endPage(true);
        ob_clean();

        return [
            'row' => $row,
            'cssFiles' => $view->cssFiles,
            'css' => $view->css,
            'jsFiles' => $view->jsFiles,
            'js' => $view->js,
        ];
    }

}
