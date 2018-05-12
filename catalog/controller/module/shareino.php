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
        $selectedCategories = $this->config->get('shareino_selected_categories');
        $rows = $query->rows;

        if (!empty($selectedCategories)) {
            foreach ($rows as $i => $row) {
                $productCategories = $this->getProductCategories($row['product_id']);
                if (empty(array_intersect($selectedCategories, $productCategories))) {
                    unset($rows[$i]);
                }
            }
        }
        $ids = $this->array_pluck($rows, 'product_id');

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

        return array_map(function ($element) use ($column_name) {
            return $element[$column_name];
        }, $array);
    }

    public function status()
    {
        if ($this->checkActivePlugin()) {
            echo json_encode(array('status' => true), true);
        } else {
            echo json_encode(array('status' => false), true);
        }
    }

    private function checkActivePlugin()
    {
        $this->load->model('extension/extension');
        $extensions = $this->model_extension_extension->getExtensions('module');
        foreach ($extensions as $extension) {
            if (is_array($extension) && $extension['code'] === 'shareino') {
                return true;
            }
        }
        return false;
    }

    private function getProductCategories($product_id)
    {
        $product_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_category_data[] = $result['category_id'];
        }

        return $product_category_data;
    }

}
