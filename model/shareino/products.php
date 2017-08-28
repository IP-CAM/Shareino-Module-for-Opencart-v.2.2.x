<?php

class ModelShareinoProducts extends Model
{

    public function getCount()
    {
        $product = DB_PREFIX . "product";
        $synchronize = DB_PREFIX . "shareino_synchronize";

        $query = $this->db->query("SELECT COUNT(*) AS total FROM $product WHERE $product.product_id "
            . "NOT IN(SELECT $synchronize.product_id FROM $synchronize) "
            . "OR $product.date_modified "
            . "NOT IN(SELECT $synchronize.date_modified FROM $synchronize)");

        if ($query->rows > 0) {
            return $query->rows[0]['total'];
        }
        return 0;
    }

    public function getIdes($limit)
    {
        $product = DB_PREFIX . "product";
        $synchronize = DB_PREFIX . "shareino_synchronize";

        $query = $this->db->query("SELECT * FROM $product WHERE $product.product_id "
            . "NOT IN(SELECT $synchronize.product_id FROM $synchronize) "
            . "OR $product.date_modified "
            . "NOT IN(SELECT $synchronize.date_modified FROM $synchronize) "
            . "LIMIT $limit");

        if ($query->rows > 0) {
            return $this->array_pluck($query->rows, 'product_id');
        }
        return false;
    }

    public function products($ids = array())
    {
        $this->load->model('catalog/product');

        $products = array();
        foreach ($ids as $id) {
            $products[] = $this->getProductDetail($this->model_catalog_product->getProduct($id));
        }

        return $products;
    }

    protected function getProductDetail($product)
    {
        if ($product == null) {
            return array();
        }

        $this->load->model('setting/setting');
        $website = $this->config->get('config_url') ?
            $this->config->get('config_url') : 'http://' . $_SERVER['SERVER_NAME'] . '/';


        $productId = $product['product_id'];
        $this->load->model('catalog/product');
        $this->load->model('catalog/attribute');
        $this->load->model('catalog/category');

        $this->load->model('shareino/synchronize');
        $this->model_shareino_synchronize->synchronize($productId, $product['date_modified']);

        $product_specials = $this->model_catalog_product->getProductSpecials($productId);
        $product_discounts = $this->model_catalog_product->getProductDiscounts($productId);

        $listDiscounts = array();
        if ($product_specials) {
            foreach ($product_specials as $product_special) {
                if (($product_special['date_start'] == '0000-00-00' || strtotime($product_special['date_start']) < time()) && ($product_special['date_end'] == '0000-00-00' || strtotime($product_special['date_end']) > time())) {
                    $listDiscounts[] = array(
                        'amount' => $product['price'] - $product_special['price'],
                        'start_date' => $product_special['date_start'],
                        'end_date' => $product_special['date_end'],
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
                        'start_date' => $product_discount['date_start'],
                        'end_date' => $product_discount['date_end'],
                        'quantity' => $product_discount['quantity'],
                        'type' => 0
                    );
                }
            }
        }

        $images = $this->model_catalog_product->getProductImages($productId);
        $productImages = array();
        foreach ($images as $image) {
            if ($image['image']) {
                $productImages[] = $website . 'image/' . $image['image'];
            }
        }

        $attributesValues = $this->model_catalog_product->getProductAttributes($productId);
        $attributes = array();

        foreach ($attributesValues as $attr) {
            $attribute = $this->model_catalog_attribute->getAttribute($attr['attribute_id']);
            $attributes[$attribute['name']] = array(
                'label' => $attribute['name'],
                'value' => reset($attr['product_attribute_description'])['text']
            );
        }

        $variants = array();
        $contents = $this->model_catalog_product->getProductOptions($productId);
        foreach ($contents as $content) {
            if (($content['type'] !== 'select') && ($content['type'] !== 'radio')) {
                continue;
            }

            foreach ($content['product_option_value'] as $i => $value) {
                $productOptionValue = $this->model_catalog_product->getProductOptionValue($productId, $value['product_option_value_id']);

                $price = $product['price'] + $value['price'];
                if ($value['price_prefix'] === '-') {
                    $price = $product['price'] - $value['price'];
                }

                $variants [$value['option_value_id']] = array(
                    'variation' => array(
                        $productOptionValue['name'] => array(
                            'label' => $content['name'],
                            'value' => $productOptionValue['name']
                        )
                    ),
                    'code' => $productId,
                    'default_value' => $i === 0 ? '1' : '0',
                    'quantity' => $value['quantity'],
                    'price' => $price,
                    'discount' => array()
                );
            }
            break;
        }

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
            'original_url' => $website . 'index.php?route=product/product&product_id=' . $product['product_id'],
            'brand_id' => '',
            'categories' => $this->model_catalog_product->getProductCategories($productId),
            'short_content' => '',
            'long_content' => $product['description'],
            'meta_keywords' => $product['meta_keyword'],
            'meta_description' => $product['meta_description'],
            'meta_title' => $product['meta_title'],
            'image' => $product['image'] ? $website . 'image/' . $product['image'] : '',
            'images' => $productImages,
            'attributes' => $attributes,
            'tags' => explode(',', $product['tag']),
            'available_for_order' => 1,
            'out_of_stock' => 0,
            'variants' => $variants
        );
        return $productDetail;
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
