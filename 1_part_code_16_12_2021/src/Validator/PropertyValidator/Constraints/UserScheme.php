<?php

namespace App\Validator\PropertyValidator\Constraints;

use Exception;
use libphonenumber\PhoneNumberUtil;
use App\DBAL\UserStatusType;
use App\Validator\PropertyValidator\ConstraintBuilder;
use App\Validator\PropertyValidator\ConstraintInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class UserScheme implements ConstraintInterface
{
    public function build(ConstraintBuilder $builder, $options = null)
    {
        $builder->add("login", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\NotBlank()
                ]
            ])
        ]);
        $builder->add("first_name", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\NotBlank(),
                    new Assert\Length(["min" => 1, "max" => 255])
                ]
            ])
        ]);
        $builder->add("last_name", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\NotBlank(),
                    new Assert\Length(["min" => 1, "max" => 255])
                ]
            ])
        ]);
        $builder->add("middle_name", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\Length(["max" => 255])
                ]
            ])
        ]);
        $builder->add("password", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\NotBlank(),
                    new Assert\NotCompromisedPassword()
                ]
            ])
        ]);
        $builder->add("phone", [
            new Assert\Optional(["constraints" => [
                new Assert\NotBlank(),
                new Assert\Callback([
                    "callback" => function ($object, ExecutionContextInterface $context) {
                        if (empty($object)) {
                            $context->buildViolation("Invalid number format")
                                ->addViolation();
                            return;
                        }
                        if (!$this->ValidatePhoneNumber($object)) {
                            $context->buildViolation("Invalid number format")
                                ->addViolation();
                        }
                    }
                ])
            ]])
        ]);
        $builder->add("email", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\NotBlank(),
                    new Assert\Email()
                ]
            ])
        ]);

        $builder->add("organization_id", [
            new Assert\Optional()
        ]);

        $builder->add("project_id", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\PositiveOrZero()
                ]
            ])
        ]);

        $builder->add("group_id", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\PositiveOrZero()
                ]
            ])
        ]);

        $builder->add("role_id", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\Positive()
                ]
            ])
        ]);

        $builder->add("status", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\Choice([
                        "choices" => UserStatusType::toArray(),
                        "min" => 1,
                        "max" => 1
                    ])
                ]
            ])
        ]);

        $builder->add("pbx_config", [
            new Assert\Optional([
                "constraints" => [
                    new Assert\Collection([
                        "fields" => [
                            "display_name" => [
                                new Assert\Length(["max" => 20])
                            ],
                            "login" => [
                                new Assert\NotBlank()
                            ],
                            "password" => [
                                new Assert\NotBlank()
                            ],
                            "server" => [
                                new Assert\NotBlank(),
                            ],
                            "port" => [
                                new Assert\NotBlank(),
                                new Assert\Positive()
                            ],
                        ]
                    ])
                ]
            ])
        ]);

        $builder->add("projects", [
            new Assert\Optional()
        ]);
    }


    /**
     * @param string $text
     * @param string $defaultRegion
     *
     * @return bool
     */
    private function ValidatePhoneNumber(string $text, $defaultRegion = "RU"): bool
    {
        $util = PhoneNumberUtil::getInstance();
        try {
            return $util->isValidNumber($util->parse($text, $defaultRegion));
        } catch (Exception $exception) {
            return false;
        }
    }
}