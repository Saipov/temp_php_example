<?php

namespace App\Entity;

use App\DBAL\GenderType;
use App\DBAL\UserModeType;
use App\DBAL\UserStatusType;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users", indexes={
 *     @ORM\Index(columns={"first_name", "last_name", "middle_name", "login"}, flags={"fulltext"})}
 *    )
 * @UniqueEntity(
 *     fields={"login"},
 *     errorPath="login",message="The user with the specified login is already registered."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="The user with the specified email is already registered."
 * )
 * @UniqueEntity(
 *     fields={"phone"},
 *     errorPath="phone",
 *     message="The user with the specified number is already registered."
 * )
 */
class User implements UserInterface, OrganizationFilterableInterface, UserIsDeletedFilterableInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var string|null
     * @Assert\NotBlank
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $login = null;

    /**
     * @var string|null
     * @ORM\Column(type="string")
     */
    private ?string $password = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private ?string $email = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, unique=true, nullable=true)
     */
    private ?string $phone = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $first_name = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $last_name = null;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $middle_name = null;

    /**
     * @var string|null
     * @ORM\Column(type="gender", nullable=true, options={"default": "0"})
     */
    private ?string $gender = GenderType::INDETERMINATE;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $userpic;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $refresh_token;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private DateTime $created_at;

    /**
     * @var UserGroup|null
     * @ORM\ManyToOne(targetEntity="UserGroup", inversedBy="users", fetch="EXTRA_LAZY")
     */
    private ?UserGroup $group = null;

    /**
     * @ORM\OneToOne(targetEntity="UserPBXConfig", mappedBy="user", cascade={"ALL"}, fetch="EXTRA_LAZY")
     */
    private ?UserPBXConfig $pbx_config = null;

    /**
     * @ORM\ManyToOne(targetEntity="UserRole", inversedBy="users", fetch="EAGER")
     */
    private ?UserRole $role = null;

    /**
     * @var Organization|null
     * @ORM\Cache("READ_ONLY")
     * @ORM\ManyToOne(targetEntity="Organization", fetch="LAZY")
     */
    private ?Organization $organization = null;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\ManyToMany(targetEntity="Project", mappedBy="members", fetch="EXTRA_LAZY")
     */
    private $projects;

    /**
     * Текущий проект пользователя
     *
     * @ORM\ManyToOne (targetEntity="Project", inversedBy="users", fetch="LAZY")
     */
    private ?Project $project = null;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $locked_at;

    /**
     * @ORM\Column(type="user_status")
     */
    private ?string $status = UserStatusType::COFFEE_BREAK;

    /**
     * @ORM\Column(type="user_mode", options={"default": "0"})
     */
    private ?string $mode = UserModeType::NORMAL;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany (
     *     targetEntity="Contact",
     *     mappedBy="owner",
     *     cascade={"persist"},
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $contacts;

    /**
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany(
     *     targetEntity="Task",
     *     mappedBy="owner",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     */
    private $tasks;

    /**
     * Количество контактов
     * Это значение статическое, нет магии, заполни его!!!
     * @ORM\Column(type="integer", nullable=true, options={"default": 0})
     */
    private int $contacts_count = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $super_admin = false;

    /**
     * Время удаления пользователя, одновременно и флаг - удалён или нет
     * Фактического удаления не должно происходить!
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $deleted_at;

    /**
     * Временная зона пользователя.
     *
     * @var DateTimeZone|null
     * @ORM\Column(type="timezone", nullable=true)
     */
    private ?DateTimeZone $timezone = null;

    /**
     * Время входа пользователя
     *
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $last_login;

    /**
     * Дата Время последнего действия.
     *
     * @var \Datetime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_activity_at;

    /**
     * Дата время последнего совершенного вызова.
     *
     * @var \Datetime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $last_call_at;

    /**
     * Состояние звонилки пользователя.
     *
     * @var string|null
     * @ORM\Column(type="dialer_state", nullable=true, options={"default": 0})
     */
    private ?string $dialer_state = "unavailable";

    /**
     * Пользователь занят.
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private bool $busy = false;

    /**
     * Состояние пользователя онлайн/офлайн
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private bool $online = false;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $schedule = [];

    /**
     * Страна пользователя.
     *
     * @ORM\ManyToOne (targetEntity="Country", cascade={"persist"})
     */
    private ?Country $country = null;

    /**
     * Список активных сессий пользователя.
     *
     * @var ArrayCollection|PersistentCollection
     * @ORM\OneToMany (targetEntity="UserSession", mappedBy="user", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $sessions;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->created_at = new DateTime();
        $this->contacts = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->sessions = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return UserGroup|null
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param UserGroup|null $group
     */
    public function setGroup(?UserGroup $group): void
    {
        $this->group = $group;
    }

    /**
     * @return string|null
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string|null $login
     */
    public function setLogin(?string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * @param string|null $first_name
     */
    public function setFirstName(?string $first_name): void
    {
        $this->first_name = $first_name;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * @param string|null $last_name
     */
    public function setLastName(?string $last_name): void
    {
        $this->last_name = $last_name;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }

    /**
     * @param string|null $middle_name
     */
    public function setMiddleName(?string $middle_name): void
    {
        $this->middle_name = $middle_name;
    }

    /**
     * @return string|null
     */
    public function getUserpic(): ?string
    {
        return $this->userpic;
    }

    /**
     * @param string|null $userpic
     */
    public function setUserpic(?string $userpic): void
    {
        $this->userpic = $userpic;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    /**
     * @param string|null $refresh_token
     */
    public function setRefreshToken(?string $refresh_token): void
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * @param bool $unixtime
     *
     * @return DateTime|integer|null
     */
    public function getCreatedAt(bool $unixtime = false)
    {
        if ($unixtime and $this->created_at instanceof DateTime) {
            return $this->created_at->getTimestamp();
        }
        return $this->created_at;
    }

    /**
     * @return DateTime|int|null
     */
    public function getLockedAt(bool $unixtime = false)
    {
        if ($unixtime and $this->locked_at instanceof DateTime) {
            return $this->locked_at->getTimestamp();
        }
        return $this->locked_at;
    }

    /**
     * @param DateTime $lockedAt
     */
    public function setLockedAt($lockedAt): void
    {
        $this->locked_at = $lockedAt;
    }

    public function getRoles(): array
    {
        $role = $this->role;
        if ($role instanceof UserRole) {
            return $role->getPermissions()
                ->map(function (Permission $permission) {
                    return $permission->getValue();
                })->toArray();
        }
        return ['ROLE_OPERATOR'];
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getUsername(): ?string
    {
        return $this->login;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return UserPBXConfig
     */
    public function getPbxConfig(): ?UserPBXConfig
    {
        return $this->pbx_config;
    }

    /**
     * @param UserPBXConfig $pbx_config
     */
    public function setPbxConfig(UserPBXConfig $pbx_config): void
    {
        $this->pbx_config = $pbx_config;
    }

    /**
     * @return UserRole|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }

    /**
     * @return Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @param Organization|null $organization
     */
    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param ArrayCollection|PersistentCollection $projects
     */
    public function setProjects($projects): void
    {
        $this->projects = $projects;
    }

    /**
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        return $this->project;
    }

    /**
     * @param Project|null $project
     */
    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param ArrayCollection|PersistentCollection $contacts
     */
    public function setContacts($contacts): void
    {
        $this->contacts = $contacts;
    }

    /**
     * @return int
     */
    public function getContactsCount(): int
    {
        return $this->contacts_count;
    }

    /**
     * @param int $contacts_count
     */
    public function setContactsCount(int $contacts_count): void
    {
        $this->contacts_count = $contacts_count;
    }

    /**
     * @return ArrayCollection|PersistentCollection
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param ArrayCollection|PersistentCollection $tasks
     */
    public function setTasks($tasks): void
    {
        $this->tasks = $tasks;
    }

    /**
     * @return bool|null
     */
    public function isSuperAdmin(): ?bool
    {
        return $this->super_admin;
    }

    /**
     * @param bool|null $super_admin
     */
    public function setSuperAdmin(bool $super_admin): void
    {
        $this->super_admin = $super_admin;
    }

    /**
     * @return bool
     */
    public function isDeletedAt(): bool
    {
        return $this->deleted_at instanceof DateTime;
    }

    /**
     * Удаление пользователя.
     * Фактически будет выставлена дата удаления.
     * Не забудь выполнить flush.
     */
    public function delete(): void
    {
        $this->setDeletedAt(new DateTime());
    }

    /**
     * @param DateTime|null $deleted_at
     */
    public function setDeletedAt(?DateTime $deleted_at): void
    {
        $this->deleted_at = $deleted_at;
    }

    /**
     * @return DateTimeZone|null
     */
    public function getTimezone(): ?DateTimeZone
    {
        return $this->timezone ?? new DateTimeZone(date_default_timezone_get());
    }

    /**
     * @param DateTimeZone|null $timezone
     */
    public function setTimezone(?DateTimeZone $timezone): void
    {
        $this->timezone = $timezone;
    }

    /**
     * @param bool $unixtime
     *
     * @return DateTime|int|null
     */
    public function getLastLogin(bool $unixtime = false)
    {
        if ($unixtime and $this->last_login instanceof DateTime) {
            return $this->last_login->getTimestamp();
        }
        return $this->last_login;
    }

    /**
     * @param DateTime|null $last_login
     */
    public function setLastLogin(?DateTime $last_login): void
    {
        $this->last_login = $last_login;
    }

    /**
     * @return mixed
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param mixed $schedule
     */
    public function setSchedule($schedule): void
    {
        $this->schedule = $schedule;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     */
    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @param array|null $roles
     *
     * @return User
     */
    public function setRoles(?array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullName (): ?string
    {
        $chunks = [];

        if (!empty($this->last_name)) {
            $chunks[] = $this->last_name;
        }

        if (!empty($this->first_name)) {
            $chunks[] = $this->first_name;
        }

        if (!empty($this->middle_name)) {
            $chunks[] = $this->middle_name;
        }

        $name = implode(" ", $chunks);

        if (!empty($name)) {
            return $name;
        }

        return "Без имени";
    }

    /**
     * @return string|null
     */
    public function getAbbreviation (): ?string
    {
        $chunks = [];

        if (!empty($this->last_name)) {
            $chunks[] = mb_substr($this->last_name, 0, 1) ?? "";
        }

        if (!empty($this->first_name)) {
            $chunks[] = mb_substr($this->first_name, 0, 1) ?? "";
        }

        $name = implode("", $chunks);

        if (!empty($name)) {
            return $name;
        }

        return "";
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return self::class;
    }

    /**
     * @return Datetime|int
     */
    public function getLastActivityAt(bool $unixtime = false)
    {
        if ($unixtime and $this->last_activity_at instanceof DateTime) {
            return $this->last_activity_at->getTimestamp();
        }
        return $this->last_activity_at;
    }

    /**
     * @param Datetime $last_activity_at
     */
    public function setLastActivityAt(Datetime $last_activity_at): void
    {
        $this->last_activity_at = $last_activity_at;
    }

    /**
     * @return string|null
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * @param string|null $mode
     */
    public function setMode(?string $mode): void
    {
        $this->mode = $mode;
    }

    /**
     * @return string|null
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * @param string|null $gender
     */
    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ORM\PersistentCollection
     */
    public function getSessions(): ArrayCollection|PersistentCollection
    {
        return $this->sessions;
    }

    /**
     * @return \Datetime|null
     */
    public function getLastCallAt(): ?DateTime
    {
        return $this->last_call_at;
    }

    /**
     * @param \Datetime|null $last_call_at
     */
    public function setLastCallAt(?DateTime $last_call_at): void
    {
        $this->last_call_at = $last_call_at;
    }

    /**
     * @return string|null
     */
    public function getDialerState(): ?string
    {
        return $this->dialer_state;
    }

    /**
     * @param string|null $dialer_state
     */
    public function setDialerState(?string $dialer_state): void
    {
        $this->dialer_state = $dialer_state;
    }

    /**
     * @return bool
     */
    public function isBusy(): bool
    {
        return $this->busy;
    }

    /**
     * @param bool $busy
     */
    public function setBusy(bool $busy): void
    {
        $this->busy = $busy;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     */
    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }
}
