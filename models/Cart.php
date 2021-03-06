<?php
/**
 * Created by PhpStorm.
 * User: Famaly
 * Date: 08.08.2016
 * Time: 0:05
 */

namespace app\models;

class Cart extends AppActiveModel
{
    public function behaviors()
    {
        return [
            'image' => [
                'class' => 'rico\yii2images\behaviors\ImageBehave',
            ]
        ];
    }

    public function addToCart($product, $qty = 1)
    {
        $mainImg = $product->getImage();
        if (isset($_SESSION['cart'][$product['id']])){
            $_SESSION['cart'][$product['id']]['QTY'] += $qty;
        } else {
            $_SESSION['cart'][$product['id']] = [
                'QTY' => $qty,
                'NAME' => $product['title'],
                'PRICE' => $product['price'],
                'IMG' => $mainImg->getUrl('x70'),
                'CODE' => $product['vendor_code'],
                'ID' => $product['id']
            ];
        }
        $_SESSION['cart.qty'] = isset($_SESSION['cart.qty'])
            ? $_SESSION['cart.qty'] + $qty
            : $qty;
        $_SESSION['cart.sum'] = isset($_SESSION['cart.sum'])
            ? $_SESSION['cart.sum'] + $_SESSION['cart'][$product['id']]['PRICE'] * $qty
            : $_SESSION['cart'][$product['id']]['PRICE'] * $qty;
        $_SESSION['cart.prc'] = ($_SESSION['cart.qty'] <= 10)
            ? $_SESSION['cart.qty'] * 2
            : 20;
    }

    public function deleteFromCart($id)
    {
        if ( !isset($_SESSION['cart'][$id]) ){
            return false;
        }
        $_SESSION['cart.qty'] = $_SESSION['cart.qty'] - $_SESSION['cart'][$id]['QTY'];
        $_SESSION['cart.sum'] = $_SESSION['cart.sum'] - ($_SESSION['cart'][$id]['PRICE'] * $_SESSION['cart'][$id]['QTY']);
        unset($_SESSION['cart'][$id]);
        $_SESSION['cart.prc'] = ($_SESSION['cart.qty'] <= 10)
            ? $_SESSION['cart.qty'] * 2
            : 20;
    }

    public function recalculationCart($id, $action)
    {
        if ( !isset($_SESSION['cart'][$id]) ){
            return false;
        }
        if ($action == 'down'){
            $_SESSION['cart'][$id]['QTY'] = $_SESSION['cart'][$id]['QTY'] - 1;
            $_SESSION['cart.qty'] = $_SESSION['cart.qty'] - 1;
            $_SESSION['cart.sum'] = $_SESSION['cart.sum'] - $_SESSION['cart'][$id]['PRICE'];
            $_SESSION['cart.prc'] = ($_SESSION['cart.qty'] <= 10)
                ? $_SESSION['cart.qty'] * 2
                : 20;
        }
        if ($action == 'up'){
            $_SESSION['cart'][$id]['QTY'] = $_SESSION['cart'][$id]['QTY'] + 1;
            $_SESSION['cart.qty'] = $_SESSION['cart.qty'] + 1;
            $_SESSION['cart.sum'] = $_SESSION['cart.sum'] + $_SESSION['cart'][$id]['PRICE'];
            $_SESSION['cart.prc'] = ($_SESSION['cart.qty'] <= 10)
                ? $_SESSION['cart.qty'] * 2
                : 20;
        }
    }
}