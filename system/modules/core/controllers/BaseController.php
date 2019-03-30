<?php

    namespace system\modules\core\controllers;

    class BaseController
    {
        /**
         * @param $content
         * @return mixed|string
         */
        protected static function withMainView($content) {
            $view_path = APP.'system/modules/core/views/main_layout.php';
            $layout = file_get_contents($view_path);
            $built = str_replace('#page_view#', $content, $layout);
            return $built ? $built : '';
        }

        /**
         * @param string $path_name absolute path to file
         * @param array $params
         * @return bool|string
         */
        public static function getView($path_name = '', $params = array()) {
            if ($path_name !== '') {
                if (!file_exists($path_name)) {
                    return false;
                }

                $content = file_get_contents($path_name);
                if (!$content) {
                    return false;
                }

                if (count($params) == 0) {
                    return self::withMainView($content);
                }
            } else {
                $content = false;
            }


            // Search for tags and change their values
            if (isset($content) && strlen($content) > 0  && preg_match_all('/\#(.+?)\#/', $content, $result)) {
                if (is_array($result[0]) && count($result[0]) > 0) {
                    foreach ($result[0] as $row) {
                        $variable_name = str_replace('#','', $row);
                        if (isset($params[$variable_name]) && !empty($params[$variable_name])) {
                            $content = str_replace($row, $params[$variable_name], $content);
                        }
                    }
                }
            }

            return self::withMainView($content);
        }


        /**
         * @param $val
         * @return mixed
         */
        public static function clearVal($val) {
            return preg_replace('~[^0-9a-zA-Z\ \-]+~u', '', trim($val));
        }


        /**
         * @param $params
         * @return bool|string
         */
        public static function createSelectList($params, $defaultValue = true, $select_name = 'subcategory') {
            if (isset($params) && count($params) > 0) {
                $html_select = '<select name="'.$select_name.'">';

                if ($defaultValue === true) {
                    $html_select .= '<option value="0">yes</option>';
                }

                $search_flag = false;
                foreach ($params as $param) {
                    if (!isset($param['id']) || !isset($param['title'])) {
                        continue;
                    }

                    $html_select .= '<option value='.$param['id'].">".$param['title']."</option>";
                    $search_flag = true;
                }
                $html_select .= '</select>';

                return $search_flag === true ? $html_select : false;
            }

            return '<select name="'.$select_name.'"><option value="0">parent</option></select>';
        }


        /**
         * @param $value
         * @param $key
         * @param $array
         * @return bool|int|string
         */
        protected static function searchArrayValue($value, $key, $array) {
            foreach ($array as $k => $v) {
                if ($v[$key] == $value) {
                    return $k;
                }
            }

            return false;
        }
    }