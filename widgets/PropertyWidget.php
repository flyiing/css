<?php

namespace flyiing\css\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;
use flyiing\widgets\Html;
use flyiing\css\CssProps;

class PropertyWidget extends \flyiing\widgets\base\InputWidget
{
    /**
     * @var string Класс виджета по умолчанию
     */
    public $defaultWidgetClass;
    /**
     * @var string Имя css-своства
     */
    public $propName;
    /**
     * @var array Конфиг этого свойства
     */
    protected $_config;

    public function init()
    {
        if(empty($this->propName))
            throw new InvalidConfigException("'propName' is required.");
        if(($this->_config = CssProps::getProps($this->propName)) === false)
            throw new InvalidConfigException('Unknown css property.');
        if(empty($this->defaultWidgetClass))
            $this->defaultWidgetClass = \flyiing\widgets\base\InputWidget::className();
        parent::init();
        Html::addCssClass($this->options, 'css-prop-widget');
    }

    public function run()
    {
        $js = <<<JS
        function cssPropWidgetCheckUse(checkbox)
        {
            var use = $(checkbox).prop('value');
            $(checkbox).closest('.css-prop-widget').find('.css-prop-input').each(function() {
                if(use > 0) {
                    $(this).removeAttr('disabled').fadeIn();
                } else {
                    $(this).attr('disabled', 'disabled').fadeOut();
                }
            });
        }
        $('.css-prop-widget .css-prop-use input').on('change', function() {
            cssPropWidgetCheckUse(this);
        }).each(function() {
            cssPropWidgetCheckUse(this);
        });
JS;
        $this->view->registerJs($js);

        $css = <<<CSS
        .css-prop-widget .css-prop-use {
            padding: 5px 10px;
            min-height: 40px;
        }
        .css-prop-widget .css-prop-input {
            padding: 3px 5px;
        }
CSS;
        $this->view->registerCss($css);
        $this->options['id'] = Html::name2id($this->name);
        $return = Html::beginTag('div', $this->options);
        $return .= '<div class="form-group css-prop-use">';

        $return .= Html::input('hidden', $this->name. '[use]', 1);
/*
        $return .= CheckboxX::widget([
            'id' => Html::name2id($this->name. '[use]'),
            'name' => $this->name. '[use]',
            'value' => ArrayHelper::getValue($this->value, 'use', 0),
            'pluginOptions' => [
                'threeState' => false,
                'size' => 'lg',
            ],
        ]);
*/
        $return .= '</div>';

        $type = ArrayHelper::getValue($this->_config, 'type');
        if(isset($this->_config['default']))
            $defaultValue = $this->_config['default'];
        elseif(($confDefault = ArrayHelper::getValue(CssProps::getTypes($type), 'default', null)) !== null)
            $defaultValue = $confDefault;
        else
            $defaultValue = null;
        $return .= $this->renderInput($type, $this->name .'[value]',
            ArrayHelper::getValue($this->value, 'value', $defaultValue));
        $return .= Html::endTag('div');

        return $return;
    }

    public function renderInput($typeName, $name, $value, $history = [])
    {
        if(in_array($typeName, $history))
            throw new InvalidConfigException('Recursive type reference.');
        if(($type = CssProps::getTypes($typeName)) === false)
            throw new InvalidConfigException(Yii::t('css', 'Unknown css property type: {type}.', ['type' => $typeName]));

        $return = '';
        if(($params = ArrayHelper::getValue($type, 'params', false)) !== false) {
            $history[] = $type;
            foreach($params as $pName => $pConfig) {
                if(is_string($pConfig))
                    $pType = $pConfig;
                else
                    continue;
                $pValue = ArrayHelper::getValue($value, $pName);
                //$return .= Yii::t('css.prop.input', $pName) .'&nbsp;';
                $return .= $this->renderInput($pType, $name .'['. $pName .']', $pValue, $history);
            }
        } else {
            $inputOptions = ArrayHelper::getValue($type, 'input', []);
            if(($widgetClass = ArrayHelper::remove($inputOptions, 'class', $this->defaultWidgetClass)) === false)
                throw new InvalidConfigException('Can not resolve widget class.');
            $inputOptions['name'] = $name;
            $inputOptions['id'] = Html::name2id($name);
            $inputOptions['value'] = $value;
            Html::addCssClass($inputOptions, 'css-prop-input');
            $return = '<div class="form-group css-prop-input">';
            $return .= $widgetClass::widget($inputOptions);
            $return .= '</div>';
        }
        return $return;
    }

}
