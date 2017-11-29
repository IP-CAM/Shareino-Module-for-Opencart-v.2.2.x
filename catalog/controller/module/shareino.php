<?php

class ControllerModuleShareino extends Controller
{

    const SIZE = 80;

    public function index()
    {
        // Load Model
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        $this->load->model('shareino/requset');
        $this->load->model('shareino/products');
        $this->load->model('shareino/synchronize');

        // DB
        $product = DB_PREFIX . "product";
        $synchronize = DB_PREFIX . "shareino_synchronize";
        $query = $this->db->query("SELECT * FROM $product WHERE $product.product_id "
            . "NOT IN(SELECT $synchronize.product_id FROM $synchronize) "
            . "OR $product.date_modified "
            . "NOT IN(SELECT $synchronize.date_modified FROM $synchronize) LIMIT " . self::SIZE);

        // No item found
        if ($query->num_rows === 0) {
            return;
        }

        // Read token fontend
        $shareinoSetting = $this->model_setting_setting->getSetting('shareino');
        if ($this->request->get['key'] !== $shareinoSetting['shareino_token_frontend']) {
            return;
        }

        // Selected Products Id
        $ids = $this->array_pluck($query->rows, 'product_id');

        // Get JSON
        $products = $this->model_shareino_products->products($ids);

        if (empty($products)) {
            return;
        }

        // Send To SHAREINO
        $result = $this->model_shareino_requset->sendRequset('products', json_encode($products), 'POST');

        //
        if ($result) {
            foreach ($ids as $id) {
                $product = $this->model_catalog_product->getProduct($id);
                $this->model_shareino_synchronize->synchronize($id, $product['date_modified']);
            }
        }
    }

    protected function array_pluck($array, $column_name)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column_name);
        }

        return array_map(function($element) use($column_name) {
            return $element[$column_name];
        }, $array);
    }

}
