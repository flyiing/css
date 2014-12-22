<?php

/**
 * @return array Описания типов значений css-свойств, как их вводить и отображать.
 */

return [

    'size-px' => [
        'input' => [
            'class' => \flyiing\widgets\TouchSpin::className(),
            'pluginOptions' => [
                'max' => 99,
                'verticalbuttons' => true,
            ],
            'options' => [
                'style' => 'max-width: 45px; text-align: right;',
            ],
        ],
        'render' => function($value) {
                return sprintf('%dpx', $value);
            },
    ],

    'color' => [
        'input' => [
            'class' => kartik\color\ColorInput::className(),
            'pluginOptions' => [
            ],
        ],
    ],

    '_border_style' => [
        'input' => [

            'class' => \flyiing\widgets\Select2::className(),
            'items' => [
                'dotted' => Yii::t('css.prop.input', 'dotted'),
                'dashed' => Yii::t('css.prop.input', 'dashed'),
                'solid' => Yii::t('css.prop.input', 'solid'),
                'double' => Yii::t('css.prop.input', 'double'),
                'groove' => Yii::t('css.prop.input', 'groove'),
                'ridge' => Yii::t('css.prop.input', 'ridge'),
                'inset' => Yii::t('css.prop.input', 'inset'),
                'outset' => Yii::t('css.prop.input', 'outset'),
            ],
            'pluginOptions' => [
                'minimumResultsForSearch' => -1, // убираем строку поиска
                'dropdownCssClass' => 'bigdrop', // размер выпадающего списка поболе
            ],
        ],

    ],

    'border' => [
        'params' => [
            'width' => 'size-px',
            'style' => '_border_style',
            'color' => 'color',
        ],
        'default' => [
            'width' => 1,
            'style' => 'solid',
            'color' => '#000000',
        ],
    ],

    'background-image' => [
        'input' => [
            'class' => \flyiing\widgets\base\InputWidget::className(),
        ],
        'render' => function($value) {
                return sprintf('url("%s")', $value);
            },
    ],

];
