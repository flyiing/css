<?php

use yii\helpers\Html;
use flyiing\css\CssProps;
use flyiing\css\widgets\PropertyWidget;

/* @var $this yii\web\View */
/* @var $baseName string */
/* @var $propName string */
/* @var $propValue */

CssProps::initI18n();
echo Html::beginTag('div', [
    'class' => 'row',
    'data-css-prop' => $propName,
]);
echo Html::beginTag('div', ['class' => 'form-group col-md-3']);
echo '<strong>'. Yii::t('css.prop.label', $propName)
    .'</strong><br><small>'. $propName .'</small>';
echo Html::endTag('div');
echo PropertyWidget::widget([
    'propName' => $propName,
    'name' => $baseName .'['. $propName .']',
    'value' => $propValue,
]);
echo Html::endTag('div');
