<?php
/**
 * Created by PhpStorm.
 * User: Famaly
 * Date: 08.08.2016
 * Time: 0:02
 */

namespace app\controllers;
use app\models\Product;
use app\modules\admin\models\Products;
use app\models\Cart;
use Yii;
use app\models\Order;
use app\models\OrderItem;

class CartController extends AppController
{
    public function actionShow()
    {
        $session = Yii::$app->session;
        $session->open();
        $this->layout = false;
        if (empty($session['cart'])){
            return false;
        }
        return $this->render('cart_modal', compact('session'));
    }

    public function actionAdd($id, $qty = 1)
    {
        $product = Products::find()
            ->select(['id', 'title', 'vendor_code', 'price'])
            ->where(['id' => $id])
            ->one();
        if (empty($product)) return false;
        $session = Yii::$app->session;
        $session->open();
        $cart = new Cart();
        $cart->addToCart($product, $qty);
        if (!Yii::$app->request->isAjax){
            return $this->redirect(Yii::$app->request->referrer);
        }
        $this->layout = false;

        return $this->render('cart_modal', compact('session'));
    }

    public function actionLiketocart()
    {
        $session = Yii::$app->session;
        $session->open();

        foreach ($_SESSION['like'] as $item){
            $product = Products::find()
                ->select(['id', 'title', 'vendor_code', 'price'])
                ->where(['id' => $item['ID']])
                ->one();
            if (empty($product)) continue;
            $cart = new Cart();
            $cart->addToCart($product, $item['QTY']);
        }

        $session->remove('like');
        $session->remove('like.qty');
        $session->remove('like.sum');
        $session->remove('like.prc');
        $this->layout = false;

        return $this->render('cart_modal', compact('session'));
    }

    public function actionRecalc($id, $action)
    {
        $session = Yii::$app->session;
        $session->open();
        $cart = new Cart();
        $cart->recalculationCart($id, $action);
        if (!Yii::$app->request->isAjax){
            return $this->redirect(Yii::$app->request->referrer);
        }
        $this->layout = false;

        return $this->render('cart_modal', compact('session'));
    }

    public function actionClear()
    {
        $session = Yii::$app->session;
        $session->open();
        $this->layout = false;
        $session->remove('cart');
        $session->remove('cart.qty');
        $session->remove('cart.sum');
        unset($_COOKIE['QTY']);

        return $this->render('cart_modal', compact('session'));
    }

    public function actionDelete($id)
    {
        $session = Yii::$app->session;
        $session->open();
        $cart = new Cart();
        $cart->deleteFromCart($id);
        if (!Yii::$app->request->isAjax){
            return $this->redirect(Yii::$app->request->referrer);
        }
        $this->layout = false;
        return $this->render('cart_modal', compact('session'));
    }

    public function actionOrder()
    {
        $session = Yii::$app->session;
        $session->open();
        $this->like = $_SESSION['like.qty'];
        $this->setCatMeta('T-Fashion | Корзина');
        $order = new Order();
        if ($order->load(Yii::$app->request->post())){
            $order->qty = $session['cart.qty'];
            $order->black_sum = $session['cart.sum'];
            $order->prc = $session['cart.prc'];
            $order->sum = ($order->black_sum / 100) * (100 - $order->prc);
            if ($order->save()){
                $this->saveOrderItems($session['cart'], $order->id);
                $name = $order->name;
                $oid = $order->name;
                ob_start();
                include __DIR__ . '/mail/order.php';
                $body = ob_get_clean();
                $this->saveOrderItems($session['cart'], $order->id);
                Yii::$app->mailer->compose()
                    ->setFrom(['info@tatyana-fashion.ru' => 'Tatyana Fashion - клиентская служба'])
                    ->setTo($order->email)
                    ->setSubject('Ваш заказ в интернет-магазине Tatyana Fashion')
                    ->setHtmlBody($body)
                    ->send();
                ob_start();
                include __DIR__ . '/mail/admin.php';
                $adbody = ob_get_clean();
                Yii::$app->mailer->compose()
                    ->setFrom(['no-reply@tatyana-fashion.ru' => 'Tatyana Fashion'])
                    ->setTo('tanyakhonya@gmail.com')
                    ->setSubject('Новый заказ в интернет-магазине Tatyana Fashion')
                    ->setHtmlBody($adbody)
                    ->send();
                Yii::$app->session->setFlash('success', '<strong>Благодарим Вас Ваш заказ!</strong> Ближайшее время с Вами свяжется наш менеджер.');
                $session->remove('cart');
                $session->remove('cart.qty');
                $session->remove('cart.sum');
                $session->remove('cart.prc');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', '<strong>Ошибка оформления заказа</strong>. Попробуйте снова или свяжитесь с нами.');
            }

        }

        return $this->render('order', compact('session', 'order'));
    }

    protected function saveOrderItems($items, $order_id){
        foreach($items as $id => $item){
            $order_items = new OrderItem();
            $order_items->order_id = $order_id;
            $order_items->product_id = $id;
            $order_items->name = $item['NAME'];
            $order_items->price = $item['PRICE'];
            $order_items->qty_item = $item['QTY'];
            $order_items->summ_item = $item['QTY'] * $item['PRICE'];
            $order_items->save();
        }
    }
}