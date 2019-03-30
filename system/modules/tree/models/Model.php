<?php

    namespace system\modules\tree\models;

    class Model
    {
        public static function tableName() {}

        /**
         * @return mixed
         */
        public static function selectAll() {
            $sql = 'SELECT * FROM '.static::tableName();
            $data = \DB::prepare($sql)
                ->execute()
                ->fetchAll();

            return isset($data) ? $data : '';
        }

        /**
         * @param $id
         * @return mixed
         */
        public static function selectById($id) {
            if (!$id) {
                return false;
            }

            $sql = 'SELECT * FROM '.static::tableName().' WHERE id = ?';
            $data = \DB::prepare($sql)
                ->execute([$id])
                ->fetchAll();
            return $data;
        }


        /**
         * @param $id
         * @return mixed
         */
        public static function deleteById($id) {
            if (!$id) {
                return false;
            }

            $sql = 'DELETE FROM '.static::tableName().' WHERE id = ?';
            $result = \DB::prepare($sql)
                ->execute([$id]);
            return true;
        }


        /**
         * @param $id
         * @param $new_name
         * @return bool
         */
        public static function update($id, $new_name) {
            $sql = 'UPDATE ' . static::tableName() . ' SET title = ? WHERE id = ?';
            $exec = \DB::prepare($sql)
                ->execute([$new_name, $id]);

            return true;
        }
    }