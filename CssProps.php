<?php

namespace flyiing\css;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class CssProps
 * @package flyiing\css
 */
class CssProps extends \yii\base\Object
{

    public static $typesPath = '@flyiing/css/types.php';

    public static $i18n = null;

    protected static $_customTypes = [];
    /**
     * @var array|null Внутрення переменная для хранения массива типов css-свойств
     */
    protected static $_types = null;

    /**
     * @var array|null Внутрення переменная для хранения массива css-свойств
     */
    protected static $_props = null;


    public static function initI18n()
    {
        if (static::$i18n === null) {
            static::$i18n = [
                'class' => \yii\i18n\PhpMessageSource::className(),
                'basePath' => '@flyiing/css/messages',
                'forceTranslation' => true,
            ];
        }
        Yii::$app->i18n->translations['css*'] = static::$i18n;
    }

    public static function setCustomTypes($types)
    {
        static::$_customTypes = $types;
        if (static::$_types !== null) {
            static::$_types = ArrayHelper::merge(static::$_types, static::$_customTypes);
        }
    }

    /**
     * Инициализирует и возвращет массив типов css-свойств
     * @param bool $forced
     * @return array|null
     */
    public static function initTypes($forced = false)
    {
        if (static::$_types === null || $forced) {
            static::initI18n();
            static::$_types = ArrayHelper::merge(require(Yii::getAlias(static::$typesPath)),
                static::$_customTypes);
        }
        return static::$_types;
    }

    /**
     * Функция возвращает массив типов css-свойств или, если задан параметр-ключ $type,
     * только один элемент этого массива с заданным ключем.
     * @param string|null $type Имя типа
     * @return array|bool
     */
    public static function getTypes($type = null)
    {
        if (static::$_types === null) {
            static::initTypes();
        }
        if ($type === null) {
            return static::$_types;
        } else {
            return ArrayHelper::getValue(static::$_types, $type, false);
        }
    }

    /**
     * Инициализирует и возвращет массив известных css-свойств
     * @param bool $forced
     * @throws \yii\base\InvalidConfigException
     * @return array|null
     */
    public static function initProps($forced = false)
    {
        if (static::$_props === null || $forced) {
            static::$_props = require(__DIR__ . DIRECTORY_SEPARATOR . 'props.php');
            foreach (static::$_props as $prop => $config) {
                while (isset($config['parent'])) {
                    $config = static::getProps($config['parent']);
                    if ($config === false) {
                        throw new InvalidConfigException(sprintf('Unknown property name[%s] in parents for [%s].',
                            $config['parent'], $prop));
                    }
                    static::$_props[$prop] = ArrayHelper::merge($config, static::$_props[$prop]);
                }
            }
        }
        return static::$_props;
    }

    /**
     * Возвращает конфиг известных css-свойств или, если задан параметр-ключ $name,
     * только один элемент этого массива с заданным ключем.
     * @param string|null $name Имя своства
     * @return array|bool
     */
    public static function getProps($name = null)
    {
        if (static::$_props === null) {
            static::initProps();
        }
        if ($name === null) {
            return static::$_props;
        } else {
            return ArrayHelper::getValue(static::$_props, $name, false);
        }
    }

    /**
     * Упорядочивает $props в соответсвии с порядком свойств заданном в конфиге.
     * @param array $props
     * @return array|bool
     */
    public static function sort($props)
    {
        $return = static::getProps();
        foreach($return as $k => $v) {
            if (isset($props[$k])) {
                $return[$k] = $props[$k];
            } else {
                unset($return[$k]);
            }
        }
        return $return;
    }

    /**
     * Возвращает строку css-свойств готовых для отображения в коде страниц.
     * Например, `#ff00aa` или `1px solid #cc0000`
     * @param string $type Тип css-свойства (ключ из массива типов)
     * @param array $value Значение.
     * @return bool|string Строка значения css-свойства.
     */
    public static function renderValue($type, $value)
    {
        $config = static::getTypes($type);
        if ($config === false || !is_array($config)) {
            return false;
        }
        if (is_array($value)) {
            $value = ArrayHelper::getValue($value, 'value', null);
        }
        if ($value === false) { // значение не задано
            return false;
        }
        if (isset($config['render'])) {
            return call_user_func($config['render'], $value);
        }
        if (is_string($value)) {
            return $value;
        } else if (is_array($value) && is_array($params = ArrayHelper::getValue($config, 'params'))) {
            $return = '';
            foreach ($params as $pName => $pConfig) {
                if (is_string($pConfig)) {
                    $pType = $pConfig;
                } else {
                    continue;
                }
                $return .= static::renderValue($pType, ArrayHelper::getValue($value, $pName)) . ' ';
            }
            if (strlen($return) > 0) {
                return substr($return, 0, -1);
            }
        }
        return false;
    }

    /**
     * Отрисовывает массив css-свойств в css-код(без селектора)
     * @param array $css
     * @param string $propPrefix
     * @param string $propSuffix
     * @return string
     */
    public static function render($css, $propPrefix = '  ', $propSuffix = PHP_EOL)
    {
        $return = '';
        foreach (static::sort($css) as $name => $value) {
            if (ArrayHelper::getValue($value, 'use', 0) < 1) {
                continue;
            }
            $type = ArrayHelper::getValue(static::getProps($name), 'type', false);
            if ($type === false) {
                continue;
            }
            $rvalue = static::renderValue($type, $value);
            if ($rvalue === false) {
                continue;
            }
            $return .= $propPrefix . $name .': '. $rvalue .';'. $propSuffix;
        }
        return $return;
    }

    /**
     * Отрисовывает набор css-стилей с селекторами
     * При этом есть возможность задать алиасы селекторов.
     * @param array $css
     * @param array $aliases
     * @return string
     */
    public static function renderStyles($css, $aliases = [])
    {
        $return = '';
        foreach ($aliases as $from => $to) {
            if (isset($css[$from])) {
                $css[$to] = $css[$from];
                unset($css[$from]);
            }
        }
        ksort($css);
        foreach ($css as $sel => $props) {
            $ruleBody = static::render($props);
            if (strlen($ruleBody) > 0) {
                $return .= $sel .' {'. PHP_EOL . $ruleBody .'}'. PHP_EOL;
            }
        }
        return $return;
    }

}
