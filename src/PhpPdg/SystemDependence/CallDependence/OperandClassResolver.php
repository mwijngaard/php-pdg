<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPCfg\Operand;
use PHPTypes\Type;

class OperandClassResolver implements OperandClassResolverInterface {
	public function resolveClassNames(Operand $operand) {
		$classnames = [];
		if (is_object($operand->type) === true && $operand->type instanceof Type) {
			/** @var Type $type */
			$type = $operand->type;
			if ($type->type === Type::TYPE_STRING) {
				if ($operand instanceof Operand\Literal) {
					$classnames[] = strtolower($operand->value);
				}
			} else {
				$classnames = array_merge($classnames, $this->resolveClassNamesFromUserTypes($type));
			}
		}
		return $classnames;
	}

	private function resolveClassNamesFromUserTypes(Type $type) {
		$classnames = [];
		switch ($type->type) {
			case Type::TYPE_OBJECT:
				if ($type->userType !== null) {
					$classnames[] = strtolower($type->userType);
				}
				break;
			case Type::TYPE_UNION:
				foreach ($type->subTypes as $subType) {
					$classnames = array_merge($classnames, $this->resolveClassNamesFromUserTypes($subType));
				}
				break;

		}
		return $classnames;
	}
}