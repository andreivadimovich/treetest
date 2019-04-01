<?php

    namespace system\modules\tree\models;
    use system\modules\tree\models\Model;

    class ProductModel extends Model
    {

        public static function tableName()
        {
            return 'product';
        }

        public static function subTableName()
        {
            return 'category_product';
        }

        const COLORS = array(
            ['id' => 1, 'title' => 'white'],
            ['id' => 2, 'title' => 'green'],
            ['id' => 3, 'title' => 'black'],
            ['id' => 4, 'title' => 'teal'],
            ['id' => 5, 'title' => 'red'],
        );


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
        public function insert($name, $subcategory_id = 0, $color, $url, $price, $food) {
            if (self::isExist($name) > 0) {
                return 'Exists! Please select the other name';
            }

            if ($subcategory_id === 0) {
                return 'Select at least one category';
            }

            if (isset($name) && isset($color) && isset($url) && isset($price) && isset($food)) {
                $query = "INSERT INTO " . self::tableName() . " VALUES (?, ?, ?, ?, ?, ?)";
                $productAdd = \DB::prepare($query)
                    ->execute([NULL, $name, $color, $url, $price, $food]);
                if (!$productAdd) {
                    return 'Error! Cant create the new product';
                }
                $lastProductId = \DB::lastInsertId();

                if ($lastProductId) {
                    unset($query);
                    $query = "INSERT INTO " . self::subTableName() . " VALUES (?, ?, ?)";
                    $linkAdd = \DB::prepare($query)
                        ->execute([NULL, $lastProductId, (int)$subcategory_id]);
                    if (!$linkAdd) {
                        return 'Error! Try again after five minutes';
                    }

                    return true;
                }
            }

            return false;
        }


        public static function getInfo($id) {
            $product = self::selectById($id);
            if (!empty($product['0']) && count($product['0']) > 0) {
                return $product['0'] ? $product['0'] : 'info is empty';
            }

            return false;
        }
    }