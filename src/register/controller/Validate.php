<?php

class Validate {
        private $_passed = false,
                $error = array(),
                $_db = null;

        /*public function __construct(){
            $this->_db = DatabaseConnection::getInstance();
        }*/

        public function check($source,$items = array()){
            foreach ($items as $item => $rules){
                foreach ($rules as $rule =>$rule_value){
                    $value = $source[$item];
                    if($rule === 'required' && empty($value)){
                        $this->addError("{$items} is required");
                    }else{

                    }
                }
            }
        }
        public function addError(){
            $this-> error[] = $error;
        }
}