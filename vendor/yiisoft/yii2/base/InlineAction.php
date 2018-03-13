<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * InlineAction represents an action that is defined as a controllers method.
 *
 * The name of the controllers method is available via [[actionMethod]] which
 * is set by the [[controllers]] who creates this action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class InlineAction extends Action
{
    /**
     * @var string the controllers method that this inline action is associated with
     */
    public $actionMethod;


    /**
     * @param string $id the ID of this action
     * @param Controller $controller the controllers that owns this action
     * @param string $actionMethod the controllers method that this inline action is associated with
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($id, $controller, $actionMethod, $config = [])
    {
        $this->actionMethod = $actionMethod;
        parent::__construct($id, $controller, $config);
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controllers.
     * @param array $params action parameters
     * @return mixed the result of the action
     */
    public function runWithParams($params)
    {
        $args = $this->controller->bindActionParams($this, $params);
        Yii::trace('Running action: ' . get_class($this->controller) . '::' . $this->actionMethod . '()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $args);
    }
}
