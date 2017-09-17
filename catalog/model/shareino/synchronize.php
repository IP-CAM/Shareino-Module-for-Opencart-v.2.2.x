<?php

class ModelShareinoSynchronize extends Model
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

    public function synchronize($productId, $modified)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "shareino_synchronize` WHERE `product_id`=$productId");

        if ($query->num_rows > 0) {
            $result = $query->row['id'];

            $this->db->query("UPDATE `" . DB_PREFIX . "shareino_synchronize` SET "
                . "`date_sync`='" . date("Y-m-d H:i:s") . "',"
                . "`date_modified`='$modified' "
                . "WHERE `id`=$result");
        } else {

            $this->db->query("INSERT INTO `" . DB_PREFIX . "shareino_synchronize`"
                . " ( `product_id`, `date_sync`, `date_modified`) VALUES "
                . " ( '$productId', '" . date("Y-m-d H:i:s") . "',  '$modified')");
        }
    }

    public function destroy()
    {
        $query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "shareino_synchronize` "
            . "WHERE product_id NOT IN(SELECT product_id FROM `" . DB_PREFIX . "product`)");

        if ($query->num_rows > 0) {
            $idsToDelete = $this->array_pluck($query->rows, 'product_id');
            $this->db->query("DELETE FROM `" . DB_PREFIX . "shareino_synchronize` WHERE product_id IN(" . implode(',', $idsToDelete) . ")");
            return $idsToDelete;
        }
        return false;
    }

}
