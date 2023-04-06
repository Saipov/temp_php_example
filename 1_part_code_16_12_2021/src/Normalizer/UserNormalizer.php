<?php


namespace App\Normalizer;


use App\Entity\Country;
use App\Entity\Organization;
use App\Entity\Permission;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Entity\UserPBXConfig;
use App\Entity\UserRole;
use DateTime;
use Doctrine\ORM\PersistentCollection;
use Exception;
use stdClass;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * Class UserNormalizer
 *
 * @package App\Normalizer
 */
class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface
{

    private ObjectNormalizer $normalizer;
    private ?object $defaultRtcConfiguration;

    /**
     * RoleNormalizer constructor.
     *
     * @param ObjectNormalizer $normalizer
     */
    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;

        // RTC Параметры по умолчанию.
        $this->defaultRtcConfiguration = new stdClass();
        $this->defaultRtcConfiguration->bundle_policy = "balanced";
        $this->defaultRtcConfiguration->certificates = [];
        $this->defaultRtcConfiguration->ice_candidate_pool_size = 0;
        $this->defaultRtcConfiguration->ice_servers = [];
        $this->defaultRtcConfiguration->ice_transport_policy = "all";
        $this->defaultRtcConfiguration->rtcp_mux_policy = "require";
        $this->defaultRtcConfiguration->candidate_ready_timeout = 0;
    }

    /**
     * @param User        $object
     * @param string|null $format
     * @param array       $context
     *
     * @return array|null
     */
    public function normalize($object, string $format = null, array $context = []): ?array
    {

        $normalized = [
            "id" => $object->getId(),
            "first_name" => $object->getFirstName(),
            "last_name" => $object->getLastName(),
            "middle_name" => $object->getMiddleName(),
            "full_name" => $object->getFullName(),
            "userpic" => $object->getUserpic(),
            "gender" => $object->getGender(),
            "abbreviation" => $object->getAbbreviation(),
            "login" => $object->getLogin(),
            "email" => $object->getEmail(),
            "phone" => $object->getPhone(),
            "is_super_admin" => $object->isSuperAdmin(),
            "status" => $object->getStatus(),
            "mode" => $object->getMode(),
            "online" => $object->isOnline(),
            "busy" => $object->isBusy(),
            "contacts_count" => $object->getContactsCount(),
            "is_locked" => $object->getLockedAt() instanceof DateTime,
            "tz" => $object->getTimezone(),
            "last_login" => $object->getLastLogin(true),
            "last_activity_at" => $object->getLastActivityAt(true),
            "schedule_available" => (bool)$object->getSchedule(),      // True = Расписание установлено
            "roles" => $object->getRoles()
        ];

        $normalized["created_at"] = null;
        if ($object->getCreatedAt() instanceof DateTime) {
            $normalized["created_at"] = $object->getCreatedAt()->getTimestamp();
        }

        // Проект
        if (in_array("project", $context)) {
            $normalized["project"] = null;
            try {
                if ($object->getProject() instanceof Project) {
                    $normalized["project"] = [
                        "id" => $object->getProject()->getId(),
                        "name" => $object->getProject()->getName()
                    ];
                }
            } catch (Exception $exception) {
                $normalized["project"] = null;
            }
        }

        // Проекты
        if (in_array("projects", $context)) {
            $normalized["projects"] = null;
            if ($object->getProjects() instanceof PersistentCollection) {
                $normalized["projects"] = $object->getProjects()->map(function (Project $project) {
                    return [
                        "id" => $project->getId(),
                        "name" => $project->getName(),
                        "description" => $project->getDescription(),
                        "created_at" => $project->getCreatedAt(true)
                    ];
                });
            }
        }

        // Страна
        if (in_array("country", $context)) {
            $normalized["country"] = null;
            if ($object->getCountry() instanceof Country) {
                $normalized["country"] = [
                    "id" => $object->getCountry()->getId(),
                    "name" => $object->getCountry()->getName(),
                    "full_name" => $object->getCountry()->getFullName(),
                    "code" => $object->getCountry()->getCode()
                ];
            }
        }

        // Организация
        if (in_array("organization", $context)) {
            $normalized["organization"] = null;
            if ($object->getOrganization() instanceof Organization) {
                $normalized["organization"] = [
                    "id" => $object->getOrganization()->getId(),
                    "name" => $object->getOrganization()->getName()
                ];
            }
        }

        // Группа
        if (in_array("group", $context)) {
            $normalized["group"] = null;
            try {
                if ($object->getGroup() instanceof UserGroup) {
                    $normalized["group"] = [
                        "id" => $object->getGroup()->getId(),
                        "name" => $object->getGroup()->getName()
                    ];
                }
            } catch (Exception $exception) {
                $normalized["group"] = null;
            }
        }

        // Роль
        if ($object->getRole() instanceof UserRole) {
            $normalized["role"] = [
                "id" => $object->getRole()->getId(),
                "name" => $object->getRole()->getName(),
                "permissions" => $object->getRole()->getPermissions()->map(function (Permission $permission) {
                    return $permission->getValue();
                })
            ];
        }

        if (in_array("pbx_configuration.credentials", $context)) {
            if ($object->getPbxConfig() instanceof UserPBXConfig) {
                $normalized["pbx_configuration"]["credentials"] = [
                    "schema" => $object->getPbxConfig()->getSchema(),
                    "server" => $object->getPbxConfig()->getServer(),
                    "port" => $object->getPbxConfig()->getPort(),
                    "display_name" => $object->getPbxConfig()->getDisplayName(),
                    "login" => $object->getPbxConfig()->getLogin(),
                    "password" => $object->getPbxConfig()->getPassword()
                ];
            }
        }

        if (in_array("pbx_configuration.rtc_configuration", $context)) {
            if ($object->getPbxConfig() instanceof UserPBXConfig and
                $object->getPbxConfig()->getRtcConfiguration()
            ) {
                $normalized["pbx_configuration"]["rtc_configuration"] = $object->getPbxConfig()->getRtcConfiguration();
            } else {
                $normalized["pbx_configuration"]["rtc_configuration"] = $this->defaultRtcConfiguration;
            }
        }

        return $normalized;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}