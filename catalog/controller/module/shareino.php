<?php

class ControllerModuleShareino extends Controller
{

    const SIZE = 80;

    public function index()
    {
        // Load Model
        $this->load->model('shareino/requset');
        $this->load->model('setting/setting');
        $this->load->model('shareino/products');

        // DB
        $product = DB_PREFIX . "product";
        $synchronize = DB_PREFIX . "shareino_synchronize";
        $query = $this->db->query("SELECT * FROM $product WHERE $product.product_id "
            . "NOT IN(SELECT $synchronize.product_id FROM $synchronize) "
            . "OR $product.date_modified "
            . "NOT IN(SELECT $synchronize.date_modified FROM $synchronize) LIMIT " . self::SIZE);

        // No Item Finde
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

        // Send To SHAREINO
        $this->model_shareino_requset->sendRequset('products', json_encode($products), 'POST');
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
