<?php
namespace app\components;

use yii\base\Widget;
use app\models\Category;

class MenuCategoryWidget extends Widget
{
    public $tpl;
    public $data;
    public $tree;
    public $html;

    public function init()
    {
        parent::init();
        if ($this->tpl === null){
            $this->tpl = 'menu';
        }
        $this->tpl .= '.php';
    }

    public function run()
    {
        $this->data = Category::find()->asArray()->indexBy('id')->all();
        debug($this->data);
        return $this->data;
    }
}