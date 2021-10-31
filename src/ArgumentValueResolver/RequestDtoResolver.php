<?php declare(strict_types = 1);

namespace App\ArgumentValueResolver;

use App\Exception\BadRequestDtoException;
use App\Request\RequestDtoInterface;
use Generator;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//class RequestDtoResolver implements ArgumentValueResolverInterface
class RequestDtoResolver
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $reflection = new ReflectionClass($argument->getType());
        if ($reflection->implementsInterface(RequestDtoInterface::class)) {
            return true;
        }

        return false;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $class = $argument->getType();
        $dto   = new $class($request);

        $errors = $this->validator->validate($dto);
        if (count($errors)) {
            throw new BadRequestDtoException('Request is not valid', $errors);
        }

        yield $dto;
    }
}
