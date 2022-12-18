<?php

namespace Se\Mdx\Structure;

class NoneEmptyExpression extends Expression
{
    private array $expressions;

    public function __construct()
    {
        $this->expressions = [];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf("NoneEmpty(%s)", implode(', ', $this->getExpressions()));
    }

    /**
     * @return array
     */
    public function getExpressions(): array
    {
        return $this->expressions;
    }

    /**
     * @param Expression $expression
     * @return $this
     */
    public function addExpression(Expression $expression): self
    {
        $this->expressions[] = $expression;
        return $this;
    }
}
