<?php

namespace App\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\LogRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LogRepository::class)
 * @ORM\Table(name="logs", indexes={
 *     @ORM\Index(
 *      columns={"user_id", "action", "start_action_at", "http_method", "is_archive"}
 *     )
 * })
 */
class Log
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * Пользователь совершивший действие.
     *
     * @Assert\NotBlank()
     * @ORM\ManyToOne(targetEntity="User")
     */
    private ?User $user;

    /**
     * Тип действия
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="`action`", type="log_action_type")
     */
    private ?string $action;

    /**
     * Дата и время совершения действия
     *
     *
     * @see https://www.nikolaposa.in.rs/blog/2019/07/01/stop-using-datetime/
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTime $start_action_at;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $context = [];

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $url;

    /**
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private ?string $http_method;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $controller;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": 0})
     */
    private ?bool $is_archive;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $request = [];

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private ?string $user_agent;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $query_string;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     *
     * @return Log
     */
    public function setUser(?User $user): Log
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @param bool $timestamp
     *
     */
    public function getStartActionAt(bool $timestamp = false)
    {
        if ($timestamp && $this->start_action_at instanceof DateTimeInterface) {
            return $this->start_action_at->getTimestamp();
        }
        return $this->start_action_at;
    }

    /**
     * @param DateTime|null $start_action_at
     *
     * @return Log
     */
    public function setStartActionAt(?DateTime $start_action_at): Log
    {
        $this->start_action_at = $start_action_at;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array|null $context
     *
     * @return $this
     */
    public function setContext(?array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * @param string|null $ip
     *
     * @return $this
     */
    public function setIp(?string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     *
     * @return $this
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getHttpMethod(): ?string
    {
        return $this->http_method;
    }

    /**
     * @param string|null $http_method
     *
     * @return $this
     */
    public function setHttpMethod(?string $http_method): self
    {
        $this->http_method = $http_method;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string|null $controller
     *
     * @return $this
     */
    public function setController(?string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsArchive(): ?bool
    {
        return $this->is_archive;
    }

    /**
     * @param bool $is_archive
     *
     * @return $this
     */
    public function setIsArchive(bool $is_archive): self
    {
        $this->is_archive = $is_archive;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRequest(): ?array
    {
        return $this->request;
    }

    /**
     * @param array|null $request
     *
     * @return $this
     */
    public function setRequest(?array $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->user_agent;
    }

    /**
     * @param string|null $user_agent
     *
     * @return $this
     */
    public function setUserAgent(?string $user_agent): self
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    public function getQueryString(): ?string
    {
        return $this->query_string;
    }

    public function setQueryString(?string $query_string): self
    {
        $this->query_string = $query_string;

        return $this;
    }
}
