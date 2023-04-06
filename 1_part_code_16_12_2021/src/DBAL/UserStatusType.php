<?php


namespace App\DBAL;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Class UserStatusType
 *
 * @package App\DBAL
 */
class UserStatusType extends Type
{

    const TYPE_NAME = "user_status";

    const AVAILABLE = "available";              // Доступен
    const DO_NOT_DISTURB = "do_not_disturb";    // Не беспокоить
    const COFFEE_BREAK = "coffee_break";        // Перерыв на кофе
    const BUSY = "busy";                        // Занят

    /**
     * @return string[]
     */
    public static function toArray(): array
    {
        return [
            self::AVAILABLE,
            self::DO_NOT_DISTURB,
            self::COFFEE_BREAK,
            self::BUSY,
        ];
    }

    /**
     * @param array            $column
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return "TINYINT(1)";
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        switch ($value) {
            case 1: return self::AVAILABLE;
            case 2: return self::DO_NOT_DISTURB;
            case 3: return self::COFFEE_BREAK;
            case 4: return self::BUSY;
            default:
            {
                return null;
            }
        }
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return self::convert($value);
    }

    /**
     * @param $value
     *
     * @return int|null
     */
    public static function convert($value): ?int
    {
        return match ($value) {
            self::AVAILABLE => 1,
            self::DO_NOT_DISTURB => 2,
            self::COFFEE_BREAK => 3,
            self::BUSY => 4,
            default => null,
        };
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
