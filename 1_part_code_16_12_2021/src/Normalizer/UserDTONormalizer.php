<?php

namespace App\Normalizer;

use App\DTO\UserDTO;
use DateTime;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

/**
 *
 */
class UserDTONormalizer implements ContextAwareNormalizerInterface
{


    /**
     * @param UserDTO     $object
     * @param string|null $format
     * @param array       $context
     *
     * @return array|null
     */
    public function normalize($object, string $format = null, array $context = [])
    {

        $normalized = [
          "id" =>  $object->getId(),
          "first_name" =>  $object->getFirstName(),
          "last_name" =>  $object->getLastName(),
          "middle_name" =>  $object->getMiddleName(),
          "full_name" =>  $this->computedFullName($object),
          "abbreviation" =>  $this->computedAbbreviation($object),
          "dialer_state" =>  $object->getDialerState(),
          "online" =>  $object->isOnline(),
          "project" => [
              "id" =>  $object->getProjectId(),
              "name" =>  $object->getProjectName()
          ],
          "group" => [
              "id" =>  $object->getGroupId(),
              "name" =>  $object->getGroupName()
          ]
        ];

        $normalized["last_activity_at"] = false;
        if ($object->getLastActivityAt() instanceof DateTime) {
            $normalized["last_activity_at"] = $object->getLastActivityAt()->getTimestamp();
        }

        return $normalized;
    }

    /**
     * @param             $data
     * @param string|null $format
     * @param array       $context
     *
     * @return bool
     */
    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof UserDTO;
    }

    /**
     * @param UserDTO $object
     *
     * @return string
     */
    private function computedFullName(UserDTO $object): string
    {
        $chunks = [];

        if (!empty($object->getLastName())) {
            $chunks[] = $object->getLastName();
        }

        if (!empty($object->getFirstName())) {
            $chunks[] = $object->getFirstName();
        }

        if (!empty($object->getMiddleName())) {
            $chunks[] = $object->getMiddleName();
        }

        $name = implode(" ", $chunks);

        if (!empty($name)) {
            return $name;
        }

        return "Без имени";
    }


    /**
     * @param UserDTO $object
     *
     * @return string|null
     */
    public function computedAbbreviation (UserDTO $object): ?string
    {
        $chunks = [];

        if (!empty($object->getLastName())) {
            $chunks[] = mb_substr($object->getLastName(), 0, 1) ?? "";
        }

        if (!empty($object->getFirstName())) {
            $chunks[] = mb_substr($object->getFirstName(), 0, 1) ?? "";
        }

        $name = implode("", $chunks);

        if (!empty($name)) {
            return $name;
        }

        return "";
    }
}