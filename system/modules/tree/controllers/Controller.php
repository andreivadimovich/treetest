<?php

    namespace system\modules\tree\controllers;
    use system\modules\core\controllers\BaseController;

    use system\modules\tree\models\CategoryModel;
    use system\modules\tree\models\ProductModel;

    class Controller extends BaseController
    {
        /**
         * @return bool|mixed|string
         */
        public function run() {
            $view = '';

            // add category
            if (isset($_GET['add_category']) && !empty($_GET['add_category'])
                && isset($_POST['title']) && !empty($_POST['title'])) {
                $insertCategory = $this->addCategory(
                    parent::clearVal($_POST['title']),
                    $_POST['subcategory']
                );
            }

            // add product
            if (isset($_GET['add_product']) && !empty($_GET['add_product'])
                && isset($_POST['title']) && !empty($_POST['title'])) {
                $insertProduct = $this->addProduct(
                    parent::clearVal($_POST['title']),
                    $_POST['subcategory'],
                    trim($_POST['url']),
                    $_POST['color'],
                    trim($_POST['price']),
                    trim($_POST['food'])
                );
            }

            // select all
            if (isset($_GET['all']) && !empty($_GET['all'])) {
                $all = CategoryModel::selectWithProduct();
                $all = self::toFlat($all);
                $all = self::buildTree($all);
                exit(json_encode($all));
            }

            // update
            if (isset($_GET['update']) && isset($_GET['type']) && isset($_GET['id']) && isset($_GET['new_name'])) {
                if ($_GET['type'] == 'product') {
                    $res = ProductModel::update(
                        (int)$_GET['id'],
                        parent::clearVal($_GET['new_name'])
                    );
                    return $res;
                }

                if ($_GET['type'] == 'category') {
                    $res = CategoryModel::update(
                        (int)$_GET['id'],
                        parent::clearVal($_GET['new_name'])
                    );
                    return $res;
                }
            }

            // delete
            if (isset($_GET['delete']) && isset($_GET['type']) && isset($_GET['id'])) {
                if ($_GET['type'] == 'product') {
                    $res = ProductModel::deleteById((int)$_GET['id']);
                    return $res;
                }

                if ($_GET['type'] == 'category') {
                    $res = CategoryModel::deleteById((int)$_GET['id']);
                    return $res;
                }
            }

            // get cost & more info
            if (isset($_GET['cost']) && isset($_GET['type']) && isset($_GET['id'])) {
                if ($_GET['type'] == 'product') {
                    $r = ProductModel::getInfo((int)$_GET['id']);
                    if (isset($r) && is_array($r) && count($r) > 0) {
                        return json_encode(array(
                            'price' => isset($r['price']) && !empty($r['price']) ? $r['price'] : 0,
                            'color' => isset($r['color']) ? ProductModel::COLORS[$r['color']]['title'] : '',
                            'url' => isset($r['url']) && !empty($r['url']) ? $r['url'] : '',
                            'food' => isset($r['food']) && !empty($r['food']) ? $r['food'] : 0,
                        ));
                    } else {
                        return json_encode(array(
                            'error' => 'not found'
                        ));

                    }
                }

                if ($_GET['type'] == 'category') {
                    return json_encode(array('price' => CategoryModel::getCost((int)$_GET['id'])));
                }
            }


            // select list of category
            $forProduct = '';
            $selectCategoryList = parent::createSelectList(CategoryModel::selectAll());
            if ($selectCategoryList) {
                $forProduct = parent::createSelectList(CategoryModel::selectAll(), false);
            }

            // select list of colors
            $productColors = parent::createSelectList(ProductModel::COLORS, false, 'color');


            // the add form view
            if (isset($_GET['add']) && !empty($_GET['add'])) {
                $view = APP.'system/modules/tree/views/form_add.php';
            }

            // build view
            $view = parent::getView($view, array( //
                'categoryList' => $selectCategoryList,
                'categoryForProduct' => $forProduct,
                'productColors' => $productColors,
            ));

            return $view;
        }


        /**
         * @param $title
         * @param $subcategory_id
         * @return bool|string
         */
        protected function addCategory($title, $subcategory_id) {
            $category = new CategoryModel();

            return $category->insert(
                $title,
                isset($subcategory_id) ? (int)$subcategory_id : 0
            );
        }


        /**
         * @param $title
         * @param $category_id
         * @param $color
         * @param $url
         * @param $price
         * @param $food
         * @return bool|string
         */
        protected function addProduct($title, $category_id, $color, $url, $price, $food) {
            $product = new ProductModel();

            return $product->insert(
                $title,
                isset($category_id) ? (int)$category_id : 0,
                $color,
                $url,
                $price,
                $food
            );
        }


        /**
         * @param array $a
         * @return array
         */
        protected static function toFlat(array $a) {
            $result=[];
            foreach ($a as $r) {

                //+node
                if ((isset($r['category_title']) && !empty($r['category_title'])
                    && isset($r['category_id']) && !empty($r['category_id']))) {

                        $key_id = parent::searchArrayValue($r['category_id'], 'id', $result);
                        $key_text = parent::searchArrayValue($r['category_title'], 'text', $result);

                        if ($key_id === false && $key_text === false) {
                            $new_category = array(
                                'parent' => !empty($r['parent']) ? $r['parent'] : null,
                                'id' => $r['category_id'],
                                'text' => $r['category_title'],
                                'icon' => $r['icon'],
                            );
                            array_push($result, $new_category);
                        }
                }

                //+child
                if (isset($r['product_title']) && !empty($r['product_title'])
                    && isset($r['category_title']) && !empty($r['category_title'])) {

                    $new = array(
                        'parent' => $r['category_id'],
                        'product_id' => $r['product_id'],
                        'id' => 'product' . $r['product_id'],
                        'text' => $r['product_title'],
                        'icon' => $r['icon'],
                    );
                    array_push($result, $new);
                    unset($r['product_id'], $r['product_title']);
                }
            }

            return $result;
        }


        /**
         * @param array $elements
         * @param array $options
         * @param int $parentId
         * @return array
         */
        protected static function buildTree(array $elements, $options = [
            'parent_id_column_name' => 'parent',
            'children_key_name' => 'children',
            'id_column_name' => 'id'], $parentId = 0) {
            $branch = array();
            foreach ($elements as $element) {
                if ($element[$options['parent_id_column_name']] == $parentId) {
                    $children = self::buildTree($elements, $options, $element[$options['id_column_name']]);

                    if ($children) {
                        $element[$options['children_key_name']] = $children;
                    } else {
                        $element[$options['children_key_name']] = [];
                    }

                    $element['data'] = $element['id'];
                    unset($element['parent'], $element['product_id'], $element['id']);

                    $branch[] = $element;
                }
            }
            return $branch;
        }
    }