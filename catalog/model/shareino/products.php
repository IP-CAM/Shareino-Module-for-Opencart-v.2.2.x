<?php

class ModelShareinoProducts extends Model
{

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
        $this->load->model('catalog/category');
        $this->load->model('shareino/attribute');

        $website = $this->config->get('config_url') ?
            $this->config->get('config_url') : 'http://' . $_SERVER['SERVER_NAME'] . '/';

        $productId = $product['product_id'];

        $product_specials = $this->getProductSpecials($productId);
        $product_discounts = $this->getProductDiscounts($productId);

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

        $images = $this->getProductImages($productId);
        $productImages = array();
        foreach ($images as $image) {
            if ($image['image']) {
                $productImages[] = $website . 'image/' . $image['image'];
            }
        }

        $attributesValues = $this->getProductAttributes($productId);
        $attributes = array();
        foreach ($attributesValues as $attr) {
            if (!isset($attr['attribute_id'])) {
                break;
            }

            $attribute = $this->getAttribute();
            $attributes[$attribute['name']] = array(
                'label' => $attribute['name'],
                'value' => reset($attr['product_attribute_description'])['text']
            );
        }

        $variants = array();
        $contents = $this->getProductOptions($productId);
        foreach ($contents as $content) {
            if (($content['type'] !== 'select') && ($content['type'] !== 'radio')) {
                continue;
            }

            foreach ($content['product_option_value'] as $i => $value) {
                $productOptionValue = $this->getProductOptionValue($productId, $value['product_option_value_id']);

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
                    'quantity' => $product['quantity'],
                    'price' => $price,
                    'discount' => array()
                );
            }
            break;
        }

        $options = array();
        foreach ($contents as $content) {
            foreach ($content['product_option_value'] as $i => $value) {
                $productOptionValue = $this->getProductOptionValue($productId, $value['product_option_value_id']);

                $price = $product['price'] + $value['price'];
                if ($value['price_prefix'] === '-') {
                    $price = $product['price'] - $value['price'];
                }

                $options [$value['option_value_id']] = array(
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
            'categories' => $this->getProductCategories($productId),
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
            'out_of_stock' => $this->config->get('shareino_out_of_stock') === null ? 0 : $this->config->get('shareino_out_of_stock'),
            'variants' => $variants,
            'options' => $options
        );

        return $productDetail;
    }

    protected function getProductDiscounts($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

        return $query->rows;
    }

    protected function getProductSpecials($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

        return $query->rows;
    }

    protected function getProductImages($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    protected function getProductOptions($product_id)
    {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value WHERE product_option_id = '" . (int)$product_option['product_option_id'] . "'");

            foreach ($product_option_value_query->rows as $product_option_value) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'points' => $product_option_value['points'],
                    'points_prefix' => $product_option_value['points_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix']
                );
            }

            $product_option_data[] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => $product_option['value'],
                'required' => $product_option['required']
            );
        }

        return $product_option_data;
    }

    protected function getProductOptionValue($product_id, $product_option_value_id)
    {
        $query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    protected function getProductCategories($product_id)
    {
        $product_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_category_data[] = $result['category_id'];
        }

        return $product_category_data;
    }

    protected function getProductAttributes($product_id)
    {
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
            $product_attribute_data = array();

            $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

            foreach ($product_attribute_query->rows as $product_attribute) {
                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $product_attribute['name'],
                    'text' => $product_attribute['text']
                );
            }

            $product_attribute_group_data[] = array(
                'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                'name' => $product_attribute_group['name'],
                'attribute' => $product_attribute_data
            );
        }

        return $product_attribute_group_data;
    }

    protected function getAttribute($attribute_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE a.attribute_id = '" . (int)$attribute_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

}
