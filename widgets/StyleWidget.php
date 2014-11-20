<?php

namespace flyiing\css\widgets;

use Yii;
use yii\bootstrap\Button;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use flyiing\css\CssProps;
use yii\helpers\Url;
use yii\helpers\VarDumper;

class StyleWidget extends \flyiing\widgets\base\InputWidget
{

    public $toolbarOptions = [];

    public $forcedProps = [];

    public $part = false;

    public $url = ['get-css-property'];

    protected $_props;

    public function init()
    {
        CssProps::initI18n();
        parent::init();
        Html::addCssClass($this->options, 'css-styles form-inline panel panel-default');
        Html::addCssClass($this->toolbarOptions, 'css-style-toolbar panel-heading');
        StyleWidgetAsset::register($this->view);
        $this->initProps();
    }

    public function initProps()
    {
/*
        $this->_props = CssProps::getProps();
        foreach($this->_props as $k => $v) {
            $this->_props[$k] = [
                'label' => Yii::t('css.prop.label', $k),
            ];
        }
*/
        $this->_props = [];
        foreach (CssProps::getProps() as $k => $v) {
            $this->_props[] = [
                'id' => $k,
                'label' => Yii::t('css.prop.label', $k),
            ];
        }
    }

    public function run()
    {
        $props = $this->value;
        $baseName = $this->name;
        $options = $this->options;
        if ($this->part !== false) {
            $parts = is_array($this->part) ? $this->part : [ $this->part ];
            foreach ($parts as $part) {
                $props = ArrayHelper::getValue($props, $part, []);
                $baseName .= '[' . $part . ']';
                $options['id'] .= '-' . $part;
            }
        }
        $options['name'] = $baseName;

        foreach ($this->forcedProps as $prop) {
            if (!isset($props[$prop]))
                $props[$prop] = ['use' => 0];
        }

        $tag = ArrayHelper::remove($options, 'tag', 'div');
        echo Html::beginTag($tag, $options) . PHP_EOL;
        echo Html::beginTag('div', $this->toolbarOptions);
        echo $this->renderToolbar($baseName);
        echo Html::endTag('div');
        echo Html::beginTag('div', ['class' => 'css-style-props panel-body']) . PHP_EOL;
        foreach (CssProps::sort($props) as $propName => $propValue) {
            if (CssProps::getProps($propName) === false)
                continue;
            echo $this->render('@flyiing/css/widgets/views/property',
                compact('baseName', 'propName', 'propValue'));
        }
        echo Html::endTag('div');
        echo Html::endTag($tag) . PHP_EOL;

        $pluginOptions = [
            'url' => Url::toRoute($this->url),
            'propsAvail' => $this->_props,
            'btnAddLabel' => '<span class="glyphicon glyphicon-plus"></span>',
            'btnDelLabel' => '<span class="glyphicon glyphicon-minus"></span>',
            'btnAddClass' => 'btn-success',
            'btnDelClass' => 'btn-danger',
            'delRowConfirm' => Yii::t('css', 'Are you sure to delete this property?'),
        ];
        $pluginOptions = Json::encode($pluginOptions);
        $js = <<<JS
        $("#{$options['id']}").styleWidget($pluginOptions);
JS;
        $this->view->registerJs($js);

    }

    public function renderToolbar($baseName)
    {
        $return = Html::beginTag('div', ['class' => 'input-group select2-bootstrap-prepend']);
        $return .= Html::beginTag('div', ['class' => 'input-group-btn']);
        $return .= Button::widget([
            'label' => '<span class="glyphicon glyphicon-plus"></span>',
            'encodeLabel' => false,
            'options' => [
                'type' => 'button',
                'disabled' => 'disabled',
            ],
        ]);
        $return .= Html::endTag('div');
        $return .= Html::input('hidden', $baseName .'[select]', null, [
            'class' => 'css-prop-select',
        ]);
        $return .= Html::endTag('div');
        return $return;
    }

}
