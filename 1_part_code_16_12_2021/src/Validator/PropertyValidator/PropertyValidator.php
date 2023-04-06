<?php


namespace App\Validator\PropertyValidator;


use InvalidArgumentException;
use App\Exception\Http\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PropertyValidator
 *
 * @package App\Validator\PropertyValidator
 */
class PropertyValidator
{

    private ValidatorInterface $validator;
    private TranslatorInterface $translator;
    private ?Request $request;

    /**
     * RequestValidator constructor.
     *
     * @param ValidatorInterface  $validator
     * @param TranslatorInterface $translator
     * @param RequestStack        $requestStack
     */
    public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->validator = $validator;
        $this->translator = $translator;
    }


    /**
     * @param array|mixed  $object
     * @param string|array $constraints Имя класса реализующего ConstraintInterface или массив constraints
     * @param array        $options
     *
     * @return ConstraintViolationListInterface
     * @throws BadRequestException
     */
    public function validate($object, $constraints, array $options = []): ConstraintViolationListInterface
    {

        if (is_array($constraints)) {
            $violations = $this->validator->validate($object, new Assert\Collection($constraints));
        } else {

            if (!class_exists($constraints)) {
                throw new InvalidArgumentException("Параметр \$constraints не является классом.");
            }

            $class = new $constraints();

            if (!$class instanceof ConstraintInterface) {
                throw new InvalidArgumentException("Класс $constraints не реализует интерфейс ConstraintInterface");
            }

            $cb = new ConstraintBuilder();

            $class->build($cb, $options);

            $violations = $this->validator->validate($object, new Assert\Collection($cb->getConstraints()));
        }

        if (!array_key_exists("raise_exception", $options)) {
            $options["raise_exception"] = true;
        }

        if (!array_key_exists("message", $options)) {
            $options["message"] = "Bad request.";
        }

        if ($options["raise_exception"] and count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    "property_name" => $violation->getPropertyPath(),
                    "value" => $violation->getInvalidValue(),
                    "message" => $violation->getMessage(),
                    "code" => $violation->getCode()
                ];
            }
            throw new BadRequestException(
                $this->translator->trans($options["message"], [], null, $this->request->getLocale()),
                "bad_request",
                $errors
            );
        }
        return $violations;
    }


    /**
     * @param       $object
     * @param null  $constraints
     * @param array $options
     *
     * @return ConstraintViolationListInterface
     * @throws BadRequestException
     */
    public function validateEntity($object, $constraints = null, array $options = []): ConstraintViolationListInterface
    {
        if (!array_key_exists("raise_exception", $options)) {
            $options["raise_exception"] = true;
        }

        if (!array_key_exists("message", $options)) {
            $options["message"] = "Bad request.";
        }

        if (!array_key_exists("groups", $options)) {
            $options["groups"] = null;
        }

        $violations = $this->validator->validate($object, $constraints, $options["groups"]);

        if ($options["raise_exception"] and count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    "property_name" => $violation->getPropertyPath(),
                    "value" => $violation->getInvalidValue(),
                    "message" => $violation->getMessage(),
                    "code" => $violation->getCode()
                ];
            }
            throw new BadRequestException(
                $this->translator->trans($options["message"], [], null, $this->request->getLocale()),
                "bad_request",
                $errors
            );
        }
        return $violations;
    }

}