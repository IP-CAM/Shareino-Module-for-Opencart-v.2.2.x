<?php

class ModelShareinoProducts extends Model
{

    protected function array_pluck($array, $column_name)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column_name);
        }

        return array_map(function($element) use($column_name) {
            return $element[$column_name];
        }, $array);
    }

    public function getAllIdes()
    {
        /*
         * تمام ایدی های محصولات را استخراج میکند
         * کاربرد ان برای ارسال کالاها با جیسون میباشد
         */

        /*
         * SELECT * FROM oc_product WHERE ( oc_product.product_id NOT IN( SELECT oc_shareino_synchronize.product_id FROM oc_shareino_synchronize ) OR oc_product.date_modified NOT IN( SELECT oc_shareino_synchronize.date_modified FROM oc_shareino_synchronize ) ) AND oc_product.status = 1
         */
        $product = DB_PREFIX . "product";
        $synchronize = DB_PREFIX . "shareino_synchronize";

        $result = $this->db->query("SELECT COUNT(*) AS 'count' FROM $synchronize");

        if (!$result->row['count']) {
            $query = $this->db->query("SELECT `product_id` FROM $product"); //WHERE `status`=1");
        } else {
            $query = $this->db->query("SELECT * FROM $product WHERE $product.product_id "
                . "NOT IN(SELECT $synchronize.product_id FROM $synchronize) "
                . "OR $product.date_modified "
                . "NOT IN(SELECT $synchronize.date_modified FROM $synchronize)"); //AND $product.status =1");
        }
        if ($query->rows > 0) {
            return $this->array_pluck($query->rows, 'product_id');
        }
        return false;
    }

    public function getAllProducts($productIds = array(), $type = 0)
    {
        $this->load->model('catalog/product');
        if ($type) {
            /*
             * لیست  پنجاه عدد کالایی رو که هربار میخواهیم ارسال کنیم استخراج میکنید
             */
            $productsArray = array();
            foreach ($productIds as $value) {
                $product = $this->model_catalog_product->getProduct($value);
                $productsArray[] = $this->getProductDetail($product);
            }
        } else {
            /*
             * لیست تمام محصولات یک فروشگاه را استخراج میکند
             */
            $products = $this->model_catalog_product->getProducts(); //array("filter_status" => 1)
            $productsArray = array();
            foreach ($products as $product) {
                $productsArray[] = $this->getProductDetail($product);
            }
        }
        return $productsArray;
    }

    function getProduct($id)
    {
        /*
         * فقط یک محصول را استخراج میکند
         */
        $this->load->model('catalog/product');
        $this->load->model('catalog/attribute');

        $product = $this->model_catalog_product->getProduct($id);
        return $this->getProductDetail($product);
    }

    function getProductDetail($product)
    {
        if ($product == null) {
            return array();
        }
        $productId = $product['product_id'];

        /*
         * مدل های سیستم
         */
        $this->load->model('catalog/product');
        $this->load->model('catalog/attribute');
        $this->load->model('catalog/category');

        /*
         * هنگام سازی محصولات
         */
        $this->load->model('shareino/synchronize');
        $this->model_shareino_synchronize->synchronize($productId, $product['date_modified']);


        //چک کردن کالا برای  تخفیف
        $product_specials = $this->model_catalog_product->getProductSpecials($productId);
        $product_discounts = $this->model_catalog_product->getProductDiscounts($productId);

        $listDiscounts = array();
        if ($product_specials) {
            foreach ($product_specials as $product_special) {
                if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
                    $listDiscounts[] = array(
                        'amount' => $product['price'] - $product_special['price'],
                        'start_date' => $product_special['date_start'] . ' 00:00:00',
                        'end_date' => $product_special['date_end'] . ' 00:00:00',
                        'quantity' => 1,
                        'type' => 0
                    );
                }
            }
        }

        if ($product_discounts) {
            foreach ($product_discounts as $product_discount) {

                if (($product_discount['date_start'] == '0000-00-00' || strtotime($product_discount['date_start']) < time()) && ($product_discount['date_end'] == '0000-00-00' || strtotime($product_discount['date_end']) > time())) {
                    $listDiscounts[] = array(
                        'amount' => $product['price'] - $product_discount['price'],
                        'start_date' => $product_discount['date_start'] . ' 00:00:00',
                        'end_date' => $product_discount['date_end'] . ' 00:00:00',
                        'quantity' => $product_discount['quantity'],
                        'type' => 0
                    );
                }
            }
        }

        /*
         * دریافت همه تصاویر یک کالا
         */
        $images = $this->model_catalog_product->getProductImages($productId);
        $productImages = array();
        foreach ($images as $image) {
            if ($image['image']) {
                $productImages[] = 'http://' . $_SERVER['SERVER_NAME'] . '/image/' . $image['image'];
            }
        }

        /*
         * دریافت خصوصیات یک کالا
         */
        $attributesValues = $this->model_catalog_product->getProductAttributes($productId);
        $attributes = array();

        foreach ($attributesValues as $attr) {
            $attribute = $this->model_catalog_attribute->getAttribute($attr['attribute_id']);
            $attributes[$attribute['name']] = array(
                'label' => $attribute['name'],
                'value' => reset($attr['product_attribute_description'])['text']
            );
        }

        /*
         * ساختار ارسالی به شرینو
         */
        $productDetail = array(
            'name' => $product['name'],
            'code' => $product['product_id'],
            'sku' => $product['sku'],
            'price' => $product['price'],
            'active' => $product['status'],
            'sale_price' => '',
            'discount' => $listDiscounts,
            'quantity' => $product['quantity'],
            'weight' => $product['weight'],
            'original_url' => 'http://' . $_SERVER['SERVER_NAME'] . '/index.php?route=product/product&product_id=' . $product['product_id'],
            'brand_id' => '',
            'categories' => $this->model_catalog_product->getProductCategories($productId),
            'short_content' => '',
            'long_content' => $product['description'],
            'meta_keywords' => $product['meta_keyword'],
            'meta_description' => $product['meta_description'],
            'meta_title' => $product['meta_title'],
            'image' => 'http://' . $_SERVER['SERVER_NAME'] . '/image/' . $product['image'],
            'images' => $productImages,
            'attributes' => $attributes,
            'tags' => explode(',', $product['tag']),
            'available_for_order' => 1,
            'out_of_stock' => 0
        );
        return $productDetail;
    }

}
