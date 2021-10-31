<?php declare(strict_types = 1);

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use function is_int;
use function is_object;

final class UniqueValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!is_object($value)) {
            throw new UnexpectedTypeException($value, 'object');
        }

        if (!$constraint instanceof Unique) {
            throw new UnexpectedTypeException($constraint, Unique::class);
        }

        $qb = $this->entityManager->createQueryBuilder()
            ->select('COUNT(e)')
            ->from($constraint->entityClass, 'e');

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ((array) $constraint->fields as $dtoField => $entityField) {
            if (is_int($dtoField)) {
                $dtoField = $entityField;
            }

            $fieldValue = $propertyAccessor->getValue($value, $dtoField);

            $qb->andWhere($qb->expr()->eq("e.$entityField", ":f_$entityField"));
            $qb->setParameter("f_$entityField", $fieldValue);
        }

        foreach ((array) $constraint->idFields as $dtoField => $entityField) {
            if (is_int($dtoField)) {
                $dtoField = $entityField;
            }

            $fieldValue = $propertyAccessor->getValue($value, $dtoField);

            if ($fieldValue !== null) {
                $qb->andWhere($qb->expr()->neq("e.$entityField", ":i_$entityField"));
                $qb->setParameter("i_$entityField", $fieldValue);
            }
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        if ($count > 0) {
            $this->context->buildViolation($constraint->message)
                ->setCode(Unique::NOT_UNIQUE_ERROR)
                ->atPath($constraint->errorPath)
                ->addViolation();
        }
    }
}
