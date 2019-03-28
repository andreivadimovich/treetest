<?php

    namespace system\modules\tree\controllers;
    use system\BaseController;

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
                    $_POST['url'],
                    $_POST['color'],
                    $_POST['price']
                );
            }

            // select all
            if (isset($_GET['all']) && !empty($_GET['all'])) {
                $all = self::toFlat(CategoryModel::selectWithProduct());
                $all = array_unique($all, SORT_REGULAR);
                exit(json_encode(
                    self::buildTree($all)
                ));
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

            // get cost
            if (isset($_GET['cost']) && isset($_GET['type']) && isset($_GET['id'])) {
                if ($_GET['type'] == 'product') {
                    return ProductModel::getCost((int)$_GET['id']);
                }

                if ($_GET['type'] == 'category') {
                    return CategoryModel::getCost((int)$_GET['id']);
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
                $view = '/modules/tree/views/form_add.php';
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
         * @return bool|string
         */
        protected function addProduct($title, $category_id, $color, $url, $price) {
            $product = new ProductModel();

            return $product->insert(
                $title,
                isset($category_id) ? (int)$category_id : 0,
                $color,
                $url,
                $price
            );
        }


        /**
         * @param array $a
         * @return array
         */
        protected static function toFlat(array $a) {
            $result=[];
            foreach ($a as $r) {
                if (isset($r['product_title']) && !empty($r['product_title'])
                    && isset($r['category_title']) && !empty($r['category_title'])) {

                    $new = array(
                        'parent' => $r['category_id'],
                        'product_id' => $r['product_id'],
                        'id' => 'product'.$r['product_id'],
                        'text' => $r['product_title'],
                        'icon' => $r['icon'],
                    );
                    array_push($result, $new);

                    unset($r['product_id'], $r['product_title']);
                }

                $r = array(
                    'parent' => $r['parent'],
                    'id' => $r['category_id'],
                    'text' => $r['category_title'],
                    'icon' => $r['icon'],
                );

                array_push($result, $r);
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