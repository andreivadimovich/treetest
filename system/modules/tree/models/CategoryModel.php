<?php

    namespace system\modules\tree\models;
    use system\modules\tree\models\Model;
    use system\modules\tree\models\ProductModel;

    class CategoryModel extends Model
    {
        public static function tableName()
        {
            return 'category';
        }

        /**
         * @param $val
         * @return mixed
         */
        public static function isExist($val) {
            $sql = 'SELECT * FROM '.self::tableName().' WHERE title = ?';
            $data = \DB::prepare($sql)
                ->execute([$val])
                ->rowCount();
            return $data;
        }


        /**
         * @param string $name
         * @param int $subcategory_id
         * @return bool|string
         */
        public function insert($name, $subcategory_id = 0) {
            if (self::isExist($name) > 0) {
                return 'Exists! Please select the other name';
            }

            $query = "INSERT INTO " . self::tableName() . " VALUES (?, ?, ?)";
            $category_add = \DB::prepare($query)
                ->execute([NULL, $name, $subcategory_id]);
            if (!$category_add) {
                return false;
            }
            $id = \DB::lastInsertId();
            return $id ? $id : false;
        }


        /**
         * @return mixed
         */
        public static function selectWithProduct() {

            $pt = ProductModel::tableName();
            $pst = ProductModel::subTableName();
            $ct = CategoryModel::tableName();

            $sql = "
                SELECT 
                    {$pt}.title as product_title, 
                    {$ct}.title as category_title, 
                    {$pt}.id as product_id, 
                    {$ct}.id as category_id, 
                    {$ct}.parent_id as `parent`,
                    IF({$pt}.id IS NULL, 'jstree-icon jstree-themeicon', '') as icon
                FROM {$ct} 
                    LEFT JOIN {$pst} ON {$pst}.category_id = {$ct}.id 
                    LEFT JOIN {$pt} ON {$pt}.id = {$pst}.product_id
                
                    ORDER BY {$ct}.id ASC
            ";

            $data = \DB::prepare($sql)
                ->execute()
                ->fetchAll();

            return isset($data) ? $data : '';
        }

        /**
         * @param int $id
         * @return string
         */
        public static function getCost($id) {
            $sql = "
                SELECT SUM(product.price) as all_cost
                FROM category 
                LEFT JOIN category_product ON category_product.category_id = category.id 
                LEFT JOIN product ON product.id = category_product.product_id 
                WHERE category.id = ? OR category.parent_id = ?
                ORDER BY category.id ASC
            ";
            $data = \DB::prepare($sql)
                ->execute([$id, $id])
                ->fetchAll();

            $cost = isset($data) ? $data['0']['all_cost'] : '';
            if ($cost) {
                $cost = number_format($cost, 2, '.', '');
            }

            return $cost;
        }
    }
