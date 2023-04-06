<?php


namespace App\Classes;

use InvalidArgumentException;

/**
 * Вспомогательные функции, обёртки над стандартными функциями, дополняющие возможности.
 *
 * Class Functions
 *
 * @package App\Classes
 */
class Functions
{
    /**
     * explode — Разбивает строку с помощью разделителя.
     * А также опционально применять нормализатор в виде дополнительной функции к каждому элементу массива.
     *
     * @param string       $separator  Разделитель.
     * @param string       $string     Входная строка.
     * @param int          $limit      [необязательный] Если limit установлен и положителен, возвращаемый массив будет
     *                                 содержать максимум элементов limit, причем последний элемент будет содержать
     *                                 остальную часть строки. Если параметр limit отрицательный, возвращаются все
     *                                 компоненты, кроме последнего -limit. Если параметр ограничения равен нулю, то
     *                                 это рассматривается как 1.
     * @param string|array $normalizer [необязательный] Имя функции или массив имн функций. Применит функцию или массив
     *                                 функций trim, ltrim, rtrim с одним аргументом.
     *
     * @return array
     */
    static function explode(string $separator, string $string, int $limit = PHP_INT_MAX, $normalizer = null): array
    {
        if (!is_null($normalizer)) {
            if (is_array($normalizer)) {
                foreach ($normalizer as $item) {
                    if (!function_exists($item)) {
                        throw new InvalidArgumentException("normalizer not found");
                    }
                }
            } else {
                if (!function_exists($normalizer)) {
                    throw new InvalidArgumentException("normalizer not found");
                }
            }
        }

        $arr = explode($separator, $string, $limit);
        return array_map(function ($e) use ($normalizer) {
            if (is_null($normalizer)) {
                return $e;
            }
            // Если хотим применить несколько нормализаторов
            if (is_array($normalizer)) {
                $tmp = $e;
                foreach ($normalizer as $item) {
                    $tmp = call_user_func($item, $tmp);
                }
                return $tmp;
            }
            return call_user_func($normalizer, $e);
        }, $arr);
    }
}