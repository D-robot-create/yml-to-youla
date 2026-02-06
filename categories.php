<?php
// categories.php
require_once 'config.php';

class CategoryMapper {
    
    public static function mapCategory($ymlCategoryId, $ymlCategoryName = '') {
        $categoryNameLower = strtolower($ymlCategoryName);
        
        // По умолчанию - категория для кабелей и проводов
        // Так как ваш фид содержит кабели, добавим для них обработку
        
        // Кабели и провода → Ремонт и строительство → Электротехника
        if (self::containsAny($categoryNameLower, ['кабель', 'провод', 'cable', 'wire', 'шнур', 'кабельно-проводниковая продукция'])) {
            return [
                'categoryId' => '6', // Ремонт и строительство
                'subcategoryId' => '611', // Электротехника (допустим)
                'param_name' => 'elektrotehnika_tip',
                'param_value' => '9530' // Кабели и провода
            ];
        }
        
        // Бытовая техника - Климатическая техника
        if (self::containsAny($categoryNameLower, ['вентилятор', 'кондиционер', 'обогреватель', 'увлажнитель', 'очиститель', 'fan', 'air conditioner', 'heater'])) {
            return [
                'categoryId' => '2',
                'subcategoryId' => '212',
                'param_name' => 'klimaticheskaya_tip',
                'param_value' => self::getClimateType($ymlCategoryName)
            ];
        }
        
        // Ремонт и строительство - Отопление и вентиляция
        elseif (self::containsAny($categoryNameLower, ['вентиляция', 'котел', 'радиатор', 'камин', 'печь', 'теплый пол', 'ventilation', 'boiler', 'radiator'])) {
            return [
                'categoryId' => '6',
                'subcategoryId' => '610',
                'param_name' => 'otoplenie_ventilyaciya_tip',
                'param_value' => self::getHeatingType($ymlCategoryName)
            ];
        }
        
        // Вытяжки
        elseif (strpos($categoryNameLower, 'вытяжка') !== false || strpos($categoryNameLower, 'hood') !== false) {
            return [
                'categoryId' => '2',
                'subcategoryId' => '206',
                'param_name' => '',
                'param_value' => ''
            ];
        }
        
        // По умолчанию - Ремонт и строительство → Электротехника
        return [
            'categoryId' => '6',
            'subcategoryId' => '611',
            'param_name' => 'elektrotehnika_tip',
            'param_value' => '9530'
        ];
    }
    
    private static function getClimateType($categoryName) {
        $nameLower = strtolower($categoryName);
        
        if (self::containsAny($nameLower, ['вентилятор', 'fan'])) {
            return '9920';
        } elseif (strpos($nameLower, 'ионизатор') !== false || strpos($nameLower, 'ionizer') !== false) {
            return '9921';
        } elseif (self::containsAny($nameLower, ['метеостанц', 'термометр', 'термостат', 'weather station'])) {
            return '9922';
        } elseif (strpos($nameLower, 'мобильный кондиционер') !== false) {
            return '9923';
        } elseif (strpos($nameLower, 'настенный кондиционер') !== false) {
            return '9924';
        } elseif (self::containsAny($nameLower, ['обогреватель', 'обогрев', 'heater'])) {
            return '9925';
        } elseif (self::containsAny($nameLower, ['очиститель', 'увлажнитель', 'air purifier', 'humidifier'])) {
            return '9926';
        }
        
        return '9920';
    }
    
    private static function getHeatingType($categoryName) {
        $nameLower = strtolower($categoryName);
        
        if (strpos($nameLower, 'вентиляция') !== false) {
            return '9519';
        } elseif (self::containsAny($nameLower, ['газовый баллон', 'баллон газ'])) {
            return '9520';
        } elseif (self::containsAny($nameLower, ['камин', 'печь', 'печка', 'fireplace', 'stove'])) {
            return '9521';
        } elseif (self::containsAny($nameLower, ['котел', 'boiler'])) {
            return '9522';
        } elseif (self::containsAny($nameLower, ['радиатор', 'батарея', 'radiator'])) {
            return '9523';
        } elseif (strpos($nameLower, 'теплый пол') !== false || strpos($nameLower, 'подогрев пол') !== false) {
            return '9524';
        }
        
        return '9519';
    }
    
    private static function containsAny($haystack, $needles) {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}