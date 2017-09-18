<?php

class ModelShareinoRequset extends Model
{

    const SHAREINO_API_URL = 'https://shareino.ir/api/v1/public/';
    const Version = '1.2.11';

    public function sendRequset($url, $body, $method)
    {
        // Get api token from server
        $this->load->model('setting/setting');
        $shareinoSetting = $this->model_setting_setting->getSetting('shareino');

        if ($shareinoSetting) {

            // Init curl
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            // SSL check
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);

            // Generate url and set method in url
            $url = self::SHAREINO_API_URL . $url;
            curl_setopt($curl, CURLOPT_URL, $url);

            // Set method in curl
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

            // Set Body if its exist
            if ($body != null) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            }

            // Get result
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer ' . $shareinoSetting['shareino_api_token'],
                'User-Agent: OpenCart_module_' . self::Version
                )
            );

            // Get result
            curl_exec($curl);

            // Get Header Response header
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpcode != 200) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function deleteProducts($ids, $all = false)
    {
        $body = array();
        $url = 'products';
        // Chek if want to delete All product
        if ($all) {
            $body = array('type' => 'all');
        } else {
            // check if want to delete multiple
            if (is_array($ids)) {
                $body = array('type' => 'selected', 'code' => $ids);
            } // if want to delete once
            else {
                $url .= "/$ids";
            }
        }
        $result = $this->sendRequset($url, json_encode($body), 'DELETE');
        return $result;
    }

}
